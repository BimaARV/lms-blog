#!/bin/bash
set -e

cd /var/www/html

# Jalankan perintah berdasarkan argumen
if [ "$1" == "artisan" ]; then
    shift
    exec php artisan "$@"
elif [ "$1" == "composer" ]; then
    shift
    exec composer "$@"
elif [ "$1" == "npm" ]; then
    shift
    exec npm "$@"
fi

# Default: jalankan supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
