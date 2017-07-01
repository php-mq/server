#!/usr/bin/env bash

sudo apt-get update

cd /vagrant

mkdir build/logs
chmod -R 0777 build/logs

sudo composer self-update
sudo chmod -R 0777 /home/vagrant/.composer
sudo chmod -R 0777 /tmp
sudo service php7.1-fpm restart
