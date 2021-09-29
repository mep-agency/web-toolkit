FROM php:8.0-apache

# Copy PHP extension installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

# Install dependencies
RUN apt-get -y update \
    && install-php-extensions intl \
    && docker-php-ext-install pdo_mysql \
    && install-php-extensions redis \
    && docker-php-ext-install opcache

# PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY ../files/php/env-vars.ini $PHP_INI_DIR/conf.d/
COPY ../files/php/uploads.ini $PHP_INI_DIR/conf.d/
COPY ../files/php/opcache.ini $PHP_INI_DIR/conf.d/
COPY ../files/php/realpath-cache.ini $PHP_INI_DIR/conf.d/

# Enable Apache extensions
RUN a2enmod expires headers

# Copy Apache configuration
COPY ../files/apache/mods-enabled/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
COPY ../files/apache/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Change document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Init workspace
RUN rm -rf /var/www/html/*
# Copy initialization script
COPY ../files/init-and-run.sh /var/www/html

# Set some default values that can be overridden if needed
ENV APACHE_START_SERVERS=5
ENV APACHE_MIN_SPARE_SERVERS=5
ENV APACHE_MAX_SPARE_SERVERS=10
ENV APACHE_MAX_REQUEST_WORKERS=150
ENV APACHE_MAX_CONNECTIONS_PER_CHILD=0

CMD ["/var/www/html/init-and-run.sh"]