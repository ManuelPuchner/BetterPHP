FROM php:8.1-apache

ARG DB_HOST
ARG DB_NAME
ARG DB_USER
ARG DB_PW
ARG DB_PORT

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

COPY ./src /var/www/public
RUN echo "DB_HOST=${DB_HOST}" >> /var/www/.env && \
    echo "DB_NAME=${DB_NAME}" >> /var/www/.env && \
    echo "DB_USER=${DB_USER}" >> /var/www/.env && \
    echo "DB_PW=${DB_PW}" >> /var/www/.env && \
    echo "DB_PORT=${DB_PORT}" >> /var/www/.env

RUN chown -R www-data:www-data /var/www

RUN sed -i "s/Listen 80/Listen ${PORT:-80}/g" /etc/apache2/ports.conf && \
  sed -i "s/:80/:${PORT:-80}/g" /etc/apache2/sites-enabled/*

EXPOSE 80

CMD ["apache2-foreground"]