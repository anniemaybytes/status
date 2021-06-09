#!/bin/bash
set -e

if [ "$EUID" -ne 0 ]; then
    echo "Must be run as root"
    exit
fi

export DEBIAN_FRONTEND=noninteractive

echo Configuring system...
DPKG_MAINTSCRIPT_NAME=postinst DPKG_MAINTSCRIPT_PACKAGE=grub-pc upgrade-from-grub-legacy # bug: system assumes /dev/vda but that is not necessarily valid anymore
apt-mark hold linux-image-amd64 # bug: vboxsf component are not updated
apt-mark hold grub-pc # bug: attempt to run grub-pc updater in noninteractive mode will fail

echo
echo Installing required base components...
apt-get update
apt-get -qq -y install apt-transport-https dirmngr curl

echo
echo Adding repositories...
cd /vagrantroot/configs/etc/apt
cp -avu * /etc/apt/
curl -sSL https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add -
curl -sSL https://packages.sury.org/php/apt.gpg | apt-key add -

echo
echo Updating apt cache...
apt-get update

echo
echo Updating currently installed packages...
apt-get -qq -y -o Dpkg::Options::="--force-confnew" upgrade

echo
echo Cleaning up stale packages and files...
apt-get -y autoremove && apt-get -y autoclean

echo
echo Installing packages...
apt-get -qq -y -o Dpkg::Options::="--force-confold" install php8.0 php8.0-xdebug php8.0-apcu php8.0-xml php8.0-fpm \
    php8.0-cli php8.0-curl php8.0-mbstring pv git unzip zip htop iotop nodejs nginx

echo
echo Setting up packages...
rm -f /etc/php/8.0/cli/conf.d/20-xdebug.ini
rm -rf /etc/nginx/{sites,mods}-enabled
rm -rf /etc/nginx/{sites,mods}-available
rm -rf /etc/nginx/conf.d
cd /vagrantroot/configs
cp -av * /

echo
echo Installing composer as /usr/local/bin/composer...
cd /tmp
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo
echo Configuring daemons...
systemctl daemon-reload
systemctl disable nginx
systemctl disable php8.0-fpm
systemctl disable webpack

echo
echo Stopping daemons...
systemctl stop nginx
systemctl stop php8.0-fpm
systemctl stop webpack
systemctl stop cron

echo
echo Installing composer packages from lock file...
cd /code
su -s /bin/bash vagrant -c 'composer install'

echo
echo Installing node dependencies
npm i -g yarn
su vagrant -s /bin/bash -c 'yarn install --no-bin-links --frozen-lockfile'

echo
echo Starting webpack watcher
systemctl start webpack

echo
echo Creating required directories
su -s /bin/bash vagrant -c 'mkdir -p /code/logs'

echo
echo Starting daemons...
systemctl start nginx
systemctl start php8.0-fpm
systemctl start cron
