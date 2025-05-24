FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Copy app files
COPY . /var/www/html

# Install any required extensions (optional)
RUN docker-php-ext-install pdo pdo_mysql
