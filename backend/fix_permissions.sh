#!/bin/bash

# Fix permissions script
cd /var/www/kasir-web/backend

# Stop potential permission issues
sudo rm -f storage/logs/laravel.log
sudo rm -rf storage/logs/*
sudo rm -rf bootstrap/cache/*.php

# Fix ownership
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/

# Fix permissions
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/

# Recreate log file with correct permissions
sudo touch storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log

# Install vendor dependencies
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

echo "Done!"
