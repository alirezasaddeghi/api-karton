FROM debian:bullseye

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive

# Install nginx, PHP and required extensions
RUN apt-get update && apt-get install -y \
    nginx \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-cli \
    php8.2-zip \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-pdo \
    php8.2-common \
    unzip \
    curl \
    supervisor \
    && apt-get clean

# Copy app files
COPY . /var/www/html

# Copy nginx config
COPY ./nginx/default.conf /etc/nginx/sites-available/default

# Configure supervisor to run both nginx and php-fpm
COPY ./supervisord.conf /etc/supervisord.conf

# Expose port
EXPOSE 8080

# Start services
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
