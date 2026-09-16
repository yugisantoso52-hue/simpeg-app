# =============================================================================
# SETUP WINDOWS TASK SCHEDULER - SIKAP BACKUP OTOMATIS (STRUKTUR 2)
# Jalankan script ini SEKALI sebagai Administrator
# Klik kanan PowerShell -> "Run as Administrator" -> jalankan script ini
# =============================================================================

Write-Host "================================================" -ForegroundColor Cyan
Write-Host " SETUP TASK SCHEDULER - SIKAP CLOUD BACKUP      " -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

$ScriptDir = "C:\laragon\www\simpeg\scripts"

# --- Buat direktori backup ---
$dirs = @(
    "C:\simpeg-backup\db",
    "C:\simpeg-backup\files",
    "C:\simpeg-backup\logs"
)

foreach ($dir in $dirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "[OK] Dibuat folder: $dir" -ForegroundColor Green
    } else {
        Write-Host "[SKIP] Folder sudah ada: $dir" -ForegroundColor Yellow
    }
}

Write-Host ""

# =============================================================================
# TASK: Backup Satu Arah (Railway DB + Cloudflare R2) - Setiap hari jam 02:00
# =============================================================================
$taskName    = "SikapBackupCloudToLocal"
$taskScript  = "$ScriptDir\backup-cloud-to-local.ps1"

$taskAction  = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument "-NonInteractive -NoProfile -ExecutionPolicy Bypass -File `"$taskScript`""

$taskTrigger = New-ScheduledTaskTrigger -Daily -At "02:00"

$taskSettings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit (New-TimeSpan -Hours 2) `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 5) `
    -StartWhenAvailable

# Hapus task lama jika ada
if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
    Write-Host "[UPDATE] Task lama '$taskName' dihapus." -ForegroundColor Yellow
}

# Hapus legacy task jika ada
foreach ($old in @("SimpegBackupDatabase", "SimpegSyncGoogleDrive")) {
    if (Get-ScheduledTask -TaskName $old -ErrorAction SilentlyContinue) {
        Unregister-ScheduledTask -TaskName $old -Confirm:$false
        Write-Host "[CLEANUP] Task lawas '$old' dinonaktifkan." -ForegroundColor Yellow
    }
}

Register-ScheduledTask `
    -TaskName $taskName `
    -Action $taskAction `
    -Trigger $taskTrigger `
    -Settings $taskSettings `
    -Description "Pencadangan satu arah (Railway MySQL + Cloudflare R2 ke PC Lokal) setiap hari jam 02:00 WIB" `
    -RunLevel Highest | Out-Null

Write-Host "[OK] Task dibuat: $taskName (setiap hari 02:00 WIB)" -ForegroundColor Green
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host " SETUP TASK SCHEDULER SELESAI!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Cek di: Task Scheduler -> Task Scheduler Library" -ForegroundColor White
Write-Host "  - $taskName : jam 02:00 setiap hari" -ForegroundColor White