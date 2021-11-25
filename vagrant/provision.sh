#!/bin/bash
set -e

if [ "$EUID" -ne 0 ]; then
    echo "Must be run as root"
    exit
fi

export DEBIAN_FRONTEND=noninteractive

echo Configuring system...
apt-mark hold linux-image-amd64 grub-pc # do not bloat image with new kernel
rm -rf /var/log/journal && systemctl restart systemd-journald

echo
echo Adding repositories...
cd /vagrantroot/configs/etc/apt
cp -avu * /etc/apt/

echo
echo Installing packages...
apt-get update
find /etc/apt/sources.list.d -name "*.list" -type f -exec \
    apt-get -qq -y \
    -o Dpkg::Options::="--force-confnew" \
    -o Dir::Etc::sourcelist="{}" \
    -o Dir::Etc::sourceparts="-" \
    -o APT::Get::List-Cleanup="0" \
    dist-upgrade \; # https://github.com/oerdnj/deb.sury.org/issues/1682
apt-get -qq -y -o Dpkg::Options::="--force-confnew" install php8.0 php8.0-xdebug php8.0-apcu php8.0-xml php8.0-fpm \
    php8.0-cli php8.0-curl php8.0-mbstring pv curl git unzip zip htop iotop nodejs nginx

echo
echo Setting up packages...
rm -f /etc/php/8.0/cli/conf.d/20-xdebug.ini
rm -rf /etc/nginx/{sites,mods}-enabled
rm -rf /etc/nginx/{sites,mods}-available
rm -rf /etc/nginx/conf.d
cd /vagrantroot/configs
cp -av * /
update-grub

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
su vagrant -s /bin/bash -c 'composer install'

echo
echo Installing node dependencies
npm i -g yarn
su vagrant -s /bin/bash -c 'yarn install --no-bin-links --frozen-lockfile'

echo
echo Starting webpack watcher
systemctl start webpack

echo
echo Creating required directories
su vagrant -s /bin/bash -c 'mkdir -p /code/logs'

echo
echo Starting daemons...
systemctl start nginx
systemctl start php8.0-fpm
systemctl start cron
