FROM php:8.2-apache

# Instala PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Elimina TODOS los MPM y deja solo prefork
RUN a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

# Configuración de Apache para permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copia el proyecto al directorio raíz de Apache
COPY . /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

EXPOSE 80
CMD ["apache2-foreground"]
