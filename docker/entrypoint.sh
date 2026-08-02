#!/bin/sh
set -eu

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=UserSeeder --force

if [ "${SEED_SAMPLE_DATA:-false}" = "true" ]; then
    php artisan db:seed --class=SampleDataSeeder --force
fi

php artisan schedule:work &

exec "$@"
