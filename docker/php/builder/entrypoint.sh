#!/bin/bash

composer install

cat ./.env.template > ./.env

php artisan key:generate
php artisan orchid:install
php artisan migrate
php artisan orchid:admin admin admin@admin.com admin@admin.com

chown -R www-data:www-data /var/www/html

find /var/www/html -type f -exec chmod 644 {} \;
find /var/www/html -type d -exec chmod 755 {} \;

chmod -R gu+w storage
chmod -R guo+w storage

php ./vendor/bin/phpunit

php-fpm
