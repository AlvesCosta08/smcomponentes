FROM php:8.4-apache

# Instala dependências do sistema e MySQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    nano \
    default-mysql-server \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP necessárias
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    curl \
    opcache

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura o Apache
RUN a2enmod rewrite \
    && a2enmod headers \
    && a2enmod expires

# Configura o Apache para apontar para public
RUN sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's!/var/www/!/var/www/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*

# Instala Node.js 20.x e npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@10.8.2 \
    && npm --version

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto
COPY . .

# Cria diretórios necessários
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && mkdir -p public/storage \
    && chmod -R 777 storage \
    && chmod -R 777 bootstrap/cache

# Cria .env se não existir
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Gera APP_KEY
RUN php artisan key:generate --force || true

# Instala dependências PHP
RUN composer install \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

# Executa scripts do Composer
RUN php artisan package:discover --ansi || true

# Instala dependências Node
RUN if [ -f package.json ]; then \
        npm install && \
        npm run build || true; \
    fi

# Configura permissões finais
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/public

# Configura o php.ini
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "date.timezone = America/Sao_Paulo" >> /usr/local/etc/php/conf.d/timezone.ini

# Configura MySQL para aceitar conexões
RUN mkdir -p /var/run/mysqld \
    && chown -R mysql:mysql /var/run/mysqld \
    && chmod -R 777 /var/run/mysqld

# Configura o MySQL para escutar em todas as interfaces
RUN echo "[mysqld]" > /etc/mysql/mysql.conf.d/custom.cnf \
    && echo "bind-address = 0.0.0.0" >> /etc/mysql/mysql.conf.d/custom.cnf \
    && echo "character-set-server = utf8mb4" >> /etc/mysql/mysql.conf.d/custom.cnf \
    && echo "collation-server = utf8mb4_unicode_ci" >> /etc/mysql/mysql.conf.d/custom.cnf \
    && echo "max_allowed_packet = 256M" >> /etc/mysql/mysql.conf.d/custom.cnf

# Script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 3306

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["start"]