#!/bin/bash

echo "Ejecutando tareas programadas..."

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php /var/www/html/artisan migrate --force

# Ejecutar seeders sprint 4
echo "Ejecutando seeders..."
#php /var/www/html/artisan db:seed --force

# Cargar el campo city_id en la tabla hotels
#php /var/www/html/artisan db:seed --class=loadCityObjectidInHotelSeeder

#seeders de sprint 4 helpers

#php /var/www/html/artisan db:seed --class=LoadCityObjectidInProductSeeder
#php /var/www/html/artisan db:seed --class=LoadCityObjectidPlaceSeeder
#php /var/www/html/artisan db:seed --class=assignCityInProductByHosterSeeder

#Ejecutar custom commmands sprint 4
echo "Ejecutando customs commands..."
# php /var/www/html/artisan custom:command

echo "Todas las tareas programadas se han ejecutado."
