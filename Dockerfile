# =========================
# Stage 1: Builder PHP
# =========================
FROM php:8.2-fpm-alpine AS builder

RUN sed -i 's|https://|http://|' /etc/apk/repositories \
 && apk add --no-cache \
    autoconf g++ make \
    icu-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    freetype-dev libzip-dev libxml2-dev oniguruma-dev \
    curl-dev postgresql-dev linux-headers

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) \
    pdo_pgsql pgsql gd zip intl mbstring xml dom bcmath curl opcache exif calendar

RUN pecl install redis && docker-php-ext-enable redis

# =========================
# Stage 2: Runtime PHP + NGINX
# =========================
FROM php:8.2-fpm-alpine

RUN sed -i 's|https://|http://|' /etc/apk/repositories \
 && apk add --no-cache tzdata nginx supervisor shadow su-exec libcap \
    icu libpng libjpeg-turbo libwebp freetype libzip libxml2 oniguruma \
    curl postgresql-libs \
    && rm -rf /var/cache/apk/*

ENV TZ=Asia/Jakarta

COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# =========================
# Create non-root user
# =========================
ARG APP_UID=1000
ARG APP_GID=1000
RUN addgroup -g ${APP_GID} app \
 && adduser -D -u ${APP_UID} -G app app

# =========================
# Directories
# =========================
RUN mkdir -p \
    /var/www/html \
    /var/cache/nginx \
    /var/log/nginx \
    /var/log/php \
    /var/log/supervisord \
    /run/nginx \
    /run/supervisord \
    /tmp/fastcgi_temp /tmp/client_temp /tmp/proxy_temp \
    /tmp/uwsgi_temp /tmp/scgi_temp \
 && chown -R app:app /var/www/html /var/cache/nginx \
    /var/log/nginx /var/log/php /run/nginx /run/supervisord \
    /var/log/supervisord /var/lib/nginx \
 && chmod 1777 /tmp \
 && chmod -R 755 /var/lib/nginx /run/nginx /var/log/nginx /var/log/php

RUN touch /var/log/supervisord/supervisord.log && chown app:app /var/log/supervisord/supervisord.log

# =========================
# Copy application & configs
# =========================
COPY --chown=app:app ./git/ /var/www/html/
COPY ./nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./nginx/conf.d /etc/nginx/conf.d
COPY ./config/supervisord-fpm.conf /etc/supervisord.conf
COPY ./config/php/php.ini /usr/local/etc/php/php.ini

# =========================
# Healthcheck
# =========================
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
  CMD php-fpm -t && nginx -t || exit 1

WORKDIR /var/www/html
EXPOSE 8080

CMD ["/bin/sh", "-c", "chown app:app /var/www/html/runtime /var/www/html/runtime_api /var/www/html/runtime_sessions /var/www/html/assets /var/www/html/app_asset /var/www/html/uploads /tmp /var/cache/nginx /run/nginx 2>/dev/null || true; exec su-exec app /usr/bin/supervisord -c /etc/supervisord.conf -n"]
