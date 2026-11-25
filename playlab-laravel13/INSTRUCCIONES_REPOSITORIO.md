# PlayLab en game_questions

Esta carpeta agrupa el paquete PlayLab para Laravel 13. El resto del repositorio conserva las evaluaciones HTML y el ZIP previo.

## Descarga

Clona o descarga el repositorio completo y entra a `playlab-laravel13/`.

```bash
git clone https://github.com/xwcamor/game_questions.git
cd game_questions/playlab-laravel13
```

## Instalación automática en Windows/Laragon

Ejecuta `INSTALAR.bat` desde la terminal de Laragon. El script crea `playlab-app` en la carpeta superior y no sobrescribe una carpeta destino existente.

## Instalación manual

Crear una aplicación Laravel 13 limpia, copiar el contenido de esta carpeta sobre el proyecto (fusionando `app`, `database`, `resources`, `routes` y `tests`) y seguir `README.md`.

**Importante:** este paquete no contiene `vendor/`, `.env`, credenciales, ni el framework Laravel. No se debe subir `.env` al repositorio.