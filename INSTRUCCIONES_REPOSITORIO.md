# Descargar y preparar PlayLab desde GitHub

El proyecto está en la **carpeta principal** del repositorio. No busques una carpeta `playlab-laravel13`.

```bat
cd C:\laragon\www
git clone https://github.com/xwcamor/game_questions.git
cd game_questions
```

Luego ejecuta `INSTALAR.bat` desde la terminal de Laragon. El script prepara el framework Laravel 13 y combina sus archivos con el código de PlayLab en esta misma carpeta, sin sobrescribir las rutas, vistas, estilos ni seeders personalizados.

Después crea la base MySQL `playlab`, configura `.env` y ejecuta los comandos de migración y compilación que aparecen en `README.md`.

Los archivos antiguos de evaluaciones se conservan en `old/`.
