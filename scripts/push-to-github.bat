@echo off
echo ============================================
echo  SIMPEG - Push ke GitHub
echo ============================================
echo.
echo [INFO] Pastikan Laragon sedang berjalan
echo [INFO] Akan push dari: C:\laragon\www\simpeg
echo [INFO] Ke repo: https://github.com/yugisantoso52-hue/simpeg-app
echo.
set GIT=C:\laragon\bin\git\bin\git.exe
set REPO=C:\laragon\www\simpeg
set GCM=C:\Users\OPAC\AppData\Local\GitHubDesktop\app-3.6.4\resources\app\git\mingw64\bin\git-credential-manager.exe
set PHPRC=C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64

"%GIT%" -C "%REPO%" config credential.helper "%GCM%"
"%GIT%" -C "%REPO%" add .
echo.
set /p msg=Pesan commit (Enter untuk skip jika tidak ada perubahan): 
if "%msg%"=="" (
    echo Tidak ada commit baru, langsung push...
) else (
    "%GIT%" -C "%REPO%" commit -m "%msg%"
)
echo.
echo [INFO] Pushing ke GitHub... (browser mungkin akan muncul untuk login)
"%GIT%" -C "%REPO%" push origin main
echo.
echo ============================================
echo  SELESAI!
echo ============================================
pause
