FROM php:8.1-fpm

COPY ./docker/php/node/php.ini /usr/local/etc/php/php.ini

RUN apt-get update && apt-get install -y libcurl4-gnutls-dev git zip \
	&& docker-php-ext-install curl bcmath sockets pdo_mysql \
	&& apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN curl --silent --show-error https://getcomposer.org/installer | php \
	&& mkdir -p /usr/local/bin \
	&& mv composer.phar /usr/local/bin/composer \
	&& chmod +x  /usr/local/bin/composer

WORKDIR /var/www/html

RUN mkdir -p /home/composer && chmod 777 /home/composer

COPY ./docker/php/node/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
