FROM mcr.microsoft.com/devcontainers/php:8.3-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
      libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev default-mysql-client unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" intl gd zip bcmath exif pdo_mysql \
 && rm -rf /var/lib/apt/lists/*
