FROM php:8.2-fpm

# Install system dependencies and nginx
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Copy application files
COPY . /var/www/html

# Nginx config
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf

RUN rm -f index.nginx-debian.html

# Supervisor config
COPY ./supervisord.conf /etc/supervisord.conf

# Expose port
EXPOSE 80

# Start supervisor (runs nginx + php-fpm)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
