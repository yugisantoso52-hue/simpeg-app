FROM php:8.3-fpm-bookworm

# Install dependensi sistem, ekstensi PHP, dan Node.js untuk Vite
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi custom php.ini untuk batas upload & eksekusi aman
RUN echo 'upload_max_filesize = 20M\n\
post_max_size = 25M\n\
memory_limit = 256M\n\
max_execution_time = 300' > /usr/local/etc/php/conf.d/uploads.ini

# Environment variables Composer & Node
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PROCESS_TIMEOUT=600 \
    COMPOSER_MAX_PARALLEL_HTTP=4 \
    NODE_ENV=production

# Install Composer versi 2.7 stabil spesifik
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# 1. Optimasi Cache: Install dependencies PHP terlebih dahulu
COPY composer.json composer.lock ./

RUN composer config --global repo.packagist composer https://packagist.org \
    && (composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist \
        || (echo "Retrying composer install (attempt 2)..." && sleep 5 && composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist) \
        || (echo "Retrying composer install (attempt 3)..." && sleep 10 && composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist))

# 2. Optimasi Cache: Install dependencies NPM
COPY package.json package-lock.json ./
RUN npm ci || npm install

# 3. Copy seluruh source code aplikasi
COPY . /var/www

# 4. Generate optimized autoloader & build asset frontend
RUN composer dump-autoload --optimize --no-dev \
    && npm run build

# Set permission storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Konfigurasi Nginx
RUN echo 'server {\n\
    listen 80;\n\
    index index.php index.html;\n\
    root /var/www/public;\n\
    client_max_body_size 25M;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
}' > /etc/nginx/sites-available/default

# Konfigurasi Supervisor & Startup Command yang Aman (Tanpa Reset Database)
RUN echo '[supervisord]\n\
nodaemon=true\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
\n\
[program:startup]\n\
command=/bin/sh -c "php /var/www/artisan migrate --force && php /var/www/artisan config:clear && php /var/www/artisan storage:link --force"\n\
autostart=true\n\
autorestart=false\n\
startretries=1\n' > /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
