FROM php:7.2.3

# Essentials
RUN apt-get update && buildDeps="cmake libssl-dev libsasl2-dev libpq-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev openssh-server libxrender1 libfontconfig1 libxext6" && apt-get install -y $buildDeps git nano wget --no-install-recommends
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql zip bcmath gd

# Composer
RUN wget https://getcomposer.org/composer.phar && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer
RUN pecl config-set php_ini "${PHP_INI_DIR}/php.ini"
RUN pecl install mongodb
RUN docker-php-ext-enable mongodb

RUN mkdir /fogger && chmod 777 /fogger
COPY . /app
WORKDIR /app

RUN composer install
RUN sed 's/DC2Type/IGNORE_TYPE/g'  vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/AbstractSchemaManager.php > tmp.php
RUN cp tmp.php vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/AbstractSchemaManager.php

ENTRYPOINT ["php", "bin/console"]
CMD ["--help"]
