FROM php:8.0 as php

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath

RUN pecl install mongodb && docker-php-ext-enable mongodb

RUN apt-get install -y libpng-dev
RUN docker-php-ext-install gd

WORKDIR /var/www
COPY . .

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

ENV PORT=8000
# Copiar el script de entrada
COPY entrypoint.sh /entrypoint.sh

# Dar permisos de ejecución al script de entrada
RUN chmod +x /entrypoint.sh

# Configurar el comando de entrada
ENTRYPOINT ["/entrypoint.sh"]
