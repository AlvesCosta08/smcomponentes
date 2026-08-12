# Usando PHP 8.3 Alpine (imagem leve)
FROM php:8.3-fpm-alpine

# Instala dependências do sistema (CORRIGIDO)
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    openssl-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libpq-dev \
    icu-dev \
    gmp-dev \
    oniguruma-dev \
    libmemcached-dev \
    libffi-dev \
    bash \
    vim \
    nano \
    supervisor \
    nodejs \
    npm \
    autoconf \
    g++ \
    make

# Instala extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    mysqli \
    curl \
    gd \
    mbstring \
    xml \
    zip \
    bcmath \
    intl \
    opcache \
    soap \
    gmp \
    ldap \
    imap \
    tidy \
    exif \
    sockets \
    pcntl \
    gettext \
    shmop \
    sysvmsg \
    sysvsem \
    sysvshm

# Instala extensões PECL
RUN pecl install redis memcached imagick && \
    docker-php-ext-enable redis memcached imagick

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cria diretórios
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/log

WORKDIR /var/www/html

# Copia arquivos de dependências primeiro (cache)
COPY composer.json composer.lock package.json package-lock.json ./

# Instala dependências do Composer
RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist --optimize-autoloader

# Instala dependências Node
RUN npm ci --only=production || npm install --production

# Copia o restante do projeto
COPY . .

# Builda assets
RUN npm run build

# Executa scripts pós-instalação
RUN composer install --no-interaction --optimize-autoloader

# Cria link storage
RUN php artisan storage:link || true

# Permissões
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache && \
    chmod -R 755 /var/www/html/public

# Configura PHP
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/laravel.ini && \
    echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/laravel.ini && \
    echo "date.timezone = America/Sao_Paulo" >> /usr/local/etc/php/conf.d/laravel.ini

# Configura Supervisor
RUN cat > /etc/supervisord.conf << 'EOL'
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/php-fpm.log
stderr_logfile=/var/log/php-fpm-error.log

[program:queue-worker]
command=php /var/www/html/artisan queue:work --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/queue-worker.log
stderr_logfile=/var/log/queue-worker-error.log

[program:schedule-worker]
command=php /var/www/html/artisan schedule:work
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/schedule-worker.log
stderr_logfile=/var/log/schedule-worker-error.log
EOL

# Cria entrypoint
RUN echo '#!/bin/sh\n\
set -e\n\
\n\
# Permissões\n\
chown -R www-data:www-data /var/www/html/storage\n\
chown -R www-data:www-data /var/www/html/bootstrap/cache\n\
\n\
# Limpa cache\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
php artisan route:clear\n\
\n\
# Cria .env se não existir\n\
if [ ! -f .env ]; then\n\
    cp .env.example .env\n\
    php artisan key:generate\n\
fi\n\
\n\
# Rodar migrations\n\
php artisan migrate --force\n\
\n\
# Otimiza\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Inicia Supervisor\n\
exec /usr/bin/supervisord -c /etc/supervisord.conf\n' > /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Porta
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]