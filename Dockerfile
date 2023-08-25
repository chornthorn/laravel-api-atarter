FROM php:8.2-fpm

# Install required extensions and dependencies
RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    git \
    curl \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    supervisor \
    libmagickwand-dev --no-install-recommends

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli exif pcntl intl bcmath gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer and Node.js 16
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Override with custom php.ini configuration
COPY ./docker/php-fpm/php-ini-overrides.ini $PHP_INI_DIR/conf.d/99-overrides.ini

# Copy the application files to the container
COPY . /var/www/html

COPY .env.example /var/www/html/.env

WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-dev --prefer-dist && \
    php artisan key:generate && \
    php artisan jwt:secret

# Set necessary permissions on storage/ and bootstrap/ directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install nginx
RUN apt-get update && \
    apt-get install -y nginx

# Remove the default Nginx configuration file
RUN rm /etc/nginx/sites-enabled/default

# Copy our Nginx configuration file
COPY ./docker/nginx/app.conf /etc/nginx/sites-enabled/

# Expose port 80 and start Nginx and PHP-FPM servers
EXPOSE 80

CMD ["/bin/bash", "-c", "php-fpm -D && nginx -g \"daemon off;\""]

