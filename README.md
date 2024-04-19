# Gestor de contenidos - Cursalab v2 (Weconnect)

Gestor con funcionalidades trasladadas de UCFP, con Laravel y PHP actualizados.
Se mantiene frontend y backend en el mismo proyecto (gestor)

## Instalación

-   Habilitar extension sodium en php.ini
-   Descargar/instalar/habilitar extensión mongodb (en Windows descargar el ddl, guardarlo en la carpeta ext/ de php, luego incluir extension=php_mongodb.dll en php.ini)
-   Extensión DLL (windows) para php8.1 https://pecl.php.net/package/mongodb/1.13.0/windows
-   Es necesario el paquete consoletvs/charts, chartisan/php ?
-   Para descargar la dependencia Chartisan, es necesario realizar pasar por autenticación con una cuenta de github ?
-   Instalar paquete s3: composer require league/flysystem-aws-s3-v3
-   Ejecutar: php artisan passport:install

## Chagelog

### Agosto 2022


## New tags

git add . && git commit -m "message commit" && git tag -a v2.1.1 -m "message tag" && git push origin main && git push origin main v2.1.1