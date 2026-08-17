FROM php:8.3-apache

# Instala dependências do sistema
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
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires

# Configura o Apache para apontar para public
RUN sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's!/var/www/!/var/www/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*

# Instala Node.js 20.x e npm compatível
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@10.8.2 \
    && npm --version

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto
COPY . .

# Cria arquivo .env se não existir
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Instala dependências PHP
RUN composer install \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --ignore-platform-req=ext-zip

# Instala dependências Node (se tiver package.json)
RUN if [ -f package.json ]; then \
        npm install && \
        npm run build || true; \
    fi

# Configura permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/public

# Configura o php.ini
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]