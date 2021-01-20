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
echo Updating composer from lock file...
cd /code
if composer --version -n | grep "1\."; then
    composer self-update -n --2 # update to composer v2
else
    composer self-update -n
fi
su -s /bin/bash vagrant -c 'composer install'

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
systemctl start php8.0-fpm
systemctl start cron
