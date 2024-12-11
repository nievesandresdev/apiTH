# Use PHP 8.2 Apache como imagen base
FROM php:8.2-apache

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias del sistema necesarias para Laravel y Imagick
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    default-mysql-client \
    zip \
    vim \
    unzip \
    git \
    curl \
    libmagickwand-dev --no-install-recommends && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP necesarias para Laravel, incluyendo Imagick
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions bcmath gd exif pcntl pdo_mysql mbstring zip soap imagick

# Instalar Composer (administrador de dependencias de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar php.ini
RUN { \
        echo "upload_max_filesize = 500M"; \
        echo "post_max_size = 500M"; \
        echo "memory_limit = 512M"; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# Copiar el archivo de configuración de Apache
COPY 000-default.conf /etc/apache2/sites-available/

# Habilitar el sitio Apache
RUN a2ensite 000-default.conf

# Copiar todos los archivos del proyecto al contenedor
COPY . /var/www/html

# Crear directorios necesarios y dar permisos a las carpetas de Laravel
RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar dependencias de Laravel con Composer
RUN composer install --no-dev --optimize-autoloader

# Crear y establecer la clave de la aplicación
RUN php artisan key:generate

# Cachear configuración de Laravel (opcional, mejora el rendimiento)
RUN php artisan config:cache
RUN php artisan route:cache

# Exponer el puerto 80
EXPOSE 80

# Comando por defecto para iniciar Apache
CMD ["apache2-foreground"]
