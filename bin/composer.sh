#!/usr/bin/env bash

if [[ -f 'composer.phar' ]]; then
    php composer.phar self-update
else
    wget https://getcomposer.org/installer -O composer-setup.php
    php composer-setup.php
    rm -f composer-setup.php
fi

