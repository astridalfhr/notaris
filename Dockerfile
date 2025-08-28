# ===== PHP 8.2 + Apache (mirror ECR yang stabil)
FROM public.ecr.aws/docker/library/php:8.2-apache

# Aktifkan mod_rewrite untuk CI4
RUN a2enmod rewrite

# Dependency untuk intl & zip (+ alat bantu)
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zlib1g-dev \
    pkg-config \
    unzip git \
 && rm -rf /var/lib/apt/lists/*

# Konfigurasi & install ekstensi PHP (intl, zip, PDO MySQL)
RUN docker-php-ext-configure intl \
 && docker-php-ext-configure zip \
 && docker-php-ext-install intl zip pdo pdo_mysql mysqli

# Set workdir dan copy source
WORKDIR /var/www/html
COPY . .

# Set DocumentRoot -> /public dan izinkan .htaccess
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
 && printf "\n<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

# Composer install (copy biner composer dari image resmi)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permission untuk folder writable & public
RUN chown -R www-data:www-data writable public \
 && chmod -R 775 writable

# Mode production (bisa dioverride via env)
ENV CI_ENVIRONMENT=production

EXPOSE 80

# Entrypoint: generate .env dari ENV Railway lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
