#!/bin/bash

composer install
chmod -R 777 ./storage
php artisan migrate
php artisan key:generate
php-fpm