#!/bin/bash
# =============================================================================
# SKRIP INISIALISASI & DEPLOYMENT SIKAP FKP UNRI (KUBERNETES POD)
# Jalankan skrip ini langsung di Terminal Pod Headlamp:
# bash scripts/deploy-pod.sh
# =============================================================================

echo ">>> [1/6] Memeriksa direktori dan environment..."
if [ -f .env.production.unri ] && [ ! -f .env ]; then
    cp .env.production.unri .env
    echo "Berkas .env berhasil disiapkan dari template UNRI."
fi

echo ">>> [2/6] Memasang dependensi Composer..."
if [ -f composer.json ]; then
    composer install --optimize-autoloader --no-dev --no-interaction
fi

echo ">>> [3/6] Menyiapkan Application Key..."
php artisan key:generate --force

echo ">>> [4/6] Menghubungkan direktori public storage..."
php artisan storage:link

echo ">>> [5/6] Menjalankan migrasi database..."
php artisan migrate --force
php artisan db:seed --force

echo ">>> [6/6] Mengoptimalkan Cache Laravel untuk Produksi..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Hak akses folder storage & bootstrap
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "======================================================"
echo "🎉 DEPLOYMENT SELESAI! SIKAP FKP UNRI SIAP DIAKSES."
echo "Domain: https://sikap.fkp.unri.ac.id"
echo "======================================================"
