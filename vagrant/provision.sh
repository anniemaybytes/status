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
apt-get -qq -y -o Dpkg::Options::="--force-confnew" install php8.4 php8.4-xdebug php8.4-apcu php8.4-xml php8.4-fpm \
    php8.4-cli php8.4-curl php8.4-mbstring pv curl git unzip zip htop iotop nodejs nginx

echo
echo Setting up packages...
rm -f /etc/php/8.4/cli/conf.d/20-xdebug.ini
rm -rf /etc/nginx/{sites,mods}-enabled
rm -rf /etc/nginx/{sites,mods}-available
rm -rf /etc/nginx/conf.d
cd /vagrantroot/configs
cp -av * /

echo
echo Reconfiguring microarchitecture mitigations...
cat << EOF > /etc/default/grub
GRUB_DEFAULT=0
GRUB_TIMEOUT=5
GRUB_DISTRIBUTOR=`lsb_release -i -s 2> /dev/null || echo Debian`
GRUB_CMDLINE_LINUX_DEFAULT="net.ifnames=0 biosdevname=0 mitigations=off"
GRUB_CMDLINE_LINUX="consoleblank=0"
EOF
update-grub

echo
echo Configuring virtual hosts...
mkdir -p /etc/hosts.d/ && mv /etc/hosts /etc/hosts.d/10-native
echo "$(ip route show default | awk '/default/ {print $3}') animebytes.local " > /etc/hosts.d/99-tentacles
echo "$(ip route show default | awk '/default/ {print $3}') mei.animebytes.local " > /etc/hosts.d/99-mei
echo "$(ip route show default | awk '/default/ {print $3}') irc.animebytes.local " > /etc/hosts.d/99-irc
echo "$(ip route show default | awk '/default/ {print $3}') tracker.animebytes.local " > /etc/hosts.d/99-tracker
cat /etc/hosts.d/* > /etc/hosts

echo
echo Installing composer as /usr/local/bin/composer...
cd /tmp
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

echo
echo Configuring daemons...
systemctl daemon-reload
systemctl disable nginx
systemctl disable php8.4-fpm
systemctl disable webpack

echo
echo Stopping daemons...
systemctl stop nginx
systemctl stop php8.4-fpm
systemctl stop webpack
systemctl stop cron

echo
echo Installing composer packages from lock file...
cd /code
su vagrant -s /bin/bash -c 'composer install'

echo
echo Installing node dependencies
cd /code
corepack enable
su vagrant -s /bin/bash -c 'rm -rf node_modules; pnpm install --frozen-lockfile'

echo
echo Starting webpack watcher
systemctl start webpack

echo
echo Creating required directories
su vagrant -s /bin/bash -c 'mkdir -p /code/logs'

echo
echo Starting daemons...
systemctl start nginx
systemctl start php8.4-fpm
systemctl start cron
