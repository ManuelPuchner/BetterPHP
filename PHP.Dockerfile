FROM php:8.2-apache

RUN a2enmod rewrite


COPY ./src /var/www/html
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html

# suppress php warnings
RUN echo "error_reporting = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini



# add pgsql pdo
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

RUN sed -i "s/Listen 80/Listen ${PORT:-80}/g" /etc/apache2/ports.conf && \
  sed -i "s/:80/:${PORT:-80}/g" /etc/apache2/sites-enabled/*

EXPOSE 80

CMD ["apache2-foreground"]