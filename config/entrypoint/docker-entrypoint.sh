#!/bin/sh
set -e

rm -f /run/nginx/nginx.pid /run/nginx/*.pid

chown app:app \
    /var/www/html/runtime \
    /var/www/html/runtime_api \
    /var/www/html/runtime_sessions \
    /var/www/html/assets \
    /var/www/html/app_asset \
    /var/www/html/uploads \
    /tmp \
    /var/cache/nginx \
    /run/nginx \
    2>/dev/null || true

exec su-exec app /usr/bin/supervisord -c /etc/supervisord.conf -n
