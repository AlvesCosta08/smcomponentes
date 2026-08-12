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
    libpq-dev \
    libicu-dev \
    libgmp-dev \
    libreadline-dev \
    libedit-dev \
    libsqlite3-dev \
    locales \
    vim \
    nano \
    htop \
    net-tools \
    && rm -rf /var/lib/apt/lists/*

# Instala Node.js 22.x e npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm@10.8.2

# Instala PHP 8.3 com todas extensões necessárias
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
    && rm -rf /var/lib/apt/lists/*

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cria usuário para o Laravel
RUN groupadd -g 1000 laravel && \
    useradd -u 1000 -g laravel -m -s /bin/bash laravel

# Configura locale
RUN locale-gen pt_BR.UTF-8 && \
    update-locale LANG=pt_BR.UTF-8 LC_ALL=pt_BR.UTF-8
ENV LANG=pt_BR.UTF-8 \
    LC_ALL=pt_BR.UTF-8 \
    LANGUAGE=pt_BR:pt:en

# Configura PHP
RUN echo "upload_max_filesize = 100M" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "memory_limit = 256M" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "max_execution_time = 300" >> /etc/php/8.3/cli/conf.d/99-overrides.ini && \
    echo "date.timezone = America/Sao_Paulo" >> /etc/php/8.3/cli/conf.d/99-overrides.ini

RUN echo "upload_max_filesize = 100M" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "memory_limit = 256M" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "max_execution_time = 300" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini && \
    echo "date.timezone = America/Sao_Paulo" >> /etc/php/8.3/fpm/conf.d/99-overrides.ini

# Cria diretórios necessários
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/log

WORKDIR /var/www/html

# Copia arquivos de dependências primeiro (melhor cache)
COPY composer.json composer.lock package.json package-lock.json ./

# Instala dependências do Composer
RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist --optimize-autoloader

# Instala dependências Node
RUN npm ci --only=production || npm install --production

# Copia o restante do projeto
COPY . .

# Builda os assets
RUN npm run build

# Executa scripts pós-instalação do Composer
RUN composer install --no-interaction --optimize-autoloader

# Cria link storage
RUN php artisan storage:link || true

# Permissões
RUN chown -R laravel:laravel /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache && \
    chmod -R 755 /var/www/html/public

# Configura PHP-FPM
RUN sed -i 's/^listen = .*/listen = 9000/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;listen.owner = .*/listen.owner = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;listen.group = .*/listen.group = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;listen.mode = .*/listen.mode = 0660/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^user = .*/user = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^group = .*/group = laravel/' /etc/php/8.3/fpm/pool.d/www.conf && \
    sed -i 's/^;clear_env = no/clear_env = no/' /etc/php/8.3/fpm/pool.d/www.conf

# Cria entrypoint
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Permissões\n\
chown -R laravel:laravel /var/www/html/storage\n\
chown -R laravel:laravel /var/www/html/bootstrap/cache\n\
\n\
# Aguarda banco de dados\n\
if [ -n "$DB_HOST" ]; then\n\
    echo "Aguardando banco de dados..."\n\
    while ! nc -z $DB_HOST $DB_PORT; do\n\
        sleep 1\n\
    done\n\
    echo "Banco de dados disponível!"\n\
fi\n\
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
# Inicia PHP-FPM\n\
php-fpm8.3 -F\n' > /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Portas
EXPOSE 8000 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]