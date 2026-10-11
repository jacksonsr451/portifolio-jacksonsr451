# syntax=docker/dockerfile:1@sha256:4edf897a3ffa55b89f906fc8cc78afdb3f1834cc9c7083565e611a8a7d5fe99e
FROM node:22-alpine@sha256:0a7108bf6c7bf5de370ffb1a3ed6be93d405b43ff159f681a8d18c0e2bc2e402 AS assets
WORKDIR /src
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js .
RUN npm run build

FROM composer:2.10@sha256:af98f42dfff7c68ba8d53c2164fd9fde1087b7d449514baa38c418b1f6bc4bac AS dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-progress --optimize-autoloader

FROM php:8.4-fpm-alpine@sha256:78cd8de9970a9cd6ff4d98860a94eb5bf37dd2d5776b4785562dbfad01c29d5a AS runtime
RUN mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs /var/www/html/bootstrap/cache \
    && sed -i 's#;pid = run/php-fpm.pid#pid = /tmp/php-fpm.pid#' /usr/local/etc/php-fpm.conf \
    && sed -i 's#listen = 127.0.0.1:9000#listen = 9000#' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's#^user = www-data#;user = www-data#' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's#^group = www-data#;group = www-data#' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's#;clear_env = no#clear_env = no#' /usr/local/etc/php-fpm.d/www.conf
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=dependencies --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /src/public/build ./public/build
RUN chmod -R ug+rwX storage bootstrap/cache
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
USER www-data
EXPOSE 9000
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 CMD php artisan about --only=environment >/dev/null || exit 1
CMD ["php-fpm", "-F"]

FROM nginx:1.28-alpine@sha256:a8b39bd9cf0f83869a2162827a0caf6137ddf759d50a171451b335cecc87d236 AS web
COPY docker/nginx-main.conf /etc/nginx/nginx.conf
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=runtime /var/www/html/public /var/www/html/public
USER nginx
ENTRYPOINT []
CMD ["nginx", "-g", "daemon off;"]
EXPOSE 8080
