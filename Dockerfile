FROM php:8.4-fpm

# ============================================
# VARIÁVEIS DE AMBIENTE
# ============================================
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV NODE_VERSION=20

# ============================================
# INSTALAR DEPENDÊNCIAS DO SISTEMA
# ============================================
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    unzip \
    zip \
    gnupg \
    ca-certificates \
    apt-transport-https \
    lsb-release \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    libicu-dev \
    libsodium-dev \
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@10.8.2

# ============================================
# INSTALAR EXTENSÕES PHP
# ============================================
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    sockets \
    soap \
    sodium

# ============================================
# INSTALAR COMPOSER
# ============================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update

# ============================================
# INSTALAR DEPENDÊNCIAS NODE GLOBAIS
# ============================================
RUN npm install -g \
    vite \
    laravel-vite-plugin \
    @vitejs/plugin-vue \
    --legacy-peer-deps \
    && npm cache clean --force

# ============================================
# DEFINIR DIRETÓRIO DE TRABALHO
# ============================================
WORKDIR /var/www/html

# ============================================
# COPIAR ARQUIVOS DE DEPENDÊNCIA PRIMEIRO (BETTER CACHE)
# ============================================
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# ============================================
# INSTALAR DEPENDÊNCIAS PHP
# ============================================
RUN composer install \
    --no-interaction \
    --optimize-autoloader \
    --no-dev \
    --prefer-dist \
    --no-scripts

# ============================================
# INSTALAR DEPENDÊNCIAS NODE
# ============================================
RUN npm ci --no-audit --no-fund --prefer-offline --legacy-peer-deps \
    && npm cache clean --force

# ============================================
# COPIAR O RESTANTE DO CÓDIGO
# ============================================
COPY . .

# ============================================
# CRIAR DIRETÓRIOS DE CACHE E LOGS
# ============================================
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p public/build \
    && chmod -R 777 storage bootstrap/cache public/build

# ============================================
# COMPILAR ASSETS (VITE)
# ============================================
RUN npm run build || echo "Build de assets ignorado (sem package.json configurado)"

# ============================================
# CONFIGURAR PERMISSÕES FINAIS
# ============================================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/public/build 2>/dev/null || true

# ============================================
# COPIA ENTRYPOINT E CONFIGURA
# ============================================
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ============================================
# EXPORTA PORTA
# ============================================
EXPOSE 8000

# ============================================
# ENTRYPOINT E CMD
# ============================================
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]