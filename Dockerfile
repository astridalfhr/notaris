# ===== Runtime: PHP 8.2 + Apache (ECR mirror) =====
FROM public.ecr.aws/docker/library/php:8.2-apache

# Pakai bash + noninteractive biar build stabil
SHELL ["/bin/bash", "-lc"]
ENV DEBIAN_FRONTEND=noninteractive

# Enable mod_rewrite & pasang dependensi/ekstensi CI4
RUN set -eux \
  && a2enmod rewrite headers env expires \
  && apt-get update \
  && apt-get install -y --no-install-recommends \
       unzip git libicu-dev libzip-dev \
  && docker-php-ext-install intl mbstring zip pdo pdo_mysql mysqli \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
# Copy source code lebih dulu (agar layer cache Composer bisa dipakai kalau ada)
COPY . /var/www/html

# Install Composer langsung (tanpa image composer)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

# Install vendor (prod)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress \
 && composer dump-autoload -o

# Set DocumentRoot -> /public dan izinkan .htaccess
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
 && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/ci4.conf \
 && a2enconf ci4

# Permission penting untuk CI4
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

ENV CI_ENVIRONMENT=production
EXPOSE 80

# Entrypoint: tulis .env dari Variables Railway lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
