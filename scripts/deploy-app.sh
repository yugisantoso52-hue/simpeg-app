#!/usr/bin/env bash
# ==============================================================================
# SIKAP FKP UNRI - Quick Deployment Script (Pull, Migrate, Optimize)
# ==============================================================================

set -e

echo "🚀 Memulai Pembaruan Sistem SIKAP FKP UNRI..."

# 1. Pindah ke direktori aplikasi
if [ -d "/var/www/html" ]; then
    cd /var/www/html
elif [ -d "/var/www/sikap" ]; then
    cd /var/www/sikap
fi

# 2. Tarik kode terbaru dari GitHub
git pull origin main

# 3. Jalankan Migrasi Database (jika ada tabel/kolom baru)
php artisan migrate --force

# 4. Bersihkan & Optimalkan Cache
php artisan optimize:clear

echo "✅ Pembaruan SIKAP FKP UNRI Selesai & Sukses!"
