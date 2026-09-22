#!/usr/bin/env bash
# ==============================================================================
# SIKAP FKP UNRI - Quick Deployment Script (Pull, Migrate, Optimize, Reload)
# ==============================================================================

set -e

echo "🚀 Memulai Pembaruan Sistem SIKAP FKP UNRI..."

cd /var/www/sikap

# 1. Mode Pemeliharaan Sementara (Zero Downtime / Graceful)
php artisan down || true

# 2. Tarik kode terbaru dari GitHub
git pull origin main

# 3. Update dependensi Composer
composer install --no-dev --optimize-autoloader

# 4. Jalankan Migrasi Database
php artisan migrate --force

# 5. Bersihkan & Optimalkan Cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Build ulang asset jika ada perubahan frontend
npm ci --prefer-offline || npm install
npm run build || true

# 7. Restart PHP-FPM
systemctl reload php8.3-fpm

# 8. Hidupkan kembali aplikasi
php artisan up

echo "✅ Pembaruan SIKAP FKP UNRI Selesai & Sukses!"
