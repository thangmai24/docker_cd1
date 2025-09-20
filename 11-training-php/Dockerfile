FROM php:8.2-apache

# Cài extension MySQL cho PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# Bật mod_rewrite
RUN a2enmod rewrite
