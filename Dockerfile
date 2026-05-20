# Use official PHP Apache image
FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Set Apache configuration
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/php-app.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/php-app.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/php-app.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/php-app.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/php-app.conf && \
    a2enconf php-app

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
