# =============================================================================
# SCRIPT SYNC BACKUP KE GOOGLE DRIVE via rclone
# Dijalankan otomatis oleh Windows Task Scheduler setiap hari jam 03:00
# Pastikan rclone sudah dikonfigurasi terlebih dahulu (lihat PANDUAN-SETUP.md)
# =============================================================================

param(
    [string]$RcloneRemote   = "gdrive",
    [string]$RemoteFolder   = "gdrive:simpeg-backup",
    [string]$LocalBackupDir = "C:\simpeg-backup",
    [string]$RclonePath     = "C:\rclone\rclone.exe",
    [string]$LogFile        = "C:\simpeg-backup\logs\rclone.log"
)

# --- Fungsi Logging ---
function Write-Log {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $entry = "[$timestamp] $Message"
    Write-Host $entry -ForegroundColor $Color
    Add-Content -Path $LogFile -Value $entry
}

# --- Cek Folder Log ---
$logDir = Split-Path $LogFile
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}

# --- Cek rclone tersedia ---
if (-not (Test-Path $RclonePath)) {
    $rcloneCmd = Get-Command rclone -ErrorAction SilentlyContinue
    if ($rcloneCmd) {
        $RclonePath = $rcloneCmd.Source
    } else {
        Write-Log "ERROR: rclone tidak ditemukan di $RclonePath maupun PATH." "Red"
        Write-Log "Pastikan rclone sudah diinstall dan dikonfigurasi." "Red"
        exit 1
    }
}

Write-Log "============================================" "Cyan"
Write-Log "Mulai sync backup ke Google Drive via rclone ($RclonePath)..." "Cyan"

# --- Jalankan rclone sync ---
& $RclonePath sync $LocalBackupDir $RemoteFolder `
    --log-file $LogFile `
    --log-level INFO `
    --transfers 4 `
    --checkers 8 `
    --contimeout 60s `
    --timeout 300s `
    --retries 3 `
    --low-level-retries 10 `
    --stats 1m

if ($LASTEXITCODE -eq 0) {
    Write-Log "Sync ke Google Drive BERHASIL." "Green"
} else {
    Write-Log "ERROR: Sync ke Google Drive GAGAL dengan exit code $LASTEXITCODE" "Red"
    exit 1
}

Write-Log "Sync selesai." "Green"
Write-Log "============================================" "Cyan"