FROM php:8.2-apache
WORKDIR /var/www/html
COPY . /var/www/html/
EXPOSE 80

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite