# Gunakan image PHP 8.0 dengan Apache
FROM php:8.0-apache

# Set platform untuk kompatibilitas (opsional dan biasanya tidak perlu di Dockerfile)
# ARG PLATFORM=linux/amd64

# Instal ekstensi PHP yang diperlukan
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install pdo pdo_mysql

# Salin kode aplikasi ke dalam container
COPY . /var/www/html/

# Set permission direktori jika diperlukan
RUN chown -R www-data:www-data /var/www/html

# Set environment variables
ENV MYSQL_HOST=db \
    MYSQL_USER=root \
    MYSQL_PASSWORD=password \
    MYSQL_DATABASE=toko_db

# Expose port 80 untuk web server Apache
EXPOSE 80

# Command untuk menjalankan Apache
CMD ["apache2-foreground"]
