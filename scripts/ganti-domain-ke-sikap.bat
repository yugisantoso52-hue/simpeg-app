@echo off
:: Cek apakah dijalankan sebagai Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [INFO] Meminta izin Administrator...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo ===================================================
echo   Mengatur Domain: http://sikap.fkpunri
echo ===================================================
echo.

set HOSTS=%WINDIR%\System32\drivers\etc\hosts

:: Cek apakah sudah ada di hosts
findstr /i "sikap.fkpunri" "%HOSTS%" >nul
if %errorLevel% equ 0 (
    echo [OK] Domain sikap.fkpunri sudah terdaftar di file hosts.
) else (
    echo [..] Menambahkan sikap.fkpunri ke Windows hosts...
    echo 127.0.0.1      sikap.fkpunri          #simpeg unri >> "%HOSTS%"
    echo [OK] Berhasil ditambahkan!
)

:: Flush DNS
echo [..] Membersihkan DNS Cache...
ipconfig /flushdns >nul

echo.
echo ===================================================
echo   SUKSES!
echo   Sekarang Anda bisa membuka:
echo   http://sikap.fkpunri
echo ===================================================
pause
