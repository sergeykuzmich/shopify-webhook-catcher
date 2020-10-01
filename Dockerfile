# Prepare Composer dependencies
FROM composer AS deps
WORKDIR /deps
COPY composer.* ./
# Install deps
RUN composer install --ignore-platform-reqs --no-autoloader
RUN composer global require phpunit/phpunit "7.3.*"

# Setup common layer
FROM php:7-apache AS common
# Enable Apache's ModRewrite
RUN a2enmod rewrite
# Enable mongodb extension & install libraries to support SSL protocol
RUN apt-get update && \
    apt-get install libssl-dev pkg-config --assume-yes && \
    pecl install mongodb && \
    docker-php-ext-enable mongodb
COPY --from=deps /usr/bin/composer ./composer
COPY src/ ./src/
COPY --from=deps /deps/vendor ./vendor
COPY .htaccess index.php composer.json composer.lock ./

# Build QA image
FROM common AS qa
RUN pecl install xdebug pcov && \
    docker-php-ext-enable xdebug pcov
# Copy QA files
COPY . .
COPY --from=deps /tmp/vendor/phpunit ./vendor/phpunit
RUN ./composer dumpautoload && rm ./composer composer.*
# Launch PHPUnit tests
CMD ["vendor/bin/phpunit"]

# Build production image
FROM common AS application
RUN ./composer dumpautoload --no-dev && rm ./composer composer.*
