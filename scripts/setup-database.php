<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$envPath = $projectRoot . DIRECTORY_SEPARATOR . '.env';

if (!is_file($envPath)) {
    fwrite(STDERR, "ERROR: No existe el archivo .env en la raiz del proyecto.\n");
    exit(1);
}

$envContents = file_get_contents($envPath);
if ($envContents === false) {
    fwrite(STDERR, "ERROR: No se pudo leer el archivo .env.\n");
    exit(1);
}

function readEnvValue(string $contents, string $key, string $default = ''): string
{
    $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';

    if (!preg_match($pattern, $contents, $matches)) {
        return $default;
    }

    $value = trim($matches[1]);

    if (strlen($value) >= 2) {
        $first = $value[0];
        $last = $value[strlen($value) - 1];

        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        }
    }

    return $value;
}

function writeEnvValue(string &$contents, string $key, string $value): void
{
    $line = $key . '=' . $value;
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

    if (preg_match($pattern, $contents)) {
        $contents = preg_replace_callback($pattern, static fn (): string => $line, $contents, 1);
    } else {
        $contents = rtrim($contents) . PHP_EOL . $line . PHP_EOL;
    }
}

$connection = strtolower(readEnvValue($envContents, 'DB_CONNECTION', 'sqlite'));

if ($connection === 'sqlite' || $connection === '') {
    // Guardar la ruta de la SQLite anterior para importar sus usuarios y cuestionarios
    // a MySQL despues de crear las tablas nuevas.
    $sqliteDatabase = readEnvValue($envContents, 'DB_DATABASE', 'database/database.sqlite');
    $sqlitePath = $sqliteDatabase;

    if ($sqlitePath !== '' && !preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $sqlitePath)) {
        $sqlitePath = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sqlitePath);
    }

    if ($sqlitePath !== '' && is_file($sqlitePath)) {
        $markerDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app';
        if (!is_dir($markerDirectory)) {
            mkdir($markerDirectory, 0775, true);
        }
        file_put_contents($markerDirectory . DIRECTORY_SEPARATOR . 'playlab-sqlite-import-source.txt', realpath($sqlitePath) ?: $sqlitePath, LOCK_EX);
        echo "Se encontro una base SQLite anterior. Se conservara y sus datos se importaran a MySQL.\\n";
    }

    // Convertimos ese .env al perfil MySQL de Laragon.
    writeEnvValue($envContents, 'DB_CONNECTION', 'mysql');
    writeEnvValue($envContents, 'DB_HOST', '127.0.0.1');
    writeEnvValue($envContents, 'DB_PORT', '3306');
    writeEnvValue($envContents, 'DB_DATABASE', 'playlab');
    writeEnvValue($envContents, 'DB_USERNAME', 'root');
    writeEnvValue($envContents, 'DB_PASSWORD', '');
    writeEnvValue($envContents, 'DB_URL', 'null');
    echo "Se detecto la configuracion SQLite anterior. El archivo .env se cambio a MySQL (Laragon).\n";
}

if (file_put_contents($envPath, $envContents, LOCK_EX) === false) {
    fwrite(STDERR, "ERROR: No se pudo guardar la configuracion MySQL en .env.\n");
    exit(1);
}

$host = readEnvValue($envContents, 'DB_HOST', '127.0.0.1');
$port = readEnvValue($envContents, 'DB_PORT', '3306');
$database = readEnvValue($envContents, 'DB_DATABASE', 'playlab');
$username = readEnvValue($envContents, 'DB_USERNAME', 'root');
$password = readEnvValue($envContents, 'DB_PASSWORD', '');

if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    fwrite(STDERR, "ERROR: DB_DATABASE solo puede contener letras, numeros y guion bajo.\n");
    exit(1);
}

if (!extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "ERROR: PHP no tiene habilitada la extension pdo_mysql. Activa extension=pdo_mysql en el php.ini de Laragon y vuelve a ejecutar INSTALAR.bat.\n");
    exit(1);
}

try {
    $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 8,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $quotedDatabase = chr(96) . $database . chr(96);
    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS ' . $quotedDatabase .
        ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    echo "MySQL conectado correctamente.\n";
    echo "Base de datos '{$database}' creada o ya existente.\n";
} catch (PDOException $exception) {
    fwrite(STDERR, "ERROR: No se pudo crear/verificar la base de datos MySQL.\n");
    fwrite(STDERR, "Comprueba que MySQL este iniciado en Laragon y que DB_HOST, DB_PORT, DB_USERNAME y DB_PASSWORD en .env sean correctos.\n");
    fwrite(STDERR, "Detalle: " . $exception->getMessage() . "\n");
    exit(1);
}
