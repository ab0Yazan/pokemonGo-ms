FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create a user with the same UID and GID as the host user
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} auth && \
    useradd -u ${USER_ID} -g auth -m auth && \
    usermod -aG www-data auth

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY ../services/auth-service /var/www/html

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R auth:auth /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Switch to the new user
USER auth

# Start PHP-FPM
CMD ["php-fpm"]
