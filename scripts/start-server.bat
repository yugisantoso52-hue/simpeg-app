@echo off
echo ===================================================
echo   Memulai Apache dan MySQL untuk SIMPEG...
echo ===================================================

:: 1. Cek & Jalankan MySQL
netstat -ano | findstr :3306 | findstr LISTENING >nul
if %errorlevel% equ 0 (
    echo [OK] MySQL sudah berjalan di port 3306.
) else (
    echo [..] Menjalankan MySQL...
    start "" /B "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini" --standalone
    timeout /t 3 >nul
)

:: 2. Cek & Jalankan Apache
netstat -ano | findstr :80 | findstr LISTENING >nul
if %errorlevel% equ 0 (
    echo [OK] Apache sudah berjalan di port 80.
) else (
    echo [..] Menjalankan Apache...
    start "" /B "C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe" -d "C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18" -f "C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\conf\httpd.conf"
    timeout /t 2 >nul
)

echo.
echo ===================================================
echo   Status Server:
echo   Web:   http://simpeg.test atau http://172.30.22.156
echo ===================================================
timeout /t 3
