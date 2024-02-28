# Use PHP 8.2 Apache as base image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies required at runtime
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    vim \
    unzip \
    git \
    curl && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Docker PHP Extensions Installer script
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install PHP extensions required at runtime
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions bcmath gd exif pcntl pdo_mysql mbstring zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure php.ini
RUN { \
        echo "upload_max_filesize = 500M"; \
        echo "post_max_size = 500M"; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# Copy Apache configuration file
COPY 000-default.conf /etc/apache2/sites-available/

# Enable Apache site
RUN a2ensite 000-default.conf

# Change ownership of our applications
# Nota: Asegúrate de que el directorio /var/www/html ya contenga los datos necesarios antes de cambiar de propietario
# Esto puede requerir ajustar tu proceso de CI/CD o de despliegue para pre-cargar los archivos necesarios
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
