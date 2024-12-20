#!/bin/bash

echo "Ejecutando tareas programadas..."

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php /var/www/html/artisan migrate --force

# Ejecutar seeders
echo "Ejecutando seeders..."
#php /var/www/html/artisan db:seed --force

echo "Ejecutando customs commands..."
# php /var/www/html/artisan custom:command

echo "Todas las tareas programadas se han ejecutado."
