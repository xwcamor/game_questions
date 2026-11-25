@echo off
setlocal EnableExtensions
title PlayLab - Preparar Laravel 13 en la carpeta principal

set "ROOT=%~dp0"
set "TEMP_PROJECT="

echo.
echo  ============================================
echo        PLAYLAB - INSTALADOR LARAVEL 13
echo  ============================================
echo.
echo Este instalador prepara Laravel dentro de:
echo %ROOT%
echo.

REM Si artisan ya existe, el framework ya fue copiado. Continuar con la preparacion.
if exist "%ROOT%artisan" goto finish_setup

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

REM Reutilizar el Laravel temporal creado por una instalacion interrumpida.
for /d %%D in ("%TEMP%\playlab-laravel13-*") do (
  if exist "%%~fD\artisan" set "TEMP_PROJECT=%%~fD"
)

if not defined TEMP_PROJECT goto create_temp_project
echo Se encontro una descarga temporal anterior:
echo %TEMP_PROJECT%
goto merge_temp_project

:create_temp_project
set "TEMP_PROJECT=%TEMP%\playlab-laravel13-%RANDOM%-%RANDOM%"
echo [1/5] Descargando una base limpia de Laravel 13...
REM IMPORTANTE: Composer suele ser composer.bat en Windows.
REM CALL permite que este instalador continue cuando Composer termine.
call composer create-project "laravel/laravel:^13.0" "%TEMP_PROJECT%" --no-interaction
if errorlevel 1 goto error

:merge_temp_project
if not exist "%TEMP_PROJECT%\artisan" (
  echo ERROR: No se encontro artisan dentro del proyecto temporal.
  goto error
)

echo.
echo [2/5] Copiando los archivos base a la carpeta principal...
echo Se conservaran todos los archivos existentes de PlayLab y Git.
REM /XC /XN /XO evita reemplazar archivos que ya existan en el repositorio.
REM Solo se copian archivos que aun no existen en el destino.
REM No copiamos vendor, node_modules, .git ni el .env temporal.
robocopy "%TEMP_PROJECT%" "%ROOT%." /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /XC /XN /XO /XD .git vendor node_modules /XF .env
set "ROBOCOPY_CODE=%ERRORLEVEL%"
if %ROBOCOPY_CODE% GEQ 8 goto error

if not exist "%ROOT%artisan" (
  echo ERROR: No se pudo copiar artisan a la carpeta principal.
  goto error
)
if not exist "%ROOT%composer.json" (
  echo ERROR: No se copio composer.json.
  goto error
)
if not exist "%ROOT%package.json" (
  echo ERROR: No se copio package.json.
  goto error
)

:finish_setup
cd /d "%ROOT%"

echo.
echo [3/5] Preparando el archivo .env...
if not exist "%ROOT%.env" (
  if exist "%ROOT%.env.example" (
    copy /Y "%ROOT%.env.example" "%ROOT%.env" >nul
  ) else (
    echo ERROR: No existe .env.example.
    goto error
  )
)

echo.
echo [4/5] Instalando dependencias PHP...
call composer install --no-interaction --prefer-dist
if errorlevel 1 goto error

echo.
echo Generando la clave de aplicacion...
php artisan key:generate --force
if errorlevel 1 goto error

echo.
echo [5/7] Configurando MySQL y creando la base de datos...
if not exist "%ROOT%scripts\setup-database.php" (
  echo ERROR: No se encontro scripts\setup-database.php.
  goto error
)
php scripts\setup-database.php
if errorlevel 1 goto error

echo.
echo Limpiando configuracion cacheada...
php artisan config:clear
if errorlevel 1 goto error

echo.
echo [6/7] Ejecutando migraciones y cargando datos iniciales...
php artisan migrate --seed --force
if errorlevel 1 goto error

echo.
echo [7/7] Instalando y compilando recursos web...
where npm >nul 2>nul
if errorlevel 1 (
  echo AVISO: npm no aparece en PATH. La preparacion PHP y MySQL termino.
  echo Instala Node.js LTS y luego ejecuta: npm install ^&^& npm run build
  goto success
)
call npm install
if errorlevel 1 goto error
call npm run build
if errorlevel 1 goto error

:success
if exist "%TEMP_PROJECT%" rmdir /S /Q "%TEMP_PROJECT%" >nul 2>nul
echo.
echo  ============================================
echo       PLAYLAB QUEDO PREPARADO EN LA RAIZ
echo  ============================================
echo.
echo La base de datos MySQL playlab fue creada/verificada por codigo.
echo Las tablas y los datos iniciales ya fueron cargados.
echo.
echo SIGUIENTE:
echo Ejecuta: php artisan serve
echo.
echo Configuracion predeterminada: MySQL de Laragon (root sin contrasena).
echo Si tu MySQL usa otra clave, ajusta DB_PASSWORD en .env y vuelve a ejecutar INSTALAR.bat.
echo La carpeta old y tus archivos personalizados se conservaron.
pause
exit /b 0

:error
echo.
echo  ============================================
echo       LA INSTALACION NO TERMINO
echo  ============================================
echo.
echo Revisa el error anterior. No borres manualmente los archivos.
echo Carpeta del proyecto: %ROOT%
if exist "%TEMP_PROJECT%\artisan" echo Los archivos temporales se conservan en: %TEMP_PROJECT%
pause
exit /b 1
