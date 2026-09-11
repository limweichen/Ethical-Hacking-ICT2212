# Web container: PHP 8.3 + Apache, with the app code built in.
# EC2 has PHP 8.3.6 (Ubuntu 24.04). 8.3 = same version line, with security fixes.
FROM php:8.3-apache

# PHP extension for MySQL
RUN docker-php-ext-install pdo_mysql

# Production PHP settings (errors hidden from visitors)
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# App code and database schema go INSIDE the image
COPY web/ /var/www/html/
COPY db/ /app/db/
RUN chown -R root:www-data /var/www/html

# On start: create tables, then run Apache
COPY docker/web/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
