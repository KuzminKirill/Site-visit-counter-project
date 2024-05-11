FROM php:8.0-cli

WORKDIR /app

# Update package lists
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libhiredis-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install zip pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony CLI tool
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Copy application files
COPY . .

# Install project dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Expose port
EXPOSE 8000

# Start Symfony server
CMD ["symfony", "server:start", "--no-tls", "--port=8000"]
