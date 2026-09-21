#!/bin/bash

# 1. Konfigurasi Nginx root ke public/
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/public;|g' /etc/nginx/sites-available/default
service nginx reload

# 2. Migrasi database dan seeder
php /home/site/wwwroot/artisan migrate --force
php /home/site/wwwroot/artisan db:seed --class=QuizSeeder --force