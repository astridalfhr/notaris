# ---- Stage composer: pasang vendor saat build ----
FROM public.ecr.aws/docker/library/composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ---- Runtime: Apache + PHP siap pakai (mod_rewrite sudah aktif) ----
FROM ghcr.io/webdevops/php-apache:8.2
ENV WEB_DOCUMENT_ROOT=/app/public

WORKDIR /app
COPY --from=vendor /app /app

# Permission untuk CI4
RUN chown -R www-data:www-data /app/writable /app/public

EXPOSE 80

# Entrypoint: tulis .env dari env Railway lalu start Apache
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
