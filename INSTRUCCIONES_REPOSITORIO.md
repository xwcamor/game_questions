# Instalación de PlayLab en game_questions

El código actual está en la carpeta principal del repositorio. No existe una subcarpeta `playlab-laravel13`.

## Si ya clonaste el proyecto

Desde Laragon, en `C:\laragon\www\game_questions`:

```bat
git pull origin main
INSTALAR.bat
```

El instalador está corregido para Windows: utiliza `CALL` al ejecutar Composer y npm, reutiliza una descarga temporal de Laravel que haya quedado de un intento anterior y copia los archivos del framework que faltan a la raíz. Los archivos propios del proyecto, `.git` y `old/` se conservan.

## Después de instalar

1. Crea la base de datos MySQL `playlab`.
2. Configura `DB_CONNECTION=mysql`, `DB_DATABASE=playlab`, `DB_USERNAME` y `DB_PASSWORD` en `.env`.
3. Ejecuta `php artisan migrate --seed`.
4. Ejecuta `php artisan serve`.

Consulta `README.md` para las cuentas iniciales, pruebas y configuración completa.