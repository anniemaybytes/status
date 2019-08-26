#!/bin/bash
echo
echo Copying over configs...
sudo cp -avu /vagrantroot/configs/* /s

echo
echo Stopping daemons...
sudo systemctl stop nginx
sudo systemctl stop php7.3-fpm

echo
echo Starting daemons...
systemctl daemon-reload
sudo systemctl start nginx
sudo systemctl start php7.3-fpm
