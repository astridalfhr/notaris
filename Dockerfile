# ---------- Stage 1: install dependencies (Composer) ----------
FROM public.ecr.aws/docker/library/composer:2 AS vendor
WORKDIR /app

# install vendor (tanpa dev)
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# copy source dan optimalkan autoloader
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ---------- Stage 2: Runtime (Apache + PHP) ----------
FROM public.ecr.aws/docker/library/php:8.2-apache

# aktifkan mod_rewrite untuk CI4
RUN a2enmod rewrite

# ekstensi PHP yang umum dipakai CI4
RUN docker-php-ext-install pdo pdo_mysql mysqli

# direktori kerja
WORKDIR /var/www/html

# copy hasil build vendor stage
COPY --from=vendor /app /var/www/html

# arahkan DocumentRoot ke /public dan izinkan .htaccess
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
 && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/ci4.conf \
 && a2enconf ci4

# permission untuk writable & public
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

# environment & port
ENV CI_ENVIRONMENT=production
EXPOSE 80

# entrypoint: generate .env dari Railway Variables lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
