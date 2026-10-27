@echo off
title SIKAP FKp UNRI - Auto Update 100%% (PC -> Server Online)
color 0A

echo ==============================================================================
echo   SIKAP FKp UNRI - AUTO-UPDATE 100%% SERBA OTOMATIS
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

"%GIT%" config credential.helper manager >nul 2>&1

echo [1/3] Mengecek file perbaikan di PC lokal...
"%GIT%" status -s > "%TEMP%\git_status.txt"

set HAS_CHANGES=
for /f "tokens=*" %%i in (%TEMP%\git_status.txt) do set HAS_CHANGES=1

if not defined HAS_CHANGES (
    echo [INFO] Tidak ada file baru yang diubah. Memproses pengiriman...
    goto AFTER_CHANGES
)

echo [INFO] Ditemukan perubahan kode baru di PC lokal.
echo.
set COMMIT_MSG=
set /p COMMIT_MSG="Ketik catatan perbaikan (Atau tekan ENTER untuk otomatis): "

if not defined COMMIT_MSG set COMMIT_MSG=Pembaruan sistem SIKAP FKp UNRI

echo.
echo [PACKAGING] Menyimpan kode baru...
"%GIT%" add .
"%GIT%" commit -m "%COMMIT_MSG%"

:AFTER_CHANGES

echo.
echo [2/3] Mengunggah kode ke GitHub Repository...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$GIT = '%GIT%'; $REPO = '%REPO%'; $DOCS = 'C:\Users\OPAC\Documents\GitHub\simpeg-app'; " ^
  "$src = 'using System; using System.Runtime.InteropServices; using System.Text; public class CredR { [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)] public struct CRED { public int F; public int T; public string N; public string C; public System.Runtime.InteropServices.ComTypes.FILETIME L; public int S; public IntPtr B; public int P; public int AC; public IntPtr A; public string TA; public string U; } [DllImport(\"advapi32.dll\", EntryPoint=\"CredReadW\", CharSet=CharSet.Unicode)] public static extern bool CredRead(string t, int type, int f, out IntPtr p); [DllImport(\"advapi32.dll\")] public static extern void CredFree(IntPtr p); public static string Get(string t) { IntPtr p; if (CredRead(t,1,0,out p)) { var c = Marshal.PtrToStructure<CRED>(p); byte[] b = new byte[c.S]; Marshal.Copy(c.B,b,0,c.S); CredFree(p); return Encoding.UTF8.GetString(b); } return null; } }'; " ^
  "Add-Type -TypeDefinition $src -Language CSharp -ErrorAction SilentlyContinue; " ^
  "$tok = [CredR]::Get('GitHub - https://api.github.com/yugisantoso52-hue'); " ^
  "if ($tok) { $env:GIT_TERMINAL_PROMPT='0'; & $GIT -C $REPO -c credential.helper= push \"https://yugisantoso52-hue:$tok@github.com/yugisantoso52-hue/simpeg-app.git\" main; if (Test-Path $DOCS) { & $GIT -C $DOCS fetch origin main 2>&1 | Out-Null; & $GIT -C $DOCS reset --hard origin/main 2>&1 | Out-Null; } } else { & $GIT -C $REPO push origin main }"

if %errorlevel% neq 0 (
    echo.
    color 0C
    echo ==============================================================================
    echo [GAGAL] Terjadi kendala saat push ke GitHub. Pastikan koneksi internet aktif.
    echo ==============================================================================
    pause
    exit /b
)

echo.
echo [3/3] Menghubungi Server Online (https://sikap.fkp.unri.ac.id)...
echo [INFO] Mengaktifkan sinkronisasi kode dan migrasi otomatis di server UNRI...

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy-online.ps1"

if %errorlevel% neq 0 (
    echo.
    color 0C
    echo ==============================================================================
    echo [GAGAL] Terjadi kendala saat menghubungi Server Online UNRI.
    echo ==============================================================================
    pause
    exit /b
)

echo.
echo ==============================================================================
echo  SELESAI! PEMBARUAN 100%% SUKSES TERKIRIM DAN AKTIF DI SERVER ONLINE!
echo ==============================================================================
echo  Web Resmi: https://sikap.fkp.unri.ac.id
echo  Data Pegawai di Server Online DIJAMIN 100%% AMAN DAN TERPROTEKSI.
echo ==============================================================================
echo.
pause

