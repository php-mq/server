#!/usr/bin/env bash

sudo apt-get update

# link the uploaded nginx config to enable it
echo -e "\e[0m--"
rm -rf /etc/nginx/sites-enabled/*
for vhost in dev pma readis; do
    sudo ln -sf /vagrant/env/nginx.$vhost.conf /etc/nginx/sites-enabled/020-$vhost
    sudo test -L /etc/nginx/sites-enabled/020-$vhost && echo -e "\e[0mLinking nginx $vhost config: \e[1;32mOK\e[0m" || echo -e "Linking nginx $vhost config: \e[1;31mFAILED\e[0m";
done

cd /vagrant

echo -e "\e[0m--"
sudo composer self-update
sudo chmod -R 0777 /home/vagrant/.composer
sudo chmod -R 0777 /tmp
sudo service nginx restart
sudo service php7.1-fpm restart
