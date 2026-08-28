FROM public.ecr.aws/docker/library/php:8.3-fpm

# Install dependensi sistem, ekstensi PHP, Node.js, dan Composer
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
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.7.7 \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi custom php.ini (Upload & OPcache High Performance)
RUN echo 'upload_max_filesize = 20M\n\
post_max_size = 25M\n\
memory_limit = 256M\n\
max_execution_time = 300\n\
opcache.enable = 1\n\
opcache.enable_cli = 0\n\
opcache.memory_consumption = 128\n\
opcache.interned_strings_buffer = 16\n\
opcache.max_accelerated_files = 10000\n\
opcache.revalidate_freq = 2\n\
opcache.fast_shutdown = 1' > /usr/local/etc/php/conf.d/production-tuning.ini

# Environment variables Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_PROCESS_TIMEOUT=600 \
    COMPOSER_MAX_PARALLEL_HTTP=4

# Set working directory
WORKDIR /var/www

# 1. Optimasi Cache: Install dependencies PHP terlebih dahulu
COPY composer.json composer.lock ./

RUN composer config --global repo.packagist composer https://packagist.org \
    && (composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist \
        || (echo "Retrying composer install (attempt 2)..." && sleep 5 && composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist) \
        || (echo "Retrying composer install (attempt 3)..." && sleep 10 && composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist))

# 2. Optimasi Cache: Install dependencies NPM (termasuk Vite)
COPY package.json package-lock.json ./
RUN npm install

# 3. Copy seluruh source code aplikasi
COPY . /var/www

# 4. Generate optimized autoloader & build asset frontend (Vite)
RUN composer dump-autoload --optimize --no-dev \
    && npm run build \
    && rm -rf node_modules

# Set permission storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Konfigurasi Nginx Berkecepatan Tinggi (Gzip, FastCGI Buffering, Static Caching)
RUN echo 'server {\n\
    listen 80;\n\
    index index.php index.html;\n\
    root /var/www/public;\n\
    client_max_body_size 25M;\n\
\n\
    # Gzip Compression\n\
    gzip on;\n\
    gzip_vary on;\n\
    gzip_min_length 256;\n\
    gzip_proxied any;\n\
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    # Static Assets Caching\n\
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {\n\
        expires 30d;\n\
        add_header Cache-Control "public, no-transform";\n\
        access_log off;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        fastcgi_buffer_size 32k;\n\
        fastcgi_buffers 16 16k;\n\
        include fastcgi_params;\n\
    }\n\
}' > /etc/nginx/sites-available/default

# Konfigurasi Supervisor & Startup Command yang Cepat & Optimal
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
command=/bin/sh -c "php /var/www/artisan migrate --force && php /var/www/artisan config:cache && php /var/www/artisan route:cache && php /var/www/artisan view:cache && php /var/www/artisan storage:link --force"\n\
autostart=true\n\
autorestart=false\n\
startretries=1\n' > /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
