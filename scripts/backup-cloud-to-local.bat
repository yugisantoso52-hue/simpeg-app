@echo off
title SIKAP - Backup Cloud ke PC Lokal
echo ===================================================
echo   SIKAP - PENCADANGAN SATU ARAH (CLOUD -^> PC LOKAL)
echo ===================================================
echo.
echo Menjalankan skrip backup (Railway MySQL + Cloudflare R2)...
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0backup-cloud-to-local.ps1"

echo.
echo ===================================================
echo   Proses backup selesai!
echo   File tersimpan di C:\simpeg-backup
echo ===================================================
pause
