#!/bin/bash

chown -R www-data:www-data /var/www

composer install

cd /var/www/html

cat ./.env.template > ./.env

php artisan key:generate
php artisan orchid:install
php artisan migrate
php artisan orchid:admin admin admin@admin.com admin@admin.com

./vendor/bin/phpunit

chown -R www-data:www-data /var/www

php-fpm
