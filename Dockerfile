FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    pkg-config \
    libonig-dev \
    libicu-dev \
    && docker-php-ext-install intl \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/

COPY docker/apache/sso.conf /etc/apache2/sites-available/sso.conf
RUN a2dissite 000-default && a2ensite sso

RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions \
    && chmod 1733 /var/lib/php/sessions
