# Railway detecta este Dockerfile automáticamente.
# Usa la imagen oficial de PHP 8.2 con Apache.
FROM php:8.2-apache

# Instala la extensión de MySQL para PDO
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite para el .htaccess
RUN a2enmod rewrite

# Copia todo el proyecto dentro del contenedor
COPY . /var/www/html/

# Da permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Permite .htaccess en el directorio raíz
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80
