# ===== PHP 8.2 + Apache (ECR mirror), tanpa apt-get =====
FROM public.ecr.aws/docker/library/php:8.2-apache

# aktifkan mod_rewrite
RUN a2enmod rewrite

# ekstensi database yang dibutuhkan CI4
RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html
# copy source code
COPY . /var/www/html

# arahkan DocumentRoot -> /public & izinkan .htaccess
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
 && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/ci4.conf \
 && a2enconf ci4

# permission untuk CI4
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

ENV CI_ENVIRONMENT=production
EXPOSE 80

# entrypoint: tulis .env dari env Railway lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
