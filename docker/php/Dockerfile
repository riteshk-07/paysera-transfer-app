FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    libpq-dev \
    mariadb-client \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql intl mbstring zip opcache

# Install redis ext (phpredis) and other useful extensions
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Create working dir
WORKDIR /srv/www

# Ensure permissions
RUN useradd -m symfony && chown -R symfony:symfony /srv/www

# Switch to non-root user
USER symfony

# Expose fpm port (not strictly necessary with nginx proxy)
EXPOSE 9000

CMD ["php-fpm"]
