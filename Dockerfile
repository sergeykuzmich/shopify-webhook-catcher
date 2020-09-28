# Prepare Composer dependencies
FROM composer AS deps
WORKDIR /deps
COPY composer.* ./
RUN composer install --ignore-platform-reqs

# Build application image
FROM php:7-apache
# Enable Apache's ModRewrite
RUN a2enmod rewrite
# Enable mongodb extension & install libraries to support SSL protocol
RUN apt-get update && \
    apt-get install libssl-dev pkg-config --yes
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb
# Copy deps & src
COPY --from=deps /deps/vendor ./vendor
COPY index.php .
