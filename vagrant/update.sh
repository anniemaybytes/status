#!/bin/bash
set -e

if [ "$EUID" -ne 0 ]; then
    echo "Must be run as root"
    exit
fi

export DEBIAN_FRONTEND=noninteractive

echo
echo Copying over configs...
cd /vagrantroot/configs
cp -avu * /

echo
echo Updating packages...
apt-get update --allow-releaseinfo-change
find /etc/apt/sources.list.d -name "*.list" -type f -exec \
    apt-get -qq -y \
    -o Dpkg::Options::="--force-confold" \
    -o Dir::Etc::sourcelist="{}" \
    -o Dir::Etc::sourceparts="-" \
    -o APT::Get::List-Cleanup="0" \
    dist-upgrade \; # https://github.com/oerdnj/deb.sury.org/issues/1682
apt-get -qq -y -o Dpkg::Options::="--force-confold" --only-upgrade install php8.2* nodejs
apt-get -y autoremove && apt-get -y autoclean

echo
echo Configuring daemons...
systemctl daemon-reload
systemctl disable nginx
systemctl disable php8.2-fpm
systemctl disable webpack

echo
echo Stopping daemons...
systemctl stop nginx
systemctl stop php8.2-fpm
systemctl stop webpack
systemctl stop cron

echo
echo Updating composer from lock file...
cd /code
COMPOSER_ALLOW_SUPERUSER=1 composer self-update -n
su vagrant -s /bin/bash -c 'composer install'

echo
echo Installing node dependencies...
cd /code
npm i -g yarn
su vagrant -s /bin/bash -c 'yarn install --no-bin-links --frozen-lockfile'

echo
echo Starting webpack watcher...
systemctl start webpack

echo
echo Starting daemons...
systemctl start nginx
systemctl start php8.2-fpm
systemctl start cron
