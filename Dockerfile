# Stage 1: install dependencies
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Stage 2: runtime
FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

# arahkan DocumentRoot ke /public & izinkan .htaccess
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
 && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/ci4.conf \
 && a2enconf ci4

RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public
EXPOSE 80
ENV CI_ENVIRONMENT=production

# start via entrypoint (akan menulis .env dari Variables Railway)
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]