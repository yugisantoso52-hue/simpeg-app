# =============================================================================
# SIMPEG - Script Sync ke GitHub
# Jalankan script ini untuk push perubahan kode ke GitHub
# Usage: klik kanan > "Run with PowerShell" atau jalankan di terminal
# =============================================================================

$GIT = "C:\laragon\bin\git\bin\git.exe"
$REPO = "C:\laragon\www\simpeg"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SIMPEG - Sync ke GitHub" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Cek status
Write-Host "[1/4] Cek status perubahan..." -ForegroundColor Yellow
$status = &$GIT -C $REPO status --short
if (-not $status) {
    Write-Host "  Tidak ada perubahan. Sudah sync!" -ForegroundColor Green
    Read-Host "`nTekan Enter untuk keluar"
    exit 0
}
Write-Host "  Ada $($status.Count) file yang berubah:" -ForegroundColor White
$status | ForEach-Object { Write-Host "    $_" -ForegroundColor Gray }

# Stage semua
Write-Host "`n[2/4] Stage semua perubahan..." -ForegroundColor Yellow
&$GIT -C $REPO add .
Write-Host "  Done." -ForegroundColor Green

# Commit
Write-Host "`n[3/4] Commit..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm"
$defaultMsg = "update: $timestamp"
$commitMsg = Read-Host "  Pesan commit (Enter untuk pakai: '$defaultMsg')"
if ([string]::IsNullOrWhiteSpace($commitMsg)) { $commitMsg = $defaultMsg }

&$GIT -C $REPO commit -m $commitMsg
Write-Host "  Commit berhasil." -ForegroundColor Green

# Push
Write-Host "`n[4/4] Push ke GitHub..." -ForegroundColor Yellow
&$GIT -C $REPO push origin main 2>&1 | ForEach-Object { Write-Host "  $_" }
Write-Host ""

# Done
Write-Host "========================================" -ForegroundColor Green
Write-Host "  SELESAI! Kode sudah di-push ke GitHub" -ForegroundColor Green
Write-Host "  https://github.com/yugisantoso52-hue/simpeg-app" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Read-Host "`nTekan Enter untuk keluar"
