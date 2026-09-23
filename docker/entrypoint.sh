#!/bin/sh
set -e

echo "=== Memulai SIKAP FKP UNRI Container Entrypoint ==="

# 1. Pastikan direktori storage dan cache tersedia
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# 2. Atur ownership dan permission untuk www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Setup Cron Job untuk Laravel Task Scheduler
echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

# 4. Buat symlink storage jika belum ada
if [ ! -L /var/www/html/public/storage ]; then
    echo "Membuat symlink storage/public..."
    php artisan storage:link || true
fi

# 5. Tunggu Database jika DB_HOST terdefinisi
if [ -n "$DB_HOST" ]; then
    echo "Menunggu koneksi Database ($DB_HOST:${DB_PORT:-3306})..."
    max_tries=30
    count=0
    until nc -z -v -w3 "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null; do
        count=$((count + 1))
        if [ $count -ge $max_tries ]; then
            echo "Peringatan: Database belum dapat dihubungi setelah $max_tries percobaan. Melanjutkan..."
            break
        fi
        sleep 2
    done
    echo "Database terdeteksi siap!"
fi

# 6. Jalankan Migrasi Otomatis jika diaktifkan
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Menjalankan database migration (php artisan migrate --force)..."
    php artisan migrate --force || true
fi

# 7. Optimasi Cache untuk Lingkungan Production
if [ "$APP_ENV" = "production" ]; then
    echo "Mengoptimalkan cache Laravel (config, route, view)..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "=== SIKAP FKP UNRI Siap Melayani Traffic! ==="

# Eksekusi command utama (supervisord)
exec "$@"
