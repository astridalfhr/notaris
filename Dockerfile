# ===== PHP 8.2 + Apache (mirror ECR yang stabil)
FROM public.ecr.aws/docker/library/php:8.2-apache

# Aktifkan mod_rewrite untuk CI4
RUN a2enmod rewrite

# Install dependency & ekstensi PHP yang dibutuhkan CI4 (termasuk intl)
RUN apt-get update && apt-get install -y libicu-dev unzip git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Set workdir
WORKDIR /var/www/html

# Copy source code
COPY . .

# Set DocumentRoot -> /public dan izinkan .htaccess
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && printf "\n<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

# Install composer dependencies (copy composer binary dari image resmi)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permission untuk folder writable dan public
RUN chown -R www-data:www-data writable public \
    && chmod -R 775 writable

# Mode production (bisa di-override dari env Railway)
ENV CI_ENVIRONMENT=production

EXPOSE 80

# Entrypoint: tulis .env dari Environment Variables lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
