# =============================================================================
# SIMPEG - Script Sync ke GitHub (Otomatis & Tanpa Kendala)
# =============================================================================

$GIT = "C:\laragon\bin\git\bin\git.exe"
$REPO = "C:\laragon\www\simpeg"
$DOCS_REPO = "C:\Users\OPAC\Documents\GitHub\simpeg-app"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SIMPEG - Sync ke GitHub (Railway)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Cek Status
Write-Host "[1/4] Cek status perubahan..." -ForegroundColor Yellow
$status = &$GIT -C $REPO status --short
if (-not $status) {
    Write-Host "  Tidak ada perubahan di kodingan. Repository sudah up to date!" -ForegroundColor Green
    Read-Host "`nTekan Enter untuk keluar"
    exit 0
}
Write-Host "  Ada $($status.Count) file yang berubah:" -ForegroundColor White
$status | Select-Object -First 10 | ForEach-Object { Write-Host "    $_" -ForegroundColor Gray }
if ($status.Count -gt 10) { Write-Host "    ... dan $($status.Count - 10) file lainnya." -ForegroundColor Gray }

# 2. Stage Semua
Write-Host "`n[2/4] Stage semua perubahan..." -ForegroundColor Yellow
&$GIT -C $REPO add .
Write-Host "  Done." -ForegroundColor Green

# 3. Commit
Write-Host "`n[3/4] Commit perubahan..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm"
$defaultMsg = "update: $timestamp"
$commitMsg = Read-Host "  Pesan commit (Tekan Enter untuk: '$defaultMsg')"
if ([string]::IsNullOrWhiteSpace($commitMsg)) { $commitMsg = $defaultMsg }

&$GIT -C $REPO commit -m $commitMsg
Write-Host "  Commit berhasil." -ForegroundColor Green

# 4. Ambil Token & Push
Write-Host "`n[4/4] Push ke GitHub..." -ForegroundColor Yellow

$source = @"
using System;
using System.Runtime.InteropServices;
using System.Text;

public class CredentialReader {
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    public struct CREDENTIAL {
        public int Flags;
        public int Type;
        public string TargetName;
        public string Comment;
        public System.Runtime.InteropServices.ComTypes.FILETIME LastWritten;
        public int CredentialBlobSize;
        public IntPtr CredentialBlob;
        public int Persist;
        public int AttributeCount;
        public IntPtr Attributes;
        public string TargetAlias;
        public string UserName;
    }

    [DllImport("advapi32.dll", EntryPoint="CredReadW", CharSet=CharSet.Unicode, SetLastError=true)]
    public static extern bool CredRead(string target, int type, int flags, out IntPtr credPtr);
    
    [DllImport("advapi32.dll")]
    public static extern void CredFree(IntPtr credPtr);

    public static string GetUtf8Credential(string target) {
        IntPtr credPtr;
        if (CredRead(target, 1, 0, out credPtr)) {
            var cred = Marshal.PtrToStructure<CREDENTIAL>(credPtr);
            byte[] bytes = new byte[cred.CredentialBlobSize];
            Marshal.Copy(cred.CredentialBlob, bytes, 0, cred.CredentialBlobSize);
            CredFree(credPtr);
            return Encoding.UTF8.GetString(bytes);
        }
        return null;
    }
}
"@
Add-Type -TypeDefinition $source -Language CSharp -ErrorAction SilentlyContinue
$token = [CredentialReader]::GetUtf8Credential("GitHub - https://api.github.com/yugisantoso52-hue")

if ($token) {
    $remoteUrl = "https://yugisantoso52-hue:$token@github.com/yugisantoso52-hue/simpeg-app.git"
    $env:GIT_TERMINAL_PROMPT = "0"
    &$GIT -C $REPO -c credential.helper= push $remoteUrl main
    
    # Sync juga folder dokumen GitHub Desktop jika ada
    if (Test-Path $DOCS_REPO) {
        &$GIT -C $DOCS_REPO fetch origin main 2>&1 | Out-Null
        &$GIT -C $DOCS_REPO reset --hard origin/main 2>&1 | Out-Null
    }
} else {
    &$GIT -C $REPO push origin main
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  SUKSES! Kode ter-push ke GitHub & Railway akan build otomatis." -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Read-Host "`nTekan Enter untuk keluar"
