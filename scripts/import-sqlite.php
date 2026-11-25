<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$markerPath = storage_path('app/playlab-sqlite-import-source.txt');

if (!is_file($markerPath)) {
    echo "No hay una base SQLite anterior para importar.\n";
    exit(0);
}

$sourcePath = trim((string) file_get_contents($markerPath));

if ($sourcePath === '' || !is_file($sourcePath)) {
    fwrite(STDERR, "ERROR: Se detecto una importacion SQLite pendiente, pero no se encuentra el archivo original.\n");
    exit(1);
}

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "ERROR: PHP no tiene habilitada pdo_sqlite para leer la base anterior. Activa extension=pdo_sqlite en Laragon.\n");
    exit(1);
}

$excludedTables = [
    'migrations',
    'cache',
    'cache_locks',
    'sessions',
    'jobs',
    'job_batches',
    'failed_jobs',
    'password_reset_tokens',
];

try {
    $source = new PDO('sqlite:' . $sourcePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $mysql = DB::connection();
    $sourceTables = $source->query(
        "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
    )->fetchAll(PDO::FETCH_COLUMN);

    $mysql->statement('SET FOREIGN_KEY_CHECKS=0');

    $importedRows = 0;
    $importedTables = 0;

    try {
        foreach ($sourceTables as $table) {
            if (in_array($table, $excludedTables, true) || !Schema::hasTable($table)) {
                continue;
            }

            $quotedTable = '"' . str_replace('"', '""', $table) . '"';
            $sourceInfo = $source->query('PRAGMA table_info(' . $quotedTable . ')')->fetchAll();
            $sourceColumns = array_column($sourceInfo, 'name');
            $targetColumns = Schema::getColumnListing($table);
            $columns = array_values(array_intersect($sourceColumns, $targetColumns));

            if ($columns === []) {
                continue;
            }

            $quotedColumns = implode(', ', array_map(
                static fn (string $column): string => '"' . str_replace('"', '""', $column) . '"',
                $columns
            ));

            $rows = $source->query('SELECT ' . $quotedColumns . ' FROM ' . $quotedTable);
            $tableImported = 0;

            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($columns as $column) {
                    $values[$column] = $row[$column] ?? null;
                }

                $tableImported += $mysql->table($table)->insertOrIgnore($values);
            }

            if ($tableImported > 0) {
                $importedTables++;
                $importedRows += $tableImported;
                echo "Importada {$table}: {$tableImported} registro(s).\n";
            }
        }
    } finally {
        $mysql->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    unlink($markerPath);

    echo "Importacion SQLite finalizada. {$importedRows} registro(s) importados en {$importedTables} tabla(s).\n";
    echo "El archivo SQLite original se conserva como respaldo.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "ERROR: No se pudo importar la base SQLite anterior. El archivo original se conserva.\n");
    fwrite(STDERR, "Detalle: " . $exception->getMessage() . "\n");
    exit(1);
}
