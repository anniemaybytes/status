#!/bin/bash
set -e

if [ "$EUID" -ne 0 ]; then
    echo "Must be run as root"
    exit
fi

export DEBIAN_FRONTEND=noninteractive

echo
echo Installing required base components
apt-get update
apt-get -y install apt-transport-https dirmngr curl htop iotop

echo
echo Adding repositories
cd /vagrantroot/configs/etc/apt
cp -avu * /etc/apt/
curl -sSL https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add -
curl -sSL https://packages.sury.org/php/apt.gpg | apt-key add -

echo
echo Updating apt cache
apt-get update

echo
echo Updating currently installed packages
apt-get -y -o Dpkg::Options::="--force-confnew" dist-upgrade

echo
echo Installing packages...
apt-get -y -o Dpkg::Options::="--force-confold" install software-properties-common nginx php7.4 php7.4-curl php7.4-fpm \
    php7.4-common phpunit pv php7.4-dev php-pear libcurl3-openssl-dev build-essential php7.4-cli git libpq5 libodbc1 \
    unzip zip php7.4-apcu php7.4-json php7.4-mbstring php7.4-xml nodejs

pecl install xdebug-2.9.6

echo
echo Setting up packages...
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
echo Configuring daemons...
systemctl daemon-reload
systemctl disable nginx
systemctl disable php7.4-fpm
systemctl disable webpack

echo
echo Stopping daemons...
systemctl stop nginx
systemctl stop php7.4-fpm
systemctl stop webpack
systemctl stop cron

echo
echo Starting daemons...
systemctl start nginx
systemctl start php7.4-fpm
systemctl start cron

echo
echo Installing node dependencies
if ! hash yarn 2>/dev/null; then
    npm install -g yarn
fi
su vagrant -s /bin/bash -c 'yarn install --no-bin-links --frozen-lockfile'

echo
echo Starting webpack watcher
systemctl start webpack

echo
echo Creating required directories
su -s /bin/bash vagrant -c 'mkdir -p /code/logs'
