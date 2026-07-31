#!/bin/sh
set -eu

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=UserSeeder --force
php artisan schedule:work &

exec "$@"
