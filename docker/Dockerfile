FROM node:23 as node

FROM php:8.3-apache as base

# Middleware Instaration
# memo:
# - python2 : pdfpy が使う
# - libcurl4-openssl-dev : curl/GuzzleHTTP が使う
# - Debian での wkhtmltopdf は xvfb に依存してる
# - imagemagick : gm コマンドでpdfを画像化している
RUN apt-get update \
    && apt-get install -y \
    git \
    zip \
    unzip \
    vim \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libicu-dev \
    wkhtmltopdf \
    xvfb \
    fonts-ipafont \
    python3 \
    libcurl4-openssl-dev \
    imagemagick \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install intl mbstring pdo_mysql bcmath gd curl zip \
    && pecl install xdebug \
    && fc-cache -fv \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Laravelで必要になるmodRewriteを有効化
RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled
RUN /bin/sh -c a2enmod rewrite

# 設定ファイルをdockerコンテナ内のPHP、Apacheに読み込ませる
ADD php.ini /usr/local/etc/php/
ADD 000-default.conf /etc/apache2/sites-enabled/

# Composer Instaration
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Npm Instaration
COPY --from=node /usr/local/include/node /usr/local/include/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node

# using python3
RUN update-alternatives --install /usr/bin/python python /usr/bin/python3 1 \
    && update-alternatives --set python /usr/bin/python3

#コンテナ内に入った時のディレクトリを指定
WORKDIR /var/www/html

EXPOSE 80
