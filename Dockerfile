# Use PHP 8.2 Apache como imagen base
FROM php:8.2-apache

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias del sistema
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
    libmagickwand-dev \
    supervisor \
    cron \
    --no-install-recommends && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions bcmath gd exif pcntl pdo_mysql mbstring zip soap imagick

# Instalar Composer
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
RUN a2ensite 000-default.conf

# Copiar la aplicación Laravel
COPY . /var/www/html

# Ajustar permisos a las carpetas de Laravel
RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader

# Generar key de Laravel y cachear config
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache

# Configurar crons
COPY crontab /etc/cron.d/my-cron-jobs
RUN chmod 0644 /etc/cron.d/my-cron-jobs && chown root:root /etc/cron.d/my-cron-jobs

# Configurar Supervisor
RUN mkdir -p /var/log/supervisor && chown -R www-data:www-data /var/log/supervisor
RUN mkdir -p /var/run/supervisor && chown -R www-data:www-data /var/run/supervisor
RUN mkdir -p /etc/supervisor/conf.d /var/run/supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
RUN chmod 644 /etc/supervisor/supervisord.conf /etc/supervisor/conf.d/laravel-worker.conf


# Exponer el puerto 80
EXPOSE 80

# No especificamos CMD porque lo definiremos en docker-compose para cada servicio
