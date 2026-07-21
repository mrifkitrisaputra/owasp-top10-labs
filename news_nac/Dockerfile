FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install additional tools
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    curl \
    && docker-php-ext-install soap \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Apache configuration - allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy application source
COPY src/ /var/www/html/

# Create necessary directories
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/secrets \
    && mkdir -p /etc/nac \
    && mkdir -p /var/log/nac \
    && mkdir -p /opt/nac

# Copy secret files (flags hidden in filesystem)
COPY secrets/api_credentials.txt /var/www/secrets/api_credentials.txt
COPY secrets/service_token /etc/nac/service_token
COPY secrets/final_directive.txt /opt/nac/final_directive.txt
COPY secrets/admin_actions.log /var/log/nac/admin_actions.log

# Set permissions
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod 755 /var/www/html/uploads \
    && chmod 644 /var/www/secrets/api_credentials.txt \
    && chmod 644 /etc/nac/service_token \
    && chmod 644 /opt/nac/final_directive.txt \
    && chmod 644 /var/log/nac/admin_actions.log

# Copy seed script and entrypoint
COPY seed.php /seed.php
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# PHP configuration for vulnerabilities (intentional misconfigurations)
RUN echo "display_errors = Off" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "allow_url_fopen = On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "allow_url_include = Off" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "file_uploads = On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "libxml.disable_entity_loader = Off" >> /usr/local/etc/php/conf.d/custom.ini

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint.sh"]
