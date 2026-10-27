# 1. Gunakan image resmi PHP 8.3 dengan Apache sebagai base image
FROM php:8.3-apache

# 2. Instal library sistem yang dibutuhkan dan aktifkan ekstensi pdo_mysql serta pdo_sqlite
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_sqlite \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 3. Tentukan direktori kerja web server Apache
WORKDIR /var/www/html

# 4. Salin seluruh source code proyek ke direktori web Apache
COPY . /var/www/html/

# 5. Atur kepemilikan dan izin berkas secara aman:
#    - Direktori: 755 (dapat ditelusuri)
#    - Berkas (PHP, CSS, JS): 644 (dapat dibaca tanpa izin eksekusi yang tidak diperlukan)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} +

# 6. Port 80 untuk lalu lintas HTTP Apache
EXPOSE 80

# 7. Jalankan Apache di foreground
CMD ["apache2-foreground"]
