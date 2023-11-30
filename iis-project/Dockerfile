# Use an official PHP runtime as a parent image
FROM php:7.4-apache

# Set the working directory to /var/www/html/WWW
WORKDIR /var/www/html/WWW

# Install required extensions
RUN docker-php-ext-install pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy only specific files
COPY WWW/index.php /var/www/html/WWW/
COPY .htaccess /var/www/html/WWW/
# Add any other necessary files

# Ensure that Apache has the right permissions
RUN chown -R www-data:www-data /var/www/html/WWW

# Expose port 80 to the outside world
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
