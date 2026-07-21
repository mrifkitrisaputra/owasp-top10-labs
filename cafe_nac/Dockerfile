FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy Apache config
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Add phtml handler for Apache (intentional misconfiguration for CTF)
RUN echo "AddHandler application/x-httpd-php .phtml" >> /etc/apache2/conf-available/phtml.conf \
    && a2enconf phtml

# Copy source files
COPY src/ /var/www/html/

# Copy entrypoint and helper scripts
COPY entrypoint.sh /entrypoint.sh
COPY generate_encrypted.php /generate_encrypted.php
RUN chmod +x /entrypoint.sh

# Create necessary directories
RUN mkdir -p /var/www/html/uploads/articles \
    && mkdir -p /var/www/html/uploads/avatars \
    && mkdir -p /var/www/html/admin/data \
    && mkdir -p /var/www/html/logs \
    && mkdir -p /var/www/html/assets/themes

# Create default theme file (for deserialization)
RUN echo "/* NAC Cafe Default Theme - Coffee Brown */" > /var/www/html/assets/themes/default.css

# Create final flag (outside web root)
RUN echo "pemilik_asli_ahmad_hidayat" > /opt/flag_final.txt \
    && chmod 644 /opt/flag_final.txt

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/uploads

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
