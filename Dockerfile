FROM php:8.2-cli

# Install dependency sistem dan ekstensi PostgreSQL & ZIP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Salin semua file proyek
COPY . .

# Install dependency vendor Laravel
RUN composer install --no-dev --optimize-autoloader

# Atur permission folder storage & cache
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 10000

# Jalankan migrasi, seeder kuis, dan nyalakan web server
CMD php artisan migrate --force && php artisan db:seed --class=QuizSeeder --force && php artisan serve --host=0.0.0.0 --port=10000