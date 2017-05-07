#!/usr/bin/env bash

sudo apt-get update

cd /vagrant

echo -e "\e[0m--"
sudo composer self-update
sudo chmod -R 0777 /home/vagrant/.composer
sudo chmod -R 0777 /tmp
sudo service php7.1-fpm restart
