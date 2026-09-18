#!/usr/bin/env bash
# ==============================================================================
# SIKAP FKP UNRI - Automated Production Server Setup Script for Ubuntu 22.04/24.04
# Dibuat untuk: Server VPS UPT TIK Universitas Riau (sikap.fkp.unri.ac.id)
# ==============================================================================

set -e

# Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}===================================================================${NC}"
echo -e "${GREEN}    🚀 SETUP OTOMATIS SERVER SIKAP FAKULTAS KEPERAWATAN UNRI      ${NC}"
echo -e "${BLUE}===================================================================${NC}"

if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}[ERROR] Skrip ini harus dijalankan sebagai ROOT (Gunakan: sudo bash setup-vps-unri.sh)${NC}"
  exit 1
fi

DOMAIN="sikap.fkp.unri.ac.id"
WEB_ROOT="/var/www/sikap"
DB_NAME="simpeg"
DB_USER="simpeg_user"
DB_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)

echo -e "${YELLOW}[1/8] Memperbarui Repositori Paket Ubuntu...${NC}"
apt-get update -y && apt-get upgrade -y
apt-get install -y software-properties-common curl git unzip zip certbot python3-certbot-nginx ufw htop

echo -e "${YELLOW}[2/8] Menginstal Web Server Nginx...${NC}"
apt-get install -y nginx
systemctl enable nginx
systemctl start nginx

echo -e "${YELLOW}[3/8] Menginstal PHP 8.3 & Ekstensi Lengkap...${NC}"
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-gd php8.3-zip \
    php8.3-xml php8.3-curl php8.3-intl php8.3-bcmath php8.3-fileinfo php8.3-opcache

# Konfigurasi php.ini untuk unggahan dokumen besar (SK, Ijazah, Logbook)
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.3/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 55M/' /etc/php/8.3/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.3/fpm/php.ini
systemctl restart php8.3-fpm

echo -e "${YELLOW}[4/8] Menginstal Composer 2 & Node.js 20 LTS...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

echo -e "${YELLOW}[5/8] Mengonfigurasi Database MySQL 8.0...${NC}"
if ! command -v mysql &> /dev/null; then
    apt-get install -y mysql-server
    systemctl enable mysql
    systemctl start mysql
fi

# Buat Database & User jika belum ada
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${YELLOW}[6/8] Mengunduh Source Code SIKAP dari GitHub...${NC}"
if [ -d "$WEB_ROOT" ]; then
    echo "Direktori $WEB_ROOT sudah ada, melakukan git pull..."
    cd $WEB_ROOT
    git pull origin main
else
    git clone https://github.com/yugisantoso52-hue/simpeg-app.git $WEB_ROOT
    cd $WEB_ROOT
fi

# Salin .env production
if [ ! -f "$WEB_ROOT/.env" ]; then
    cp $WEB_ROOT/.env.example $WEB_ROOT/.env
    sed -i "s|APP_NAME=.*|APP_NAME=\"SIKAP FKP UNRI\"|" $WEB_ROOT/.env
    sed -i "s|APP_ENV=.*|APP_ENV=production|" $WEB_ROOT/.env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" $WEB_ROOT/.env
    sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" $WEB_ROOT/.env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" $WEB_ROOT/.env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" $WEB_ROOT/.env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" $WEB_ROOT/.env
fi

# Install dependensi Laravel & Build Frontend
echo -e "${YELLOW}Menginstal dependensi Composer & NPM...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate --force
php artisan storage:link || true

echo -e "${YELLOW}[7/8] Mengonfigurasi Nginx Server Block...${NC}"
cat << 'EOF' > /etc/nginx/sites-available/sikap
server {
    listen 80;
    server_name sikap.fkp.unri.ac.id;
    root /var/www/sikap/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html;
    charset utf-8;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/sikap /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo -e "${YELLOW}[8/8] Mengatur Hak Akses Direktori (Permissions)...${NC}"
chown -R www-data:www-data $WEB_ROOT
chmod -R 775 $WEB_ROOT/storage $WEB_ROOT/bootstrap/cache

# Cron Job Laravel Scheduler
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/sikap && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo -e "${BLUE}===================================================================${NC}"
echo -e "${GREEN}    🎉 INSTALASI & SETUP SERVER SIKAP FKP UNRI SELESAI!           ${NC}"
echo -e "${BLUE}===================================================================${NC}"
echo -e "Informasi Konfigurasi:"
echo -e "• Domain       : ${GREEN}http://${DOMAIN}${NC} (Jalankan 'certbot --nginx -d ${DOMAIN}' untuk SSL)"
echo -e "• Root Folder  : ${WEB_ROOT}"
echo -e "• Database     : ${DB_NAME}"
echo -e "• DB User      : ${DB_USER}"
echo -e "• DB Password  : ${GREEN}${DB_PASS}${NC}"
echo -e "-------------------------------------------------------------------"
echo -e "${YELLOW}Langkah Selanjutnya (Restore Data Backup):${NC}"
echo -e "Unggah file simpeg_cloud.sql.gz ke server ini, lalu jalankan:"
echo -e "${GREEN}gunzip < simpeg_cloud.sql.gz | mysql -u ${DB_USER} -p'${DB_PASS}' ${DB_NAME}${NC}"
echo -e "${GREEN}cd /var/www/sikap && php artisan migrate --force${NC}"
echo -e "${BLUE}===================================================================${NC}"
