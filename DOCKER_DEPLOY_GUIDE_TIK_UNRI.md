# PANDUAN DEPLOYMENT DOCKER CONTAINER
## APLIKASI SIKAP (SISTEM INFORMASI KEPEGAWAIAN & KINERJA PEGAWAI)
### FAKULTAS KEPERAWATAN UNIVERSITAS RIAU (FKP UNRI)
**Target Hostname / Subdomain:** `sikap.fkp.unri.ac.id`  
**Ditujukan kepada:** Tim Administrator Sistem & Jaringan UPT TIK Universitas Riau  

---

### 1. Arsitektur Container
Aplikasi telah dipaketkan secara mandiri (*self-contained*) dengan standar multi-stage Docker build:
- **Web & Application Server (`app`):**
  - **PHP 8.3 FPM** (Alpine Linux) dengan seluruh ekstensi yang dipersyaratkan: `pdo_mysql`, `mbstring`, `gd` (Freetype, WebP, JPEG), `zip`, `intl`, `bcmath`, `fileinfo`, `exif`, `xml`, dan `opcache`.
  - **Nginx Web Server** terintegrasi di dalam container untuk melayani traffic HTTP secara optimal.
  - **Supervisord** sebagai process manager untuk mengelola PHP-FPM, Nginx, Background Queue Worker, dan Cron Scheduler secara otomatis.
  - **Node.js 20** digunakan pada *build stage* untuk mengompilasi aset Vite & Tailwind CSS ke direktori `public/build/`.
- **Database Server (`db`):**
  - **MySQL 8.0** dengan konfigurasi charset `utf8mb4_unicode_ci` dan volume persisten.

---

### 2. Persyaratan di Sisi Server VPS UPT TIK UNRI
- Sistem Operasi: **Ubuntu Server 22.04 LTS atau 24.04 LTS (64-bit)**
- Perangkat Lunak:
  - **Docker Engine** (versi 24.x atau lebih baru)
  - **Docker Compose v2** (`docker compose` plugin)
  - **Git**

---

### 3. Langkah-Langkah Deployment (Eksekusi Pertama Kali)

#### Langkah 1: Clone Repositori ke Direktori Server
Masuk ke server VPS melalui SSH, lalu clone repositori ke `/var/www/sikap` atau direktori yang dikehendaki:
```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/yugisantoso52-hue/simpeg-app.git sikap
cd /var/www/sikap
```

#### Langkah 2: Siapkan Berkas Environment (`.env`)
Salin berkas template produksi yang telah disediakan:
```bash
cp .env.example.production .env
```

Sesuaikan password database dan konfigurasi pada `.env` bila diperlukan:
```ini
APP_NAME="SIKAP FKP UNRI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sikap.fkp.unri.ac.id

# Port container yang dipetakan ke Host VPS
APP_PORT=80
DB_DATABASE=simpeg
DB_USERNAME=sikap_user
DB_PASSWORD=GantiDenganPasswordAman2026!
DB_ROOT_PASSWORD=GantiDenganRootPasswordAman2026!
```

> **Catatan Port:**  
> Jika di VPS host sudah berjalan Nginx Reverse Proxy bawaan server UPT TIK UNRI untuk terminasi SSL Let's Encrypt / Wildcard, ubah `APP_PORT=8080` pada `.env`.

#### Langkah 3: Build & Jalankan Container
Jalankan perintah berikut untuk mengompilasi image dan menyalakan container:
```bash
sudo docker compose up -d --build
```

Container `app` akan secara otomatis:
1. Menunggu database MySQL siap menerima koneksi.
2. Menyetel hak akses direktori storage dan cache ke `www-data`.
3. Menghubungkan symlink `storage:link`.
4. Menjalankan migrasi database otomatis (`php artisan migrate --force`).
5. Mengaktifkan cache route, config, dan view untuk performa maksimal.

#### Langkah 4: Generate Application Key (APP_KEY)
Generate application key sekali saja pada inisialisasi awal:
```bash
sudo docker compose exec app php artisan key:generate --force
sudo docker compose exec app php artisan config:cache
```

#### Langkah 5: Inisialisasi Data Awal (Seeding / Akun Admin Pertama)
Jalankan seeder untuk membuat akun Administrator, data master unit kerja, dan jabatan awal:
```bash
sudo docker compose exec app php artisan db:seed --force
```

---

### 4. Konfigurasi Nginx Reverse Proxy & SSL (Host VPS)
Apabila terminasi SSL HTTPS dikelola di level Host VPS UPT TIK UNRI (misalnya menggunakan Let's Encrypt Certbot atau Sertifikat Wildcard UNRI), contoh konfigurasi Nginx Reverse Proxy di Host:

```nginx
# /etc/nginx/sites-available/sikap.fkp.unri.ac.id
server {
    listen 80;
    server_name sikap.fkp.unri.ac.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sikap.fkp.unri.ac.id;

    # Sertifikat SSL UNRI
    ssl_certificate /etc/letsencrypt/live/sikap.fkp.unri.ac.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sikap.fkp.unri.ac.id/privkey.pem;

    client_max_body_size 50M;

    location / {
        proxy_pass http://127.0.0.1:80; # Sesuaikan jika APP_PORT diubah ke 8080
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 300;
        proxy_connect_timeout 300;
    }
}
```

---

### 5. Operasi & Pemeliharaan Rutin

- **Mengecek Status Container:**
  ```bash
  sudo docker compose ps
  ```

- **Melihat Log Aplikasi & Web Server:**
  ```bash
  sudo docker compose logs -f app
  ```

- **Melakukan Update Kode Aplikasi Terbaru dari Git:**
  ```bash
  cd /var/www/sikap
  sudo git pull origin main
  sudo docker compose up -d --build
  ```

- **Backup Database MySQL:**
  ```bash
  sudo docker compose exec db mysqldump -u root -p[ROOT_PASSWORD] simpeg > backup_simpeg_$(date +%Y%m%d_%H%M%S).sql
  ```

- **Restore Database MySQL:**
  ```bash
  sudo docker compose exec -T db mysql -u root -p[ROOT_PASSWORD] simpeg < backup_simpeg.sql
  ```

- **Membersihkan / Refresh Cache Laravel:**
  ```bash
  sudo docker compose exec app php artisan optimize:clear
  sudo docker compose exec app php artisan optimize
  ```

---

### 6. Kontak Pengelola Teknis Fakultas Keperawatan UNRI
- **Nama Pengelola:** Rahmad Hidayat, ST
- **NIP:** 198006152025211060 / Penata Layanan Operasional
- **WhatsApp:** 085265888111
- **Email Resmi:** `rahmad.hidayat@staff.unri.ac.id`
