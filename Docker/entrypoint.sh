#!/bin/bash

if [ ! -f vendor/autoload.php ]; then
    composer install --no-progress --no-interaction --no-suggest --optimize-autoloader
fi

if [ ! -f .env ]; then
    echo "Creating .env file for env $APP_ENV"
    cp .env.example .env
else
    echo "Using existing .env file"
fi

php artisan passport:install

php artisan serve --port=$PORT --host=0.0.0.0 --env=.env

exec docker-php-entrypoint "$@"

