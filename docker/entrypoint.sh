#!/bin/sh
set -e

echo ">>> [Entrypoint] Menyiapkan environment Laravel..."

# Pastikan folder storage dan cache ada dan memiliki permission yang benar
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate symlink storage jika belum ada
if [ ! -L "/var/www/html/public/storage" ]; then
    php artisan storage:link --force || true
fi

# Jalankan cache optimization bila production
if [ "$APP_ENV" = "production" ]; then
    echo ">>> [Entrypoint] Mengoptimalkan cache Laravel..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Jalankan migrasi database hanya pada container utama (bukan worker)
if [ "$1" = "/usr/bin/supervisord" ] || [ "$1" = "supervisord" ]; then
    echo ">>> [Entrypoint] Menjalankan migrasi database..."
    php artisan migrate --force || true
fi

echo ">>> [Entrypoint] Menjalankan perintah utama: $@"
exec "$@"
