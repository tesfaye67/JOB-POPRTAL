FROM php:8.2-apache

# Disable conflicting MPMs
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true

# Enable correct one
RUN a2enmod mpm_prefork

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable rewrite
RUN a2enmod rewrite

# Copy files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html
