#!/bin/sh

php artisan  passport:install
chmod -R 660  storage/oauth-private.key
chmod -R 660  storage/oauth-public.key

php-fpm