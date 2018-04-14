FROM richarvey/nginx-php-fpm:1.4.1

LABEL maintainer="rackover@racknet.noip.me"

COPY IMG /usr/share/nginx/html/IMG
COPY RES /usr/share/nginx/html/RES
COPY DATA /usr/share/nginx/html/DATA
COPY CONFIG /usr/share/nginx/html/CONFIG
COPY index.php /usr/share/nginx/html/index.php 
COPY api.php /usr/share/nginx/html/api.php 
COPY update.php /usr/share/nginx/html/update.php 
COPY unit.php /usr/share/nginx/html/unit.php 
COPY LICENSE /usr/share/nginx/html/LICENSE
COPY favicon.ico /usr/share/nginx/html/favicon.ico