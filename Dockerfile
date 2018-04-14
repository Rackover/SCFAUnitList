FROM richarvey/nginx-php-fpm:1.4.1

LABEL maintainer="rackover@racknet.noip.me"

COPY . /var/www/html