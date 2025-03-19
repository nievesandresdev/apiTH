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

# Cargar las traducciones de todos los hoteles e instalaciones
#php /var/www/html/artisan db:seed --class=TranslateModulesWeabAppSeeder

#seeders de sprint 4 helpers

# Cargar las traducciones de los places, experiencias y servicios
#php /var/www/html/artisan db:seed --class=TranslateModulesWeabAppSeeder

# Cargar el campo city_id en la tabla products
#php /var/www/html/artisan db:seed --class=LoadCityObjectidInProductSeeder

# Cargar el campo city_id en la tabla places
#php /var/www/html/artisan db:seed --class=LoadCityObjectidPlaceSeeder

# Cargar el campo city_id los products que fueron creados por un hoster
#php /var/www/html/artisan db:seed --class=assignCityInProductByHosterSeeder

#Ejecutar custom commmands sprint 4
echo "Ejecutando customs commands..."
# php /var/www/html/artisan custom:command

echo "Todas las tareas programadas se han ejecutado."
