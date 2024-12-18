# Use PHP 8.2 Apache como imagen base
FROM php:8.2-apache

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias del sistema necesarias
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

# Copiar configuración de Supervisor
# Este archivo principal incluirá los programas
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Copiar configuración del worker de Laravel (queue)
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Configurar tus crons
# Ejemplo: llama a artisan cada día a las 4am para alguna tarea
# Ajusta la ruta a artisan y el comando, usuario y horario según tu necesidad
COPY crontab /etc/cron.d/my-cron-jobs
RUN chmod 0644 /etc/cron.d/my-cron-jobs && crontab /etc/cron.d/my-cron-jobs

# Exponer el puerto 80
EXPOSE 80

# Iniciar supervisor que a su vez iniciará Apache, cron y el worker
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
