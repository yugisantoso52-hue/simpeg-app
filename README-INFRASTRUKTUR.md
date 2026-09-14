# Panduan Infrastruktur SIMPEG

## Arsitektur Sistem

```
[ Kode di PC ]  ──git push──▶  [ GitHub: yugisantoso52-hue/simpeg-app ]
      │
      │  Laragon (Apache + MySQL) berjalan di PC
      │  IP WiFi: 172.30.22.156
      ▼
[ http://172.30.22.156/simpeg ]
      │
      ├──▶  Data Teks  ──▶  MySQL: DB `simpeg` (32 tabel)
      └──▶  Foto/PDF   ──▶  C:\laragon\www\simpeg\storage\app\public\
```

---

## 1. Akses dari Perangkat Lain (Laptop/HP)

> **Syarat**: Pastikan Laragon sedang berjalan (Apache + MySQL ON) dan perangkat terhubung ke WiFi yang sama.

Buka browser dan ketik:
```
http://172.30.22.156/simpeg
```

### Login Default
| Username | Password | Role |
|---|---|---|
| (sesuai database) | (sesuai database) | - |

---

## 2. Push Kode ke GitHub

### Cara Cepat (GUI)
Double-click file: `sync-to-github.ps1`

### Cara Manual (Terminal)
```powershell
$git = "C:\laragon\bin\git\bin\git.exe"
&$git -C "C:\laragon\www\simpeg" add .
&$git -C "C:\laragon\www\simpeg" commit -m "pesan commit"
&$git -C "C:\laragon\www\simpeg" push origin main
```

### Repo GitHub
```
https://github.com/yugisantoso52-hue/simpeg-app
```

---

## 3. Jalankan Artisan Command

```powershell
$env:PHPRC = "C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64"
$php = "C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe"
$artisan = "C:\laragon\www\simpeg\artisan"

# Contoh commands:
&$php $artisan migrate          # jalankan migration
&$php $artisan db:show          # cek database
&$php $artisan cache:clear      # hapus cache
&$php $artisan storage:link     # buat storage symlink
&$php $artisan queue:work       # jalankan queue worker
```

---

## 4. Lokasi Penyimpanan File Upload

| Tipe File | Folder |
|---|---|
| Foto Pegawai | `storage\app\public\pegawai_foto\` |
| Ijazah | `storage\app\public\ijazah\` |
| Sertifikat Diklat | `storage\app\public\sertifikat-diklat\` |
| SK Jabatan | `storage\app\public\sk-jabatan\` |
| Dokumen Pegawai | `storage\app\public\pegawai\` |

**URL akses:** `http://172.30.22.156/simpeg/storage/{subfolder}/{nama-file}`

---

## 5. Database MySQL

| Setting | Nilai |
|---|---|
| Host | localhost / 127.0.0.1 |
| Port | 3306 |
| Database | simpeg |
| Username | root |
| Password | (kosong) |
| Tool GUI | http://localhost/phpmyadmin |

---

## 6. Konfigurasi yang Penting

### File `.env` (jangan di-push ke GitHub!)
- `APP_URL=http://172.30.22.156/simpeg` — URL untuk akses WiFi
- `DB_DATABASE=simpeg` — nama database MySQL
- `FILESYSTEM_DISK=public` — file upload ke storage/app/public

### Git Info
- Git executable: `C:\laragon\bin\git\bin\git.exe`
- Remote: `https://github.com/yugisantoso52-hue/simpeg-app.git`
- Branch utama: `main`

---

## 7. Troubleshooting

### Tidak bisa akses dari HP/Laptop lain
1. Pastikan Laragon running (Apache = ON, MySQL = ON)
2. Pastikan terhubung ke WiFi yang sama dengan PC
3. Cek IP WiFi PC: `ipconfig` → cari `Wi-Fi IPv4`
4. Jika masih gagal, jalankan sebagai Admin: `scripts\buka-firewall-port80.bat`

### Gambar/File tidak tampil
- Storage junction sudah dibuat: `public\storage` → `storage\app\public`
- Jika rusak: hapus `public\storage` dan jalankan `mklink /J "public\storage" "storage\app\public"`

### PHP artisan tidak jalan
- Set PHPRC dulu: `$env:PHPRC = "C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64"`
