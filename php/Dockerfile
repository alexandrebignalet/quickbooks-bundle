FROM php:5.6-apache

# Environmental Variables
ENV PHP_MEMORY_LIMIT 1024M
ENV PHP_DATE_TIMEZONE UTC
ENV SYMFONY_VERSION latest

RUN apt-get update && \
        apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libbz2-dev \
        php-pear \
        curl \
        zlib1g-dev \
        libicu-dev \
        && rm -r /var/lib/apt/lists/*

# PHP ext
RUN docker-php-ext-install mcrypt zip bz2 mbstring \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo_mysql opcache

RUN apt-get update \
  && apt-get install -y php5-xdebug

# Memory Limit
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory_limit.ini

# Time Zone
RUN echo "date.timezone=${PHP_DATE_TIMEZONE}" > /etc/php5/cli/conf.d/date_timezone.ini
RUN echo "date.timezone=${PHP_DATE_TIMEZONE}" > /usr/local/etc/php/conf.d/date_timezone.ini


# Copy the local.ini
COPY config/local.ini /usr/local/etc/php/conf.d/

COPY config/xdebug.ini /usr/local/etc/php/conf.d/

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Quickbooks SDK stuff
RUN cd /usr/local && \
    curl -OL https://pecl.php.net/get/oauth-1.2.3.tgz && \
    tar xvzf oauth-1.2.3.tgz && \
    cd oauth-1.2.3 && \
    /usr/local/bin/phpize && \
    ./configure && \
    make && \
    make test && \
    make install

RUN echo "extension_dir =/usr/lib64/php/modules/" > /etc/php5/cli/conf.d/quickbooks_sdk.ini
RUN echo "extension=oauth.so" >> /usr/local/etc/php/conf.d/quickbooks_sdk.ini && service apache2 restart

# Install composer
RUN curl -k -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install symfony tool
RUN curl -LsS http://symfony.com/installer -o /usr/local/bin/symfony \
    && chmod a+x /usr/local/bin/symfony

# Add the vhost config
ADD config/symfony.dev.conf /etc/apache2/sites-enabled/
