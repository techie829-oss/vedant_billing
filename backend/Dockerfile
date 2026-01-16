FROM php:8.2-fpm

# ===============================
# System dependencies
# ===============================
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    tesseract-ocr \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ===============================
# PHP extensions
# ===============================
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl

# ===============================
# Redis PHP extension
# ===============================
RUN pecl install redis \
    && docker-php-ext-enable redis

# ===============================
# Composer
# ===============================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ===============================
# App directory
# ===============================
WORKDIR /var/www

# ===============================
# Copy application code
# ===============================
COPY . .

# ===============================
# Install Laravel dependencies
# ===============================
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ===============================
# Permissions (Laravel)
# ===============================
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# ===============================
# PHP-FPM
# ===============================
EXPOSE 9000
CMD ["php-fpm"]
