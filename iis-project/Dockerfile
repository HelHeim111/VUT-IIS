# Use an official PHP runtime as a parent image
FROM php:8.1-apache

# Set the working directory to /var/www/html/WWW
WORKDIR /var/www/html/WWW

# Install required extensions
RUN docker-php-ext-install pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy only specific files
COPY WWW/index.php ./
COPY .htaccess ./

# Remove the existing default Apache configuration file
RUN rm /etc/apache2/sites-available/000-default.conf

# Copy your custom Apache configuration file into the image
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Ensure that Apache has the right permissions
RUN chown -R www-data:www-data ./
RUN 

# Expose port 80 to the outside world
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]