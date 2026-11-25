@echo off
setlocal EnableExtensions
title PlayLab - Preparar Laravel 13 en la carpeta principal

set "ROOT=%~dp0"
set "TEMP_PROJECT=%TEMP%\playlab-laravel13-%RANDOM%-%RANDOM%"

echo.
echo  ============================================
echo        PLAYLAB - INSTALADOR LARAVEL 13
echo  ============================================
echo.
echo Este instalador prepara Laravel dentro de:
echo %ROOT%
echo.

if exist "%ROOT%artisan" (
  echo Ya se encontro artisan en esta carpeta.
  echo No se sobrescribira una instalacion existente.
  echo Ejecuta manualmente: composer install
  pause
  exit /b 0
)

where composer >nul 2>nul
if errorlevel 1 (
  echo ERROR: Composer no aparece en PATH.
  echo Abre la terminal de Laragon y vuelve a ejecutar.
  pause
  exit /b 1
)
where php >nul 2>nul
if errorlevel 1 (
  echo ERROR: PHP no aparece en PATH.
  echo Abre la terminal de Laragon y vuelve a ejecutar.
  pause
  exit /b 1
)

echo [1/4] Descargando una base limpia de Laravel 13...
composer create-project "laravel/laravel:^13.0" "%TEMP_PROJECT%"
if errorlevel 1 goto error

echo [2/4] Combinando Laravel con el codigo PlayLab de esta carpeta...
robocopy "%TEMP_PROJECT%" "%ROOT%" /E /R:2 /W:1 /XF README.md app.css app.js web.php DatabaseSeeder.php /XD .git
if errorlevel 8 goto error

echo [3/4] Comprobando que artisan este disponible...
if not exist "%ROOT%artisan" (
  echo ERROR: No se encontro artisan en la carpeta principal.
  goto error
)

echo [4/4] Preparacion del framework completada.
if exist "%TEMP_PROJECT%" rmdir /S /Q "%TEMP_PROJECT%"

echo.
echo Laravel 13 y PlayLab quedaron combinados en la carpeta principal.
echo.
echo SIGUIENTE:
echo 1. Crea la base de datos MySQL llamada playlab.
echo 2. Revisa las credenciales de .env.
echo 3. Ejecuta los comandos indicados en README.md.
echo.
pause
exit /b 0

:error
echo.
echo La instalacion no termino correctamente.
echo Revisa el mensaje de Composer o Robocopy.
echo El proyecto temporal se conserva en:
echo %TEMP_PROJECT%
pause
exit /b 1
