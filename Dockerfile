# Usando Ubuntu como base
FROM ubuntu:24.04

# Evita prompts interativos durante a instalação
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Sao_Paulo

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    apt-utils \
    gnupg \
    curl \
    wget \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    libmemcached-dev \
    libpq-dev \
    libicu-dev \
    libgmp-dev \
    libmagickwand-dev \
    libreadline-dev \
    libedit-dev \
    libsqlite3-dev \
    libgdbm-dev \
    libffi-dev \
    libbz2-dev \
    liblzma-dev \
    libncurses5-dev \
    libglib2.0-dev \
    libc6-dev \
    locales \
    vim \
    nano \
    htop \
    net-tools \
    && rm -rf /var/lib/apt/lists/*

# Instala Node.js 20.x e npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Instala PHP 8.3 com extensões
RUN apt-get update && apt-get install -y \
    php8.3 \
    php8.3-cli \
    php8.3-common \
    php8.3-fpm \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-sqlite3 \
    php8.3-curl \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-opcache \
    php8.3-readline \
    php8.3-soap \
    php8.3-gmp \
    php8.3-ldap \
    php8.3-imap \
    php8.3-tidy \
    php8.3-redis \
    php8.3-memcached \
    php8.3-imagick \
    php8.3-dev \
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala Supervisor (para gerenciar processos)
RUN apt-get update && apt-get install -y supervisor && rm -rf /var/lib/apt/lists/*

# Cria usuário para o Laravel (não root)
RUN groupadd -g 1000 laravel && \
    useradd -u 1000 -g laravel -m -s /bin/bash laravel

# Configura locale
RUN locale-gen pt_BR.UTF-8 && \
    update-locale LANG=pt_BR.UTF-8 LC_ALL=pt_BR.UTF-8
ENV LANG=pt_BR.UTF-8 \
    LC_ALL=pt_BR.UTF-8 \
    LANGUAGE=pt_BR:pt:en

# Configura PHP CLI
RUN echo "upload_max_filesize = 100M" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "memory_limit = 256M" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "max_execution_time = 300" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "date.timezone = America/Sao_Paulo" >> /etc/php/8.3/cli/conf.d/99-overrides.ini

# Configura PHP-FPM
RUN echo "upload_max_filesize = 100M" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "memory_limit = 256M" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "max_execution_time = 300" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "date.timezone = America/Sao_Paulo" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini

# Cria diretórios necessários
RUN mkdir -p /var/log/supervisor && \
    mkdir -p /var/www/html && \
    mkdir -p /var/www/html/storage && \
    mkdir -p /var/www/html/bootstrap/cache

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos do composer primeiro (melhor para cache de layers)
COPY composer.json composer.lock ./

# Instala dependências do Composer com otimizações
RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist --optimize-autoloader

# Copia o restante dos arquivos do projeto
COPY . .

# Instala dependências Node e builda assets
RUN npm install && npm run build

# Permissões corretas
RUN chown -R laravel:laravel /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache && \
    chmod -R 755 /var/www/html/public

# Configuração do Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configuração do PHP-FPM
RUN sed -i 's/^listen = .*/listen = 9000/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;listen.owner = .*/listen.owner = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;listen.group = .*/listen.group = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;listen.mode = .*/listen.mode = 0660/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^user = .*/user = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^group = .*/group = laravel/' /etc/php/8.3/fpm/pool.d/www.conf

# Cria script de entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Script de healthcheck (opcional)
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD php /var/www/html/artisan health-check || exit 1

# Portas
EXPOSE 8000 9000

# Usa entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Comando padrão
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]