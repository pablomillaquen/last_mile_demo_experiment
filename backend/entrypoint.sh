#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    echo "Creating Laravel project in temp dir..."
    cd /tmp
    composer create-project laravel/laravel . --no-interaction --prefer-dist 2>&1
    echo "Copying Laravel files to working directory..."
    cp -r /tmp/. /var/www/html/
    rm -rf /tmp/*
    cd /var/www/html
    echo "Laravel project created."
fi

php artisan serve --host=0.0.0.0 --port=8000
