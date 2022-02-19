#!/bin/sh

usermod -u 1000 www-data && groupmod -g 1000 www-data

# delete the "index.html" that installing Apache drops in here
# ルート削除とかやらかすと危ないのでここはハードコーディング
rm -rvf /var/www/html

# Laravelの公開ディレクトリにリンクを貼る
ln -s /var/www/src/public /var/www/html

######################################################
# install composer
######################################################
cd ~/

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

mv composer.phar /usr/local/bin/composer

composer install --no-scripts --no-autoloader --no-dev -d /var/www/src

#chmod 777 /var/www/src/storage/framework/sessions
#chmod 777 /var/www/src/storage/framework/views
#chmod 777 /var/www/src/storage/framework/cache
#chmod 777 /var/www/src/storage/logs
#touch /var/www/src/storage/logs/laravel.log
#chmod 666 /var/www/src/storage/logs/laravel.log

www-data composer dump-autoload

mkdir /var/www/src/storage/framework/cache
mkdir /var/www/src/storage/framework/sessions
mkdir /var/www/src/storage/framework/testing
mkdir /var/www/src/storage/framework/views
cd /var/www/src && php artisan key:generate

chown www-data:www-data -R /var/www/src

# これがないと exit code 0 で起動しないので消さないこと
exec "$@"
