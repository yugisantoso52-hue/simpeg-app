# Panduan Infrastruktur SIKAP FKP UNRI

## Arsitektur Sistem (Struktur 2: Cloud-as-Primary)

```
[ 💻 PC Kantor / Local Dev ]
  │
  ├─► Coding / Testing (Laragon Lokal: sikap.fkpunri.test)
  │      │
  │      ▼ (Push Git: sync-to-github.ps1)
  │   [ 🐙 GitHub Repo ]
  │      │
  │      ▼ (Auto Deploy CI/CD)
  │   [ ☁️ Cloud Railway ] ── (Production App: https://sikap-app.up.railway.app)
  │          │
  │          ├─► Database Utama ──► [ MySQL Cloud Railway (Single Source of Truth) ]
  │          │
  │          └─► File SK / PDF / Ijazah ──► [ ☁️ Cloudflare R2 (S3-Compatible) ]
  │                                                   ▲
  └─► [ 📥 Backup Script (One-Way Pull) ]             │
         ├─► Download SQL Dump dari Railway ──────────┤ (Hanya membaca / backup)
         └─► Download file arsip dari R2 ke Harddisk PC
```

---

## 1. Lingkungan Cloud (Production)

| Layanan | Provider | Keterangan |
|---|---|---|
| **Aplikasi Web** | Railway (`https://sikap-app.up.railway.app`) | Container Docker PHP 8.3 FPM + Nginx |
| **Database Utama** | MySQL Cloud di Railway | Single Source of Truth data kepegawaian |
| **Penyimpanan Berkas** | Cloudflare R2 | Penyimpanan foto, SK, ijazah, dan dokumen PDF |

---

## 2. Lingkungan PC Kantor (Local Dev & Offline Backup Vault)

PC Kantor memiliki 2 peran utama:
1. **Local Development & Testing**: Tempat koding dan pengujian sebelum dikirim ke GitHub.
2. **Offline Backup Vault**: Menyimpan salinan cadangan SQL dump Railway dan file Cloudflare R2 secara satu arah (*one-way pull*).

---

## 3. Push Kode ke GitHub & Auto Deploy

### Cara Cepat (GUI)
Klik kanan file **`sync-to-github.ps1`** ➔ Pilih **"Run with PowerShell"**.

### Cara Manual (Terminal)
```powershell
$git = "C:\laragon\bin\git\bin\git.exe"
&$git -C "C:\laragon\www\simpeg" add .
&$git -C "C:\laragon\www\simpeg" commit -m "update arsitektur struktur 2"
&$git -C "C:\laragon\www\simpeg" push origin main
```

Setiap push ke branch `main` akan memicu GitHub Actions CI untuk verifikasi build dan langsung di-deploy secara otomatis oleh Railway ke production.

---

## 4. Pencadangan Satu Arah (One-Way Local Backup)

### Cara Manual
Double-click file:
```text
C:\laragon\www\simpeg\scripts\backup-cloud-to-local.bat
```

### Lokasi Hasil Backup
* **Database Dump**: `C:\simpeg-backup\db\simpeg_cloud_YYYYMMDD_HHmmss.sql.gz`
* **Berkas R2 (Foto/SK/PDF)**: `C:\simpeg-backup\files\`
* **Log Pencadangan**: `C:\simpeg-backup\logs\backup.log`

### Jadwal Otomatis
Jalankan `scripts\setup-taskscheduler.ps1` sebagai Administrator untuk mengaktifkan backup otomatis setiap hari pukul **02:00 WIB**.

---

## 5. Konfigurasi Penting (.env)

### Pada Railway Dashboard:
```ini
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=...
R2_SECRET_ACCESS_KEY=...
R2_DEFAULT_REGION=auto
R2_BUCKET=sikap-files
R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
R2_URL=https://pub-xxxxxx.r2.dev
```

### Pada PC Kantor (.env Lokal):
```ini
FILESYSTEM_DISK=public
DB_CONNECTION=mysql
DB_DATABASE=simpeg

# Kredensial untuk script backup remote
RAILWAY_MYSQL_HOST=autorack.proxy.rlwy.net
RAILWAY_MYSQL_PORT=...
RAILWAY_MYSQL_DATABASE=railway
RAILWAY_MYSQL_USER=root
RAILWAY_MYSQL_PASSWORD=...

R2_ACCESS_KEY_ID=...
R2_SECRET_ACCESS_KEY=...
R2_BUCKET=sikap-files
R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
```
