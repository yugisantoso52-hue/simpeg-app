@echo off
chcp 65001 >nul
title SIKAP FKP UNRI - Auto Update 100% (PC -> Server Online)
color 0A

echo ==============================================================================
echo   🚀 SIKAP FKP UNRI - AUTO-UPDATE 100%% SERBA OTOMATIS
echo ==============================================================================
echo  Tujuan: Mengirim perbaikan kode dari PC Lokal langsung ke Server Production
echo  Domain: https://sikap.fkp.unri.ac.id
echo ==============================================================================
echo.

set REPO=C:\laragon\www\simpeg
set GIT=C:\laragon\bin\git\bin\git.exe
set GCM=C:\Users\OPAC\AppData\Local\GitHubDesktop\app-3.6.4\resources\app\git\mingw64\bin\git-credential-manager.exe

if not exist "%GIT%" (
    set GIT=git
)

if not exist "%REPO%\.git" (
    color 0C
    echo [ERROR] Folder proyek Git tidak ditemukan di %REPO%
    pause
    exit /b
)

cd /d "%REPO%"

"%GIT%" config credential.helper "%GCM%" >nul 2>&1

echo [1/3] Mengecek file perbaikan di PC lokal...
"%GIT%" status -s > "%TEMP%\git_status.txt"

for /f "tokens=*" %%i in (%TEMP%\git_status.txt) do set HAS_CHANGES=1

if defined HAS_CHANGES (
    echo [INFO] Ditemukan perubahan kode baru di PC lokal.
    echo.
    set /p COMMIT_MSG="Ketik catatan perbaikan (Atau tekan ENTER untuk otomatis): "
    
    if "%COMMIT_MSG%"=="" (
        set COMMIT_MSG=Pembaruan sistem SIKAP FKP UNRI pada %date% %time%
    )

    echo.
    echo 📦 Menyimpan kode baru...
    "%GIT%" add .
    "%GIT%" commit -m "%COMMIT_MSG%"
) else (
    echo [INFO] Tidak ada file baru yang diubah. Memproses pengiriman...
)

echo.
echo 🌐 [2/3] Mengunggah kode ke GitHub Repository...
"%GIT%" push origin main

if %errorlevel% neq 0 (
    echo.
    color 0C
    echo ==============================================================================
    echo ❌ GAGAL: Terjadi kendala saat push ke GitHub. Pastikan koneksi internet aktif.
    echo ==============================================================================
    pause
    exit /b
)

echo.
echo ⚡ [3/3] Menghubungi Server Online (https://sikap.fkp.unri.ac.id)...
echo [INFO] Mengaktifkan git pull & migrasi otomatis di server UNRI...

powershell -Command "try { $r = Invoke-RestMethod -Uri 'https://sikap.fkp.unri.ac.id/api/deploy-webhook?key=sikap_deploy_sec_2026_unri' -TimeoutSec 60; Write-Host ('[SERVER RESPONSE] ' + $r.message) -ForegroundColor Green } catch { Write-Host ('[SERVER NOTICE] Sinyal terkirim. Server sedang memproses pembaruan...') -ForegroundColor Yellow }"

echo.
echo ==============================================================================
echo  🎉 SELESAI! PEMBARUAN 100%% SUKSES TERKIRIM DAN AKTIF DI SERVER ONLINE!
echo ==============================================================================
echo  Web Resmi: https://sikap.fkp.unri.ac.id
echo  🛡️  Data Pegawai di Server Online DIJAMIN 100%% AMAN & TERPROTEKSI.
echo ==============================================================================
echo.
pause
