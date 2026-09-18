# 📘 Panduan Teknis Deployment SIKAP ke VPS UPT TIK UNRI

Dokumen panduan ini digunakan saat Anda telah menerima akses server VPS (IP Publik, Username, dan Password SSH) dari UPT TIK Universitas Riau.

---

## 1. Menghubungkan Diri ke Server VPS UNRI

Buka **PowerShell** atau **Terminal Command Prompt** di PC kantor, lalu ketik perintah:

```bash
ssh root@<IP_SERVER_DARI_UPT_TIK>
```
*(Contoh: `ssh root@103.15.22.88` lalu masukkan password yang diberikan).*

---

## 2. Menjalankan Instalasi Otomatis (1-Klik)

Setelah berhasil login ke dalam terminal server VPS, jalankan perintah instalasi otomatis berikut:

```bash
curl -fsSL https://raw.githubusercontent.com/yugisantoso52-hue/simpeg-app/main/scripts/setup-vps-unri.sh | bash
```

### Apa yang Otomatis Dilakukan Skrip Ini?
1. Menginstal Nginx Web Server, Git, Unzip, dan Curl.
2. Menginstal **PHP 8.3-FPM** beserta seluruh ekstensi wajib (`pdo_mysql`, `gd`, `zip`, `xml`, `mbstring`, `intl`, `fileinfo`).
3. Menginstal **MySQL 8.0**, membuat database `simpeg`, dan user database aman.
4. Menginstal Composer 2 dan Node.js 20 LTS.
5. Men-clone source code lengkap SIKAP dari GitHub ke folder `/var/www/sikap`.
6. Mengonfigurasi Nginx Server Block untuk domain **`sikap.fkp.unri.ac.id`**.
7. Menghubungkan cron job otomatis untuk tugas terjadwal (*scheduler*).

---

## 3. Merestore Data Backup Database

Setelah instalasi selesai, kirimkan file backup database dari PC kantor ke server VPS:

### A. Dari PC Kantor (PowerShell):
```powershell
scp C:\simpeg-backup\db\simpeg_cloud_20260916_134034.sql.gz root@<IP_SERVER_VPS>:/root/
```

### B. Di Terminal Server VPS:
Jalankan perintah restore:
```bash
gunzip < /root/simpeg_cloud_20260916_134034.sql.gz | mysql -u simpeg_user -p simpeg
```
*(Masukkan password database yang ditampilkan di akhir proses instalasi skrip langkah 2).*

Lalu jalankan pembaruan migrasi (termasuk modul Logbook baru):
```bash
cd /var/www/sikap
php artisan migrate --force
```

---

## 4. Mengaktifkan Sertifikat SSL (HTTPS Gratis & Resmi)

Di terminal VPS, jalankan perintah Certbot:
```bash
certbot --nginx -d sikap.fkp.unri.ac.id
```
Pilih opsi **Redirect HTTP to HTTPS** (otomatis).

---

## 5. Pembaruan Kode di Masa Depan (Update 10 Detik)

Kapan pun Anda mengubah kodingan di PC lokal dan meng-push ke GitHub, Anda cukup menjalankan perintah pembaruan di server VPS:

```bash
bash /var/www/sikap/scripts/deploy-app.sh
```

Aplikasi otomatis ter-update dalam 5–10 detik tanpa mematikan server.
