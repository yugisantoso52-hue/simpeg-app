# 🚀 Panduan Deployment & Pengelolaan SIKAP FKP UNRI
## Berdasarkan Alokasi Infrastruktur Kubernetes UPT TIK Universitas Riau (2026)

Dokumen ini disusun sebagai panduan operasional langkah demi langkah bagi tim teknis Fakultas Keperawatan UNRI setelah menerima jawaban dan akun akses resmi dari UPT TIK UNRI.

---

## 📌 Ringkasan Perbedaan: Rencana Awal vs Realisasi TIK UNRI

| Parameter | Permohonan Awal Kita | Disediakan oleh UPT TIK UNRI | Catatan Operasional |
| :--- | :--- | :--- | :--- |
| **Arsitektur** | VPS Standalone (Ubuntu VM) | **Kubernetes Container (Cluster UNRI)** | Lebih aman, otomatis terkelola, terisolasi dalam *namespace*. |
| **Akses Server** | SSH Port 22 (Direct Terminal) | **Web Console Headlamp (Pod Exec)** | Diakses via browser di `console.unri.ac.id`. |
| **Pengelolaan File** | SSH / SCP / Git Clone | **JumpServer / SFTP Web Bastion** | Upload/download file via browser di `bastion.unri.ac.id`. |
| **Database** | MySQL 8 di dalam VPS | **MariaDB Cluster (`mariadb-sikap-primary`)** | Tersedia antarmuka phpMyAdmin web. |
| **Domain & SSL** | `sikap.fkp.unri.ac.id` | **`sikap.fkp.unri.ac.id` (SSL Aktif)** | Subdomain dan sertifikat HTTPS sudah terkonfigurasi di Ingress UNRI. |

---

## 🔑 Informasi Akun & Akses Resmi

Simpan dan rahasiakan data kredensial berikut:

| Layanan | URL Akses | Kredensial | Keterangan |
| :--- | :--- | :--- | :--- |
| **Aplikasi Utama** | `https://sikap.fkp.unri.ac.id/` | - | Domain resmi SIKAP FKP |
| **Database (phpMyAdmin)** | `https://sikap.fkp.unri.ac.id/phpmyadmin` | **User:** `sikap_app`<br>**Pass:** `c2tvLujmZRkwSeNs5gw4SfAr58/vrNhI`<br>**Database:** `sikap_db`<br>**Host DB:** `mariadb-sikap-primary` | Kelola tabel, import SQL |
| **File Manager (SFTP)** | `https://bastion.unri.ac.id/` | **Username:** `sikap`<br>**Password:** `Rwg3xhv70SkWkmkVl01D+5BR` | Upload berkas aplikasi ke folder `web` |
| **Container Console** | `https://console.unri.ac.id/` | **Username:** `sikap`<br>**Password:** `lLfxG3wIxkWDlyBa`<br>**Namespace:** `ns-sikap` | Terminal pod (Artisan, Composer, Logs) |

---

## 📋 Langkah Kerja Selanjutnya (Step-by-Step)

### 1. Persiapan di Komputer Lokal (Selesai Disiapkan)
- Berkas [`.env.production.unri`](file:///c:/laragon/www/simpeg/.env.production.unri) telah dibuat dengan parameter database internal (`mariadb-sikap-primary`, `sikap_db`, dll).
- File asset frontend telah dikompilasi dengan `npm run build` (tersimpan di `public/build/`).
- Middleware `TrustProxies` telah diaktifkan untuk menangani HTTPS Ingress Kubernetes.

---

### 2. Impor Database ke MariaDB Server UNRI
1. Buka browser: **`https://sikap.fkp.unri.ac.id/phpmyadmin`**
2. Masukkan akun:
   - **Username:** `sikap_app`
   - **Password:** `c2tvLujmZRkwSeNs5gw4SfAr58/vrNhI`
3. Pilih database **`sikap_db`** di panel kiri.
4. Klik tab **Import** (atau jalankan migrasi via Artisan di Langkah 4).
   > *Rekomendasi:* Anda dapat mengekspor data lokal Anda (`backup/` atau mysqldump) lalu import file `.sql` tersebut ke `sikap_db` agar data master/pegawai yang sudah ada langsung tersalin.

---

### 3. Unggah Source Code Aplikasi via Bastion JumpServer
1. Buka browser: **`https://bastion.unri.ac.id/`**
2. Login dengan akun:
   - **Username:** `sikap`
   - **Password:** `Rwg3xhv70SkWkmkVl01D+5BR`
3. Klik menu **Access Assets** di panel kiri.
4. Klik ikon **Monitor** pada baris server Anda, lalu pilih mode **Web SFTP** -> klik **Connect**.
5. Masuk ke direktori **`web`** (atau direktori kerja aplikasi).
6. Unggah source code proyek SIKAP.
   > **Catatan Pengunggahan:**
   > - Jangan mengunggah folder `node_modules` (ukurannya besar dan tidak diperlukan di server produksi).
   > - Sertakan berkas [`.env.production.unri`](file:///c:/laragon/www/simpeg/.env.production.unri) dan ubah namanya menjadi `.env` di server.
   > - Pastikan berkas `.htaccess` di root dan di `public/.htaccess` ikut terunggah.

---

### 4. Konfigurasi Awal di Headlamp Console (Terminal Pod)
1. Buka browser: **`https://console.unri.ac.id/`**
2. Login dengan akun:
   - **Username:** `sikap`
   - **Password:** `lLfxG3wIxkWDlyBa`
3. **Konfigurasi Namespace (Wajib dilakukan sekali):**
   - Klik menu **Settings** (ikon roda gigi) -> **Cluster (main)**.
   - Pada bagian **Allowed namespaces**, ketik `ns-sikap` lalu tekan **Enter**.
   - Logout lalu Login kembali agar izin namespace diterapkan.
4. Masuk ke menu **Workloads** -> **Pods**.
5. Pastikan dropdown namespace di bagian atas mengarah ke **`ns-sikap`**.
6. Klik Pod aplikasi yang statusnya **Running**.
7. Klik tab/ikon **Terminal / Exec** untuk membuka terminal shell pod.
8. Jalankan perintah pemeliharaan Laravel berikut di dalam terminal pod:

```bash
# 1. Pindah ke direktori aplikasi (jika belum berada di direktori tersebut)
cd /var/www/html

# 2. Pasang dependensi PHP (jika vendor tidak di-upload via SFTP)
composer install --optimize-autoloader --no-dev

# 3. Generate APP_KEY (jika belum terisi di .env)
php artisan key:generate --force

# 4. Hubungkan penyimpanan storage publik (simbolik link)
php artisan storage:link

# 5. Jalankan migrasi dan seeder database
php artisan migrate --force
php artisan db:seed --force

# 6. Bersihkan dan optimalkan cache produksi
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Pastikan hak akses direktori storage dan cache dapat ditulis
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### 5. Verifikasi & Pengujian Akhir
1. Buka **`https://sikap.fkp.unri.ac.id/`** di peramban.
2. Pastikan halaman login muncul dengan antarmuka rapi (CSS dan JavaScript termuat tanpa error 404).
3. Lakukan login menggunakan akun Administrator.
4. Uji alur input data, unduh laporan, serta upload berkas/foto untuk memastikan konfigurasi storage berjalan lancar.
