# Use PHP 8.2 Apache como imagen base
FROM php:8.2-apache

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias del sistema, incluyendo locales
RUN apt-get update && apt-get install -y \
    locales \
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

# Configurar locales para que el sistema soporte es_ES.UTF-8
RUN echo "es_ES.UTF-8 UTF-8" >> /etc/locale.gen && \
    echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen && \
    update-locale LANG=es_ES.UTF-8

# Establecer variables de entorno para el locale
ENV LANG es_ES.UTF-8
ENV LANGUAGE es_ES:es
ENV LC_ALL es_ES.UTF-8

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
# Ajustar permisos a las carpetas de Laravel y crear archivo de logs
RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache /var/www/html/storage/logs && \
    touch /var/www/html/storage/logs/laravel.log && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader

# Generar key de Laravel y cachear config
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache

# Copiar el archivo de crons y configurar permisos
COPY crontab /etc/cron.d/my-cron-jobs

RUN echo "" >> /etc/cron.d/my-cron-jobs && \
    chmod 0644 /etc/cron.d/my-cron-jobs && \
    chown root:root /etc/cron.d/my-cron-jobs && \
    crontab /etc/cron.d/my-cron-jobs

# Crear el archivo de logs para cron
RUN touch /var/log/cron.log && chmod 0666 /var/log/cron.log

# Configurar Supervisor
RUN mkdir -p /var/log/supervisor /var/run/supervisor && \
    touch /var/log/supervisor/supervisord.log /var/log/supervisor/supervisord.err /var/log/supervisor/laravel-worker.err && \
    chown -R www-data:www-data /var/log/supervisor && \
    chown -R www-data:www-data /var/run/supervisor

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Copiar el archivo de tareas al contenedor
COPY tasks.sh /usr/local/bin/tasks.sh

# Dar permisos de ejecución al archivo
RUN chmod +x /usr/local/bin/tasks.sh

# Exponer el puerto 80
EXPOSE 80

# Ejecutar supervisord para gestionar Apache, cron y workers
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
