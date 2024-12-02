# Dockerfile

FROM php:8.2-cli

# Install necessary extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy project files
COPY . /app
# Copy .env file
COPY .env /app/.env

# Install composer (optional, if using composer)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Set the command to run when the container starts
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
