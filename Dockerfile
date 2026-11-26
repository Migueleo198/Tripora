# Tripora: Apache + PHP 8.0 (la misma versión que XAMPP)
FROM php:8.0-apache

# Extensión para conectar con MariaDB por PDO y módulos de Apache que usan los .htaccess
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers

# Solo la carpeta public es visible desde el navegador (igual que el .htaccess de la raíz en XAMPP)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Zona horaria de España para las fechas de PHP
RUN echo "date.timezone=Europe/Madrid" > /usr/local/etc/php/conf.d/zona.ini

COPY . /var/www/html/

# Apache (www-data) tiene que poder escribir las imágenes subidas y el log de correos
RUN mkdir -p /var/www/html/App/logs /var/www/html/public/img/viajes /var/www/html/public/img/hoteles \
    && chown -R www-data:www-data /var/www/html/App/logs /var/www/html/public/img

EXPOSE 80
