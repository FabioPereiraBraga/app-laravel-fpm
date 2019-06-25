FROM  php:7.3.6-fpm-alpine3.9
RUN apk add bash mysql-client
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/
RUN rm -rf /var/www/html

ENV DOCKERIZE_VERSION v0.6.1
ENV COMPOSER_PROCESS_TIMEOUT=900  

RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz

COPY . /var/www/
COPY .env.example .env

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN ln -s public html

EXPOSE 9000