FROM php:7.2.3

# Essentials
RUN apt-get update && buildDeps="libpq-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev openssh-server libxrender1 libfontconfig1 libxext6" && apt-get install -y $buildDeps git nano wget --no-install-recommends
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql zip bcmath gd

# Composer
RUN wget https://getcomposer.org/composer.phar && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer


RUN mkdir /fogger && chmod 777 /fogger
COPY . /app
WORKDIR /app

#RUN composer install --no-dev
RUN composer install

ENTRYPOINT ["php", "bin/console"]
CMD ["--help"]
