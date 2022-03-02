FROM ubuntu:20.04

# avoidance for against blocking installing by git
ENV DEBIAN_FRONTEND=noninteractive

# ENVs
ENV APACHE_CONFDIR /etc/apache2
ENV APACHE_ENVVARS ${APACHE_CONFDIR}/envvars

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR "/var/log/apache2"

# to chk command with `php -i | grep 'php.ini'`
#ENV PHP_INI_DIR /usr/local/etc/php
ENV PHP_INI_DIR /etc/php/74/cli/

USER root

RUN mkdir -p "$PHP_INI_DIR/conf.d"

##############################################################
#
# apt-get and install
# 
##############################################################
RUN apt-get update
RUN apt-get install --no-install-recommends -y \
    apt-transport-https \
    build-essential \
    curl \
    libcurl4-openssl-dev \
    ca-certificates \
    gcc \
    git \
    make \
    vim \
    gnupg2 \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-dev \
    libpng-dev \
    libpq-dev \
    locales \
    ssl-cert \
    unzip \    
    phpunit \
    zlib1g-dev \
;

RUN apt-get update
RUN apt-get install --no-install-recommends -y apache2

RUN apt-get install --no-install-recommends -y \
    php7.4 \
    php7.4-mysqli \
    php7.4-pgsql \
    php7.4-phar \
    php7.4-mbstring \
    php7.4-ctype \
    php7.4-xm \
    php7.4-zip \
    php7.4-cURL \
    php7.4-fileinfo \
    php7.4-intl \
    php7.4-opcache \
    php7.4-apcu \
    php7.4-xdebug \
    php7.4-gd \
    php7.4-imagick \
;

RUN apt-get -y clean \
    && rm -rf /var/lib/apt/lists/* \
    && echo "ja_JP.UTF-8 UTF-8" >/etc/locale.gen \
    && locale-gen \
;

######################################################
#
# Apache settings
#
######################################################
# logs should go to stdout / stderr
RUN ln -sfT /dev/stderr "${APACHE_LOG_DIR}/error.log"
RUN ln -sfT /dev/stdout "${APACHE_LOG_DIR}/access.log"
RUN ln -sfT /dev/stdout "${APACHE_LOG_DIR}/other_vhosts_access.log"

RUN chown -R --no-dereference "${APACHE_RUN_USER}:${APACHE_RUN_GROUP}" "${APACHE_LOG_DIR}"

# Apache + PHP requires preforking Apache for best results
RUN a2dismod mpm_event && a2enmod mpm_prefork;

# PHP files should be handled by PHP, and should be preferred over any other file type
RUN { \
    echo '<FilesMatch \.php$>'; \
    echo '\tSetHandler application/x-httpd-php'; \
    echo '</FilesMatch>'; \
    echo; \
    echo 'DirectoryIndex disabled'; \
    echo 'DirectoryIndex index.php index.html'; \
    echo; \
    echo '<Directory /var/www/html/>'; \
    echo '\tOptions -Indexes'; \
    echo '\tAllowOverride All'; \
    echo '</Directory>'; \
} | tee "${APACHE_CONFDIR}/conf-available/docker-php.conf" \
;

RUN a2enmod rewrite headers ssl
RUN a2enconf docker-php 

# Enable SSL
RUN ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf

EXPOSE 80
EXPOSE 443

# 初回起動時のみ実行されるスクリプト
COPY ./script/entrypoint.sh /tmp

RUN chmod 755 /tmp/entrypoint.sh
ENTRYPOINT ["/tmp/entrypoint.sh"]

# コンテナ起動時に実行されるコマンド。※これはこのDockerfile内で1行だけ有効なので注意
CMD ["apachectl", "-D", "FOREGROUND"]