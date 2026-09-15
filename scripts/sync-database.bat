@echo off
echo ===================================================
echo   SIMPEG - SINKRONISASI DATABASE (LOKAL ^<--^> CLOUD)
echo ===================================================
echo.
echo Menghubungkan PC Lokal dengan Server Railway:
echo https://sikap-app.up.railway.app
echo.

set PHPRC=C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64
set PHP=C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe

"%PHP%" "C:\laragon\www\simpeg\artisan" simpeg:sync

echo.
echo ===================================================
echo   Proses sinkronisasi selesai!
echo ===================================================
pause
