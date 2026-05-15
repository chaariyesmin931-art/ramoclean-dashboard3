FROM php:8.2-apache

# Install dependencies required for the MongoDB extension
RUN apt-get update && apt-get install -y \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Install the MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install mysqli just in case for unmigrated files (though it will error without a DB)
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all project files into the Apache document root
COPY . /var/www/html/

# Set proper permissions for Apache
RUN chown -R www-data:www-data /var/www/html/
