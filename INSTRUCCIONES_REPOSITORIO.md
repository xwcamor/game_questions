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

El propio instalador configura MySQL, crea la base de datos `playlab` si no existe, ejecuta las migraciones, importa los datos de la SQLite anterior si existe y carga los datos iniciales. El archivo SQLite original se conserva. En Laragon utiliza por defecto `root` sin contraseña. Si tu MySQL usa otra contraseña, ajusta `DB_PASSWORD` en `.env` y vuelve a ejecutar `INSTALAR.bat`.

Al finalizar, solo ejecuta:

```bat
php artisan serve
```

Consulta `README.md` para las cuentas iniciales, pruebas y configuración completa.