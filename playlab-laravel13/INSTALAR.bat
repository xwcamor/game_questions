@echo off
setlocal
set "ROOT=%~dp0"
for %%I in ("%ROOT%..") do set "PARENT=%%~fI"
set "TARGET=%PARENT%\playlab-app"
echo ===========================================
echo   PLAYLAB - INSTALADOR PARA LARAVEL 13
echo ===========================================
echo.
if exist "%TARGET%\artisan" (
  echo Ya existe una instalacion en "%TARGET%".
  echo No se modifico para evitar sobrescribir archivos.
  pause
  exit /b 1
)
where composer >nul 2>nul
if errorlevel 1 (
  echo ERROR: Composer no aparece en PATH. Abre la terminal de Laragon.
  pause
  exit /b 1
)
where php >nul 2>nul
if errorlevel 1 (
  echo ERROR: PHP no aparece en PATH. Abre la terminal de Laragon.
  pause
  exit /b 1
)
echo [1/3] Creando Laravel 13 limpio...
composer create-project "laravel/laravel:^13.0" "%TARGET%"
if errorlevel 1 goto error
echo [2/3] Copiando archivos PlayLab...
for %%D in (app database resources routes tests) do (
  if exist "%ROOT%%%D" xcopy "%ROOT%%%D\*" "%TARGET%\%%D\" /E /I /Y >nul
)
if errorlevel 1 goto error
echo [3/3] Proyecto creado.
echo Ahora configura MySQL y sigue el README.md.
echo Carpeta: %TARGET%
pause
exit /b 0
:error
echo La instalacion no termino correctamente. Revisa el error mostrado.
pause
exit /b 1
