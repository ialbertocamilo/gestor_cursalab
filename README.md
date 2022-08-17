# Gestor de contenidos - Cursalab v2 (Weconnect)

Gestor con funcionalidades trasladadas de UCFP, con Laravel y PHP actualizados.
Se mantiene frontend y backend en el mismo proyecto (gestor)

## Instalación

-   php 8 versión
-   Habilitar extension sodium en php.ini
-   Descargar/instalar/habilitar extensión mongodb (en Windows descargar el ddl, guardarlo en la carpeta ext/ de php, luego incluir extension=php_mongodb.dll en php.ini) https://stackoverflow.com/questions/65033291/php-7-4-mongodb-dll-file1-9-0-not-compatible-with-php-8-on-windows-10-system
-   Es necesario el paquete consoletvs/charts, chartisan/php ?
-   Para descargar la dependencia Chartisan, es necesario realizar pasar por autenticación con una cuenta de github ?
-   Instalar paquete s3: composer require league/flysystem-aws-s3-v3
