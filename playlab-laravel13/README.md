# PlayLab — Quiz multijugador para Laravel 13

Aplicación de preguntas multijugador con anfitriones, cuentas, salas públicas y privadas, códigos de acceso, ranking y 40 preguntas de demostración.

## Instalación en Laragon

Consulta el README completo incluido en este directorio. El proyecto requiere Laravel 13, PHP 8.3+, Composer, Node.js y MySQL.

```bash
composer create-project "laravel/laravel:^13.0" playlab
# Copia las carpetas app, database, resources, routes y tests de este paquete a playlab
cd playlab
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Cuenta inicial: `admin@kahoot.local` / `DemoAdmin2026!`. Cámbiala antes de publicar.

El sincronizado de la sala en esta entrega usa polling HTTP (aprox. 1.1 segundos); no es WebSocket. Para detalles y límites, revisa la documentación completa.