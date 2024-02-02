# Use PHP 8.2 Apache as base image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Docker PHP Extensions Installer script
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions bcmath gd exif pcntl pdo_mysql mbstring zip zlib

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application directory
COPY . /var/www/html

# Install PHP and JS dependencies
USER root

# Set ownership of storage directory
RUN chown -R www-data:www-data storage

RUN php artisan migrate

# Configure php.ini
RUN echo "upload_max_filesize = 500M" >> /usr/local/etc/php/php.ini
RUN echo "post_max_size = 500M" >> /usr/local/etc/php/php.ini

# Configure phpmyadmin-misc.ini
RUN echo "upload_max_filesize = 500M" >> /usr/local/etc/php/conf.d/phpmyadmin-misc.ini
RUN echo "post_max_size = 500M" >> /usr/local/etc/php/conf.d/phpmyadmin-misc.ini

# Copy Apache configuration file
COPY 000-default.conf /etc/apache2/sites-available/

# Enable site
RUN a2ensite 000-default.conf

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
