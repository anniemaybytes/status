#!/bin/bash
set -e

export DEBIAN_FRONTEND=noninteractive

echo
echo Copying over configs...
cd /vagrantroot/configs
cp -av * /

echo
echo Stopping daemons...
systemctl stop nginx
systemctl stop php7.3-fpm

echo
echo Starting daemons...
systemctl daemon-reload
systemctl start nginx
systemctl start php7.3-fpm

cd /code

echo
echo Updating composer from lock file
sudo -u vagrant composer install
