@echo off
setlocal

where php >nul 2>nul
if %ERRORLEVEL%==0 (
    set "PHP=php"
) else (
    set "PHP=D:\xampp\php\php.exe"
)

echo Starting Restaurant POS on http://localhost:8080
echo PHP: %PHP%
echo.

"%PHP%" -d extension=intl -d extension=sqlite3 -S 127.0.0.1:8080 -t public public/index.php
