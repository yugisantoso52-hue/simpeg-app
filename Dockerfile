# =============================================================================
# STAGE 1: FRONTEND BUILD (Vite + Tailwind CSS + Alpine.js)
# =============================================================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --ignore-scripts
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public ./public
RUN npm run build

# =============================================================================
# STAGE 2: PHP DEPENDENCIES (Composer Vendor Build)
# =============================================================================
FROM composer:2.7 AS composer-builder
WORKDIR /app
COPY composer*.json ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# =============================================================================
# STAGE 3: PRODUCTION RUNTIME (PHP 8.3 FPM + Nginx + Supervisord)
# =============================================================================
FROM php:8.3-fpm-alpine

LABEL maintainer="Tim Pengembang SIKAP FKP UNRI <rahmad.hidayat@staff.unri.ac.id>"
LABEL description="Sistem Informasi Kepegawaian & Kinerja Pegawai (SIKAP) Fakultas Keperawatan Universitas Riau"

# Set direktori kerja aplikasi
WORKDIR /var/www/html

# Install paket sistem dependensi runtime & Nginx & Supervisor
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    netcat-openbsd \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    tzdata

# Konfigurasi dan compile ekstensi PHP yang diwajibkan oleh UPT TIK UNRI
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        intl \
        bcmath \
        fileinfo \
        exif \
        xml \
        opcache

# Salin konfigurasi PHP & Nginx & Supervisor
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Beri hak eksekusi pada script entrypoint
RUN chmod +x /usr/local/bin/entrypoint.sh

# Salin source code aplikasi
COPY . .

# Salin vendor dari Stage 2
COPY --from=composer-builder /app/vendor /var/www/html/vendor

# Salin compiled frontend assets dari Stage 1
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Dump autoload composer final
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && rm /usr/bin/composer

# Setup permission direktori storage dan cache
RUN mkdir -p /var/www/html/storage/app/public \
             /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Port 80 untuk Nginx
EXPOSE 80

# Gunakan script entrypoint khusus
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Perintah default: Jalankan Supervisord (mengelola PHP-FPM, Nginx, dan Queue Worker)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]