#!/bin/bash
set -e

export DEBIAN_FRONTEND=noninteractive

echo
echo Installing required base components
apt-get update
apt-get -y install apt-transport-https dirmngr curl htop iotop

echo
echo Updating apt cache
apt-get update

echo
echo Updating currently installed packages
apt-get -y dist-upgrade

echo
echo Installing packages...
apt-get -y install software-properties-common nginx php7.3 php7.3-curl php7.3-fpm php7.3-common phpunit pv php7.3-dev php-pear libcurl3-openssl-dev build-essential php7.3-cli git libpq5 libodbc1 unzip zip php7.3-apcu php7.3-json

pecl install xdebug-2.7.2

echo
echo Setting up packages...
# disable default nginx site
rm -f /etc/nginx/sites-enabled/default
rm -rf /etc/nginx/conf.d
cd /vagrantroot/configs
cp -av * /

echo
echo Installing composer as /usr/local/bin/composer...
cd /tmp
curl -s https://getcomposer.org/installer | php
mv ./composer.phar /usr/local/bin/composer
cd /code
composer install

echo
echo Stopping daemons...
systemctl stop nginx
systemctl stop php7.3-fpm

echo
echo Starting daemons...
systemctl daemon-reload
systemctl start nginx
systemctl start php7.3-fpm

echo
echo Creating required directories
sudo -u vagrant mkdir -p /code/logs
