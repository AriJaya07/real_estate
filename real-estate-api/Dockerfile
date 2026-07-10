FROM serversideup/php:8.5-fpm-nginx

USER root
RUN install-php-extensions pdo_pgsql

USER www-data
COPY --chown=www-data:www-data . /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction