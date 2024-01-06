# Mad Fénix Api

Api para el estudio Mad Fénix.

## Instalación

Este proyecto está hecho con Laravel 10.10 por lo que es necesario seguir sus requerimientos para hacerlo funcionar:

https://laravel.com/docs/10.x

Antes de instalar el proyecto es recomendable seguir las siguientes instucciones:

* Modificar parámetros del repositorio en el composer.json
```
"name": "vgrdominik/madfenix-api",
"type": "project",
"description": "Api del estudio Mad Fénix.",
"keywords": [
    "estudio",
    "madfenix",
    "vgrdominik",
    "valentigamez"
],
```
* Crear el fichero .env del proyecto apartir del -env.example. Si algunos valores no se sabe cómo ponerlos, en https://laravel.com/docs/10.x se puede encontrar como crear un proyecto desde 0 y allí hay datos de ejemplo.

Instalación:

```
composer install
php artisan migrate
php artisan config:cache
```

## Patrocinadores

<p align="center"><img src="https://www.ciclotic.com/images/logo.png?1" width="200"></p>

## Vulnerabilidades de seguridad

Si se descubre algún fallo de seguridad se puede enviar a iam@valentigamez.com con el asunto "Vulnerabilidad madFenixApi". Valentí Gàmez, el líder del proyecto, se compromete a responder y tratar cualquier situación constructiva.

## Licencia

MIT.
