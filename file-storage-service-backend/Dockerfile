FROM php:8.1.13-fpm-alpine AS builder

EXPOSE 80

WORKDIR /

RUN apk update \
    && apk add --no-cache \
        ca-certificates \
        curl \
        nginx \
        openssl \
        supervisor \
        tar \
        xz \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        freetype-dev \
        # 若需使用郵件功能請解除下一行的註解
        # imap-dev \
        # 若需使用 SOAP 相關功能請解除下面三行的註解
        # krb5-dev \
        # libressl-dev \
        # libxml2-dev \
        # 若需要啟用 ZIP 功能請解除下一行的註解
        # libzip-dev \
    && rm -rf /var/cache/apk/* \
    # 若需使用郵件功能請解除下一行的註解
    # && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install \
        opcache \
        gd \
        # 若需使用郵件功能請解除下一行的註解
        # imap \
        # 若需要使用 SOAP 相關功能請解除下一行的註解
        # soap \
        # 若需要啟用 ZIP 功能請解除下一行的註解
        # zip \
    && mkdir /var/run/php \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && sed -i "s/expose_php = On/expose_php = Off/" /usr/local/etc/php/php.ini

WORKDIR /usr/share/nginx/html

COPY . .

COPY ./resources/templates/nginx/default.conf /etc/nginx/nginx.conf
COPY ./resources/templates/nginx/nginx-custom.conf /etc/nginx/conf.d/default.conf
COPY ./resources/templates/supervisord/supervisord.conf /etc/supervisord.conf

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer \
    && cp .env.example .env \
    # DO NOT USE `composer update` OR THE AUTOLOAD WILL FAILED
    && composer install --no-dev --no-scripts \
    && composer clear-cache \
    # Remove unnecessary configs
    && sed -i "/TinkerServiceProvider::class/d" ./bootstrap/app.php \
    && php artisan key:generate \
    && php artisan swagger-lume:generate \
    # Remove key generator and development server cmd to avoid security issues
    && rm ./app/Console/Commands/ApplicationKeyGenerator.php \
    && rm ./app/Console/Commands/DevelopmentServer.php \
    # Remove class injection in Kernel to avoid runtime exceptions
    && sed -i "/ApplicationKeyGenerator::class/d" ./app/Console/Kernel.php \
    && sed -i "/DevelopmentServer::class/d" ./app/Console/Kernel.php \
    # Remove unnecessary files
    && rm -rf ./resources/templates \
    && rm -f ./public/web.config \
    && rm -rf ./tests \
    && rm -f .env.example \
    # DO NOT REMOVE NEXT LINE OR THE DEPENDENCY INJECTION WILL FAILED
    && composer dump-autoload \
    # && apk add --no-cache nginx \
    # && apk add --no-cache --virtual temppacks openrc \
    # && openrc \
    # && touch /run/openrc/softlevel \
    # && rc-update add nginx default \
    # && apk del temppacks \
    && rm -rf /var/cache/apk/* \
    && rm -f /usr/local/bin/composer \
    && chown -R $USER:www-data storage \
    && chmod -R 775 storage/app storage/framework storage/logs storage/api-docs

ENTRYPOINT ["supervisord", "-n"]
