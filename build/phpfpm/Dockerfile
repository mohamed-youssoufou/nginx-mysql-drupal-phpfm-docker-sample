FROM php:7.4-fpm

RUN apt-get update && apt-get install -y --fix-missing \
    # mysql-client \
    imagemagick \
    graphviz \
    git \
    libpng-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libxml2-dev \
    libxslt1-dev \
    wget \
    linux-libc-dev \
    libyaml-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libssl-dev && \
    rm -r /var/lib/apt/lists/*

# RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/include/
  
# docker-php-ext-install \
    # mysqli \
# RUN pdo_mysql \
#     # gd \
#     mbstring \
#     xsl \
#     opcache \
#     calendar \
#     intl \
#     exif \
#     ftp \
#     bcmath \
#     zip

RUN apt-get update && apt-get install -y \
        mariadb-client\
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql opcache

RUN cd /usr/src && \
    curl -sS http://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

ADD xdebug.ini  /etc/php7.3/conf.d/

RUN echo "upload_max_filesize = 500M\n" \
         "post_max_size = 500M\n" \
         > /usr/local/etc/php/conf.d/maxsize.ini

RUN chown -R www-data:www-data $HOME/.composer/

USER www-data
WORKDIR /var/www/html
VOLUME /var/www/html
