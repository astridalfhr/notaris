# ---- Runtime: Apache + PHP siap pakai (mod_rewrite aktif) ----
FROM ghcr.io/webdevops/php-apache:8.2
ENV WEB_DOCUMENT_ROOT=/app/public

WORKDIR /app

# Install Composer langsung (hindari menarik image composer dari registry)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

# Pasang dependensi PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Copy source code
COPY . .

# Optimalkan autoloader untuk produksi
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Permission untuk CI4
RUN chown -R www-data:www-data /app/writable /app/public

EXPOSE 80

# Tulis .env dari Railway Variables lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
