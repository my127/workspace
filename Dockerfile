ARG PHP_VERSION
FROM php:${PHP_VERSION}

RUN apt-get update -qq \
 && DEBIAN_FRONTEND=noninteractive apt-get -s dist-upgrade | grep "^Inst" | \
      grep -i securi | awk -F " " '{print $2}' | \
      xargs apt-get -qq -y --no-install-recommends install \
 \
 # Install base packages \
 && DEBIAN_FRONTEND=noninteractive apt-get -qq -y --no-install-recommends install \
    git \
    libzip-dev \
    libzip4 \
    wget \
    zlib1g-dev \
 \
 # Install php extension deps \
 && docker-php-ext-install zip \
 \
 # Clean the image \
 && apt-get remove -qq -y zlib1g-dev libzip-dev \
 && apt-get auto-remove -qq -y \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* \
 \
 # Create the build user \
 && useradd --create-home --system build \
 \
 # Install box \
 && wget https://github.com/humbug/box/releases/download/3.8.4/box.phar \
 && chmod +x box.phar \
 && mv box.phar /usr/local/bin/box \
 \
 # Install composer for PHP dependencies \
 && wget https://getcomposer.org/installer -O /tmp/composer-setup.php -q \
 && [ "$(wget https://composer.github.io/installer.sig -O - -q)" = "$(sha384sum /tmp/composer-setup.php | awk '{ print $1 }')" ] \
 && php /tmp/composer-setup.php --install-dir='/usr/local/bin/' --filename='composer' --quiet \
 && rm /tmp/composer-setup.php \
 \
 # Create app dir \
 && mkdir /app

WORKDIR /app

RUN composer global require "hirak/prestissimo" --no-interaction --no-ansi --quiet --no-progress --prefer-dist \
 && composer clear-cache --no-ansi --quiet \
 && chmod -R go-w ~/.composer/vendor

COPY ./tools/builder/root/ /

ENTRYPOINT ["/entrypoint.sh"]
CMD ["sleep", "infinity"]
