@echo off
setlocal

echo ============================================
echo   Limpieza de cache - Gestion Academica
echo ============================================
echo.

cd /d "%~dp0..\laravel"

echo Limpiando config, rutas, vistas y cache de la aplicacion...
php artisan optimize:clear --ansi

if errorlevel 1 (
    echo.
    echo Algo fallo. Revisa que XAMPP/PHP este en el PATH y que
    echo el archivo .env exista en laravel\.env
    echo.
    pause
    exit /b 1
)

echo.
echo Listo. Si el problema era un CSRF token mismatch en el login,
echo tambien refresca (F5) la pestana del navegador donde estaba
echo abierto el formulario: el token viejo queda en esa pagina
echo hasta que la recargues, esto no lo arregla la cache del servidor.
echo.
pause
