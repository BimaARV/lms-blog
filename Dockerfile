# ============================================
# Laravel 11 Dockerfile (PHP 8.3 FPM)
# ============================================
FROM php:8.3-fpm AS base

ARG WWWGROUP
ARG NODE_VERSION=20

WORKDIR /var/www/html

# System deps
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libicu-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
    supervisor nginx \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip bcmath intl gd opcache pcntl sockets \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node 20 (for Vite build)
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
 && apt-get install -y nodejs \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && { \
    echo 'memory_limit = 512M'; \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 100M'; \
    echo 'max_execution_time = 300'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 256'; \
    echo 'opcache.max_accelerated_files = 20000'; \
    echo 'opcache.validate_timestamps = 0'; \
  } > /usr/local/etc/php/conf.d/zz-app.ini

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
 && rm -rf /etc/nginx/sites-enabled/*

# Supervisor config (run php-fpm + nginx + queue)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

COPY . /var/www/html
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist || true
RUN npm ci || true
RUN npm run build || true

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
