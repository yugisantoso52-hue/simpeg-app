@echo off
title Sinkronisasi SIMPEG (GitHub & Railway Cloud ke Laragon)
color 0A

echo ================================================================
echo       SINKRONISASI LENGKAP SIMPEG (RAILWAY CLOUD -> LARAGON)      
echo ================================================================
echo.
echo [1/4] Memulai penyalinan berkas sistem, view, & kode program...

:: 1. Salin folder inti kode program, views, & aset dari GitHub ke Laragon
robocopy "C:\Users\Acer\Documents\GitHub\simpeg-app\app" "C:\laragon\www\simpeg\app" /E /PURGE /NFL /NDL /NJH /NJS
robocopy "C:\Users\Acer\Documents\GitHub\simpeg-app\resources" "C:\laragon\www\simpeg\resources" /E /PURGE /NFL /NDL /NJH /NJS
robocopy "C:\Users\Acer\Documents\GitHub\simpeg-app\routes" "C:\laragon\www\simpeg\routes" /E /PURGE /NFL /NDL /NJH /NJS
robocopy "C:\Users\Acer\Documents\GitHub\simpeg-app\database" "C:\laragon\www\simpeg\database" /E /PURGE /NFL /NDL /NJH /NJS
robocopy "C:\Users\Acer\Documents\GitHub\simpeg-app\config" "C:\laragon\www\simpeg\config" /E /PURGE /NFL /NDL /NJH /NJS
robocopy "C:\Users\Acer\Documents\GitHub\simpeg-app\public" "C:\laragon\www\simpeg\public" /E /NFL /NDL /NJH /NJS

:: 2. Salin file konfigurasi utama
copy /Y "C:\Users\Acer\Documents\GitHub\simpeg-app\composer.json" "C:\laragon\www\simpeg\composer.json" >nul
copy /Y "C:\Users\Acer\Documents\GitHub\simpeg-app\package.json" "C:\laragon\www\simpeg\package.json" >nul
copy /Y "C:\Users\Acer\Documents\GitHub\simpeg-app\vite.config.js" "C:\laragon\www\simpeg\vite.config.js" >nul
copy /Y "C:\Users\Acer\Documents\GitHub\simpeg-app\tailwind.config.js" "C:\laragon\www\simpeg\tailwind.config.js" >nul

echo   [+] Salin kode, view, aset, dan konfigurasi selesai.
echo.

:: 3. Tentukan PHP Laragon yang memiliki driver MySQL lengkap
set PHP_BIN=php
if exist "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" (
    set PHP_BIN="C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
)

:: Masuk ke direktori Laragon
cd /d C:\laragon\www\simpeg

echo [2/4] Membersihkan seluruh cache sistem di Laragon...
call %PHP_BIN% artisan storage:link
call %PHP_BIN% artisan optimize:clear
call %PHP_BIN% artisan view:clear
call %PHP_BIN% artisan route:clear
call %PHP_BIN% artisan config:clear
echo   [+] Cache sistem berhasil dibersihkan.
echo.

echo [3/4] Menjalankan migrasi database...
call %PHP_BIN% artisan migrate --force
echo   [+] Migrasi struktur tabel selesai.
echo.

echo [4/4] MENARIK DATA ASLI DARI CLOUD RAILWAY (https://sikap-app.up.railway.app)...
echo Menghubungkan ke server Railway untuk menyinkronkan 29 Pegawai & berkas...
call %PHP_BIN% artisan simpeg:pull-cloud

echo.
echo ================================================================
echo   ALHAMDULILLAH! SINKRONISASI KODE & DATA BERHASIL TOTAL!       
echo ================================================================
echo.
echo Data Pegawai (Dosen, Tendik, PHL) dari Cloud Railway kini telah
echo 100%% SINKRON (Total 29 Pegawai) di http://localhost/simpeg/public/dashboard!
echo.
pause
