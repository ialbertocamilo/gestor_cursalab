# -------------------------------------------------------------------------------------------------------
# COMPOSER
# -------------------------------------------------------------------------------------------------------

FROM 505992365906.dkr.ecr.us-east-1.amazonaws.com/composer:2.0.16 as composer_base

USER composer

COPY --chown=composer . .

RUN composer install --no-dev --prefer-dist


# -------------------------------------------------------------------------------------------------------
# FRONTEND
# -------------------------------------------------------------------------------------------------------

# For the frontend, we want to get all the Laravel files,
# and run a production compile
FROM node:12.22.12-alpine as frontend

# We need to copy in the Laravel files to make everything is available to our frontend compilation
COPY --from=composer_base /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes

WORKDIR /opt/apps/laravel-in-kubernetes

COPY docker/vuejs/.env_vuejs ./.env

# We want to install all the NPM packages,
# and compile the MIX bundle for production
RUN npm install && \
    npm install cross-env --save-dev && \
    npm install imagemin@^5.0.0 || ^6.0.0 || ^7.0.0 --save-dev && \
    npm run prod



# ----------------------------------------------------------------------------------------------------
# CLI
# ----------------------------------------------------------------------------------------------------

# For running things like migrations, and queue jobs,
# we need a CLI container.
# It contains all the Composer packages,
# and just the basic CLI "stuff" in order for us to run commands,
# be that queues, migrations, tinker etc.
# We need a stage which contains FPM to actually run and process requests to our PHP application.
FROM 505992365906.dkr.ecr.us-east-1.amazonaws.com/php:2.0.16 as cli

WORKDIR /opt/apps/laravel-in-kubernetes

# Next we have to copy in our code base from our initial build which we installed in the previous stage
COPY --from=composer_base /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes
COPY --from=frontend /opt/apps/laravel-in-kubernetes/public /opt/apps/laravel-in-kubernetes/public


# ----------------------------------------------------------------------------------------------------
# FPM-SERVER
# ----------------------------------------------------------------------------------------------------

# We need a stage which contains FPM to actually run and process requests to our PHP application.
FROM 505992365906.dkr.ecr.us-east-1.amazonaws.com/phpfpm:2.0.16 as fpm_server

WORKDIR /opt/apps/laravel-in-kubernetes

#######

# As FPM uses the www-data user when running our application,
# we need to make sure that we also use that user when starting up,
# so our user "owns" the application when running..
USER  www-data

# COnfiguration of fpm.ini
COPY --chown=www-data docker/php-fpm/php.ini-production /usr/local/etc/php/php.ini

# We have to copy in our code base from our initial build which we installed in the previous stage
COPY --from=composer_base --chown=www-data /opt/apps/laravel-in-kubernetes /opt/apps/laravel-in-kubernetes
COPY --from=frontend --chown=www-data /opt/apps/laravel-in-kubernetes/public /opt/apps/laravel-in-kubernetes/public

# We want to cache the event, routes, and views so we don't try to write them when we are in Kubernetes.
# Docker builds should be as immutable as possible, and this removes a lot of the writing of the live application.
# RUN php artisan event:cache && \
#     php artisan route:cache && \
#     php artisan view:cache


RUN php artisan event:cache && \
    php artisan view:cache

COPY --chown=www-data docker/laravel/oauth/oauth-private.key /opt/apps/laravel-in-kubernetes/storage/oauth-private.key
COPY --chown=www-data docker/laravel/oauth/oauth-public.key /opt/apps/laravel-in-kubernetes/storage/oauth-public.key

RUN chmod -R 660  storage/oauth-private.key && \
    chmod -R 660  storage/oauth-public.key

COPY --chown=www-data ./docker/laravel/entrypoint.sh ./entrypoint.sh

# Dar permisos de ejecución al script de entrada
RUN chmod +x ./entrypoint.sh

# Configurar el comando de entrada
ENTRYPOINT ["./entrypoint.sh"]

# ----------------------------------------------------------------------------------------------------
# NGINX
# ----------------------------------------------------------------------------------------------------

# We need an nginx container which can pass requests to our FPM container,
# as well as serve any static content.
FROM nginx:1.20-alpine as web_server

WORKDIR /opt/apps/laravel-in-kubernetes

# We need to add our NGINX template to the container for startup,
# and configuration.

COPY docker/nginx/nginx.conf.template /etc/nginx/templates/default.conf.template

# Copy in ONLY the public directory of our project.
# This is where all the static assets will live, which nginx will serve for us.
COPY --from=frontend /opt/apps/laravel-in-kubernetes/public /opt/apps/laravel-in-kubernetes/public


# ----------------------------------------------------------------------------------------------------
# CRON
# ----------------------------------------------------------------------------------------------------

# We need a CRON container to the Laravel Scheduler.
# We'll start with the CLI container as our base,
# as we only need to override the CMD which the container starts with to point at cron.
FROM cli as cron

WORKDIR /opt/apps/laravel-in-kubernetes

# We want to create a laravel.cron file with Laravel cron settings, which we can import into crontab,
# and run crond as the primary command in the forground....
RUN touch laravel.cron && \
    echo "* * * * * cd /opt/apps/laravel-in-kubernetes && php artisan schedule:run" >> laravel.cron && \
    crontab laravel.cron

CMD ["crond", "-l", "2", "-f"]
