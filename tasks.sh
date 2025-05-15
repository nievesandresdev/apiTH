#!/bin/bash

echo "Ejecutando tareas programadas..."

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php /var/www/html/artisan migrate --force

# Ejecutar seeders sprint 4
echo "Ejecutando seeders..."
#php /var/www/html/artisan db:seed --force

# Cargar lenguages
#php /var/www/html/artisan db:seed --class=LoadLanguagesSeeder

# Cargar las traducciones de todos los hoteles e instalaciones
#php /var/www/html/artisan db:seed --class=TranslateModulesWeabAppSeeder

#seeders de sprint 4 helpers

# Cargar las traducciones de las categorias y subcategorias
#php /var/www/html/artisan db:seed --class=loadIconAndTranslateInTypePlacesAndCategoriPlacesSeeder

# Cargar las traducciones de los places, experiencias y servicios
#php /var/www/html/artisan db:seed --class=TranslateModulesWeabAppSeeder

#Ejecutar custom commmands sprint 4
echo "Ejecutando customs commands..."
# php /var/www/html/artisan custom:command

echo "Todas las tareas programadas se han ejecutado."

#seeders de sprint 5

#agregar traducciones a registros en chat_settings
#php artisan db:seed --class="Database\Seeders\UpdateTranslateModels\UpdateTranslateChatSettingSeeder"

#agregar traducciones a registros en checkin_settings
#php artisan db:seed --class="Database\Seeders\UpdateTranslateModels\UpdateTranslateCheckinSettingSeeder"

#agregar traducciones a registros en query_settings
#php artisan db:seed --class="Database\Seeders\UpdateTranslateModels\UpdateTranslateQuerySettingSeeder"

#agregar traducciones a registros en requests_settings
#php artisan db:seed --class="Database\Seeders\UpdateTranslateModels\UpdateTranslateRequestsSettingSeeder"

#corregir errores de traduccion en el seeder UpdateTranslateRequestsSettingSeeder
#php artisan db:seed --class=FixLinkStringRequestSettingsSeeder

#seeders de sprint 7

#php artisan db:seed --class=AddCodeToUsersSeeder
#php artisan db:seed --class=NotificationsUpdateSeeder
#php artisan db:seed --class=PasswordOtaSeeder
#php artisan db:seed --class=DefaultHotelButtonsSeeder
