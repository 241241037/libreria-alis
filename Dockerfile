FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysql \
    libapache2-mod-php8.1 \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite php8.1

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
