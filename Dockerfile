FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

# Install necessary dependencies
RUN apt update -y \
    && apt install -y vim software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt install -y php7.4-fpm php7.4-common php7.4-dom php7.4-intl php7.4-mysql php7.4-xml php7.4-xmlrpc php7.4-curl php7.4-gd php7.4-imagick php7.4-cli php7.4-dev php7.4-imap php7.4-mbstring php7.4-soap php7.4-zip php7.4-bcmath php7.4-pdo nginx unzip \
    && rm /etc/nginx/sites-available/*

COPY default /etc/nginx/sites-available/

# Download and install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Clean temporary
RUN php -r "unlink('composer-setup.php');"

# RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
#     && php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
#     && php composer-setup.php \
#     && php -r "unlink('composer-setup.php');" \
#     && mv composer.phar /usr/local/bin/composer

COPY . /var/www/html/

WORKDIR /var/www/html/

RUN composer install \
    && php artisan key:generate \
    && chmod -R 777 /var/www/html

# Install supervisord
RUN apt install -y supervisor

# Copiar el archivo de configuración de supervisord
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord"]

