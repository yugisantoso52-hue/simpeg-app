# =============================================================================
# SCRIPT BACKUP DATABASE OTOMATIS - SIMPEG-APP
# Dijalankan otomatis oleh Windows Task Scheduler setiap hari jam 02:00
# =============================================================================

param(
    [string]$ContainerName = "simpeg-db",
    [string]$DbName = "simpeg",
    [string]$DbUser = "root",
    [string]$DbPassword = $env:DB_PASSWORD,
    [string]$BackupBaseDir = "C:\simpeg-backup\db",
    [string]$LogFile = "C:\simpeg-backup\logs\backup.log",
    [int]$KeepDays = 30
)

# Baca password dari .env jika tidak disuplai lewat argumen atau env var
if (-not $DbPassword) {
    $envFile = Join-Path $PSScriptRoot "..\.env"
    if (Test-Path $envFile) {
        Get-Content $envFile | ForEach-Object {
            $line = $_.Trim()
            if ($line -and -not $line.StartsWith("#") -and $line.Contains("=")) {
                $parts = $line.Split("=", 2)
                $k = $parts[0].Trim()
                $v = $parts[1].Trim().Trim('"').Trim("'")
                if ($k -eq "DB_PASSWORD") { $DbPassword = $v }
                if ($k -eq "DB_DATABASE" -and -not $PSBoundParameters.ContainsKey('DbName')) { $DbName = $v }
                if ($k -eq "DB_USERNAME" -and -not $PSBoundParameters.ContainsKey('DbUser')) { $DbUser = $v }
            }
        }
    }
}

# --- Fungsi Logging ---
function Write-Log {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $entry = "[$timestamp] $Message"
    Write-Host $entry -ForegroundColor $Color
    Add-Content -Path $LogFile -Value $entry
}

# --- Buat Direktori Jika Belum Ada ---
if (-not (Test-Path $BackupBaseDir)) {
    New-Item -ItemType Directory -Path $BackupBaseDir -Force | Out-Null
}
$logDir = Split-Path $LogFile
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}

Write-Log "============================================" "Cyan"
Write-Log "Mulai proses backup database SIMPEG..." "Cyan"

# --- Cek apakah Docker berjalan ---
$useDocker = $false
try {
    $dockerStatus = docker inspect --format="{{.State.Running}}" $ContainerName 2>&1
    if ($dockerStatus -eq "true") {
        $useDocker = $true
    } else {
        Write-Log "[INFO] Container '$ContainerName' tidak aktif, mencari mysqldump lokal..." "Yellow"
    }
} catch {
    Write-Log "[INFO] Docker tidak aktif / tidak terpasang, beralih ke mysqldump lokal..." "Yellow"
}

# --- Jalankan mysqldump ---
$timestamp  = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupFile = "$BackupBaseDir\simpeg_$timestamp.sql"
$GzipFile   = "$BackupFile.gz"

if ($useDocker) {
    Write-Log "Menjalankan mysqldump di dalam container '$ContainerName' (DB: $DbName)..." "White"
    $passArg = if ($DbPassword) { "-p$DbPassword" } else { "" }
    docker exec $ContainerName sh -c "mysqldump -u$DbUser $passArg --single-transaction --quick --lock-tables=false $DbName" | Out-File -FilePath $BackupFile -Encoding UTF8
} else {
    $mysqldump = Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter "mysqldump.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
    if (-not $mysqldump) {
        $mysqldump = (Get-Command mysqldump -ErrorAction SilentlyContinue).Source
    }

    if (-not $mysqldump) {
        Write-Log "ERROR: mysqldump tidak ditemukan di Docker maupun Laragon/PATH. Backup dibatalkan." "Red"
        exit 1
    }

    Write-Log "Menjalankan mysqldump lokal: $mysqldump (DB: $DbName)..." "White"
    $dumpArgs = @("-u", $DbUser, "--single-transaction", "--quick", "--skip-lock-tables", $DbName)
    if ($DbPassword) { $dumpArgs = @("-p$DbPassword") + $dumpArgs }
    & $mysqldump $dumpArgs | Out-File -FilePath $BackupFile -Encoding UTF8
}

if ($LASTEXITCODE -ne 0 -or -not (Test-Path $BackupFile) -or (Get-Item $BackupFile).Length -eq 0) {
    Write-Log "ERROR: mysqldump gagal dengan exit code $LASTEXITCODE atau file kosong." "Red"
    Remove-Item -Path $BackupFile -Force -ErrorAction SilentlyContinue
    exit 1
}

# --- Kompres file backup menggunakan GZip native ---
Write-Log "Mengompres file backup..." "White"
$rawBytes = [System.IO.File]::ReadAllBytes($BackupFile)
$fsOutput = [System.IO.File]::Create($GzipFile)
$gzipStream = New-Object System.IO.Compression.GZipStream($fsOutput, [System.IO.Compression.CompressionMode]::Compress)
$gzipStream.Write($rawBytes, 0, $rawBytes.Length)
$gzipStream.Close()
$fsOutput.Close()

Remove-Item -Path $BackupFile -Force
$sizeMB = [math]::Round((Get-Item $GzipFile).Length / 1MB, 2)
Write-Log "Backup berhasil: $GzipFile ($sizeMB MB)" "Green"

# --- Hapus backup lama (lebih dari $KeepDays hari) ---
Write-Log "Membersihkan backup lama (lebih dari $KeepDays hari)..." "Gray"
$oldFiles = Get-ChildItem -Path $BackupBaseDir -Filter "*.sql.gz" |
            Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$KeepDays) }

foreach ($file in $oldFiles) {
    Remove-Item -Path $file.FullName -Force -ErrorAction SilentlyContinue
    Write-Log "Dihapus (terlalu lama): $($file.Name)" "Gray"
}

$totalBackups = (Get-ChildItem -Path $BackupBaseDir -Filter "*.sql.gz").Count
Write-Log "Total file backup tersimpan: $totalBackups file" "Cyan"
Write-Log "Backup selesai." "Green"
Write-Log "============================================" "Cyan"