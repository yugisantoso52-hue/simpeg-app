# =============================================================================
# SCRIPT BACKUP SATU ARAH (ONE-WAY PULL): RAILWAY CLOUD & R2 -> PC LOKAL
# SIKAP FKP UNRI - Arsitektur Struktur 2
# =============================================================================

$ProjectRoot   = "C:\laragon\www\simpeg"
$BackupBaseDir = "C:\simpeg-backup"
$DbBackupDir   = "$BackupBaseDir\db"
$FileBackupDir = "$BackupBaseDir\files"
$LogDir        = "$BackupBaseDir\logs"
$LogFile       = "$LogDir\backup.log"
$KeepDays      = 30

# Buat direktori jika belum ada
$targetDirs = @($BackupBaseDir, $DbBackupDir, $FileBackupDir, $LogDir)
foreach ($dir in $targetDirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}

function Write-Log {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $entry = "[$timestamp] $Message"
    Write-Host $entry -ForegroundColor $Color
    Add-Content -Path $LogFile -Value $entry
}

Write-Log "============================================================" "Cyan"
Write-Log "  MEMULAI PROSES CADANGAN OTOMATIS (CLOUD -> PC LOKAL)" "Cyan"
Write-Log "============================================================" "Cyan"

# Baca konfigurasi dari .env
$envFile = "$ProjectRoot\.env"
$envVars = @{}
if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        $line = $_.Trim()
        if ($line -and -not $line.StartsWith("#") -and $line.Contains("=")) {
            $parts = $line.Split("=", 2)
            $key = $parts[0].Trim()
            $val = $parts[1].Trim().Trim('"').Trim("'")
            $envVars[$key] = $val
        }
    }
}

# --- LANGKAH 1: BACKUP DATABASE DARI RAILWAY MYSQL ---
Write-Log "[1/2] Memproses Backup Database dari Railway Cloud..." "Yellow"

$rHost = $envVars["RAILWAY_MYSQL_HOST"]
$rPort = $envVars["RAILWAY_MYSQL_PORT"]
$rDb   = $envVars["RAILWAY_MYSQL_DATABASE"]
$rUser = $envVars["RAILWAY_MYSQL_USER"]
$rPass = $envVars["RAILWAY_MYSQL_PASSWORD"]

if (-not $rPort) { $rPort = "3306" }
if (-not $rDb)   { $rDb = "railway" }
if (-not $rUser) { $rUser = "root" }

# Cari executable mysqldump dari Laragon
$mysqldump = Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter "mysqldump.exe" -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName

if (-not $mysqldump) {
    $mysqldump = (Get-Command mysqldump -ErrorAction SilentlyContinue).Source
}

if (-not $rHost) {
    Write-Log "  [INFO] Variabel RAILWAY_MYSQL_HOST belum diatur di .env." "Magenta"
    Write-Log "         Untuk mengaktifkan remote dump, isi RAILWAY_MYSQL_* di .env." "Magenta"
    Write-Log "         Lewati langkah database dump dari remote host." "Yellow"
} elseif (-not $mysqldump) {
    Write-Log "  [ERROR] mysqldump.exe tidak ditemukan di C:\laragon\bin\mysql!" "Red"
} else {
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $sqlFile = "$DbBackupDir\simpeg_cloud_$timestamp.sql"
    $gzFile  = "$sqlFile.gz"

    Write-Log "  Menghubungkan ke $($rHost):$($rPort) (DB: $rDb)..." "White"

    $dumpArgs = @(
        "-h", $rHost,
        "-P", $rPort,
        "-u", $rUser,
        "--single-transaction",
        "--quick",
        "--skip-lock-tables",
        "--default-character-set=utf8mb4",
        $rDb
    )

    if ($rPass) {
        $dumpArgs = @("-p$rPass") + $dumpArgs
    }

    try {
        & $mysqldump $dumpArgs | Out-File -FilePath $sqlFile -Encoding UTF8
        if ($LASTEXITCODE -eq 0 -and (Test-Path $sqlFile) -and (Get-Item $sqlFile).Length -gt 0) {
            # Kompresi file SQL menggunakan GZip native .NET
            $rawBytes = [System.IO.File]::ReadAllBytes($sqlFile)
            $fsOutput = [System.IO.File]::Create($gzFile)
            $gzipStream = New-Object System.IO.Compression.GZipStream($fsOutput, [System.IO.Compression.CompressionMode]::Compress)
            $gzipStream.Write($rawBytes, 0, $rawBytes.Length)
            $gzipStream.Close()
            $fsOutput.Close()

            Remove-Item -Path $sqlFile -Force -ErrorAction SilentlyContinue
            $sizeMB = [math]::Round((Get-Item $gzFile).Length / 1MB, 2)
            Write-Log "  [OK] Database Cloud berhasil dicadangkan: $gzFile ($sizeMB MB)" "Green"
        } else {
            Write-Log "  [WARNING] mysqldump selesai dengan kode keluar $LASTEXITCODE atau file kosong." "Yellow"
            if (Test-Path $sqlFile) { Remove-Item -Path $sqlFile -Force }
        }
    } catch {
        Write-Log "  [ERROR] Gagal menjalankan mysqldump: $_" "Red"
    }
}

# --- LANGKAH 2: BACKUP BERKAS DARI CLOUDFLARE R2 ---
Write-Log "[2/2] Memproses Sinkronisasi Berkas dari Cloudflare R2..." "Yellow"

# Deteksi path PHP secara dinamis (dari PATH atau direktori Laragon)
$phpExe = $null
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if ($phpCmd) {
    $phpExe = $phpCmd.Source
} else {
    $laragonPhp = Get-ChildItem "C:\laragon\bin\php" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
    if ($laragonPhp) {
        $phpExe = $laragonPhp
        $phpDir = Split-Path $phpExe
        $env:PATH = "$phpDir;" + $env:PATH
        $env:PHPRC = $phpDir
    }
}

if ($phpExe -and (Test-Path $phpExe)) {
    try {
        $artisanPath = "$ProjectRoot\artisan"
        & $phpExe $artisanPath simpeg:backup-r2 --dest="$FileBackupDir" --sync-storage
    } catch {
        Write-Log "  [ERROR] Gagal menjalankan command simpeg:backup-r2: $_" "Red"
    }
} else {
    Write-Log "  [ERROR] PHP binary tidak ditemukan di PATH maupun C:\laragon\bin\php" "Red"
}

# --- LANGKAH 3: ROTASI BERKAS CADANGAN LAMA ---
Write-Log "Membersihkan arsip SQL lama yang lebih dari $KeepDays hari..." "Gray"
$oldFiles = Get-ChildItem -Path $DbBackupDir -Filter "*.sql.gz" -ErrorAction SilentlyContinue |
            Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$KeepDays) }

foreach ($f in $oldFiles) {
    Remove-Item -Path $f.FullName -Force -ErrorAction SilentlyContinue
    Write-Log "  Dihapus (kedaluwarsa): $($f.Name)" "Gray"
}

Write-Log "============================================================" "Cyan"
Write-Log "  PROSES CADANGAN SELESAI" "Green"
Write-Log "============================================================" "Cyan"
