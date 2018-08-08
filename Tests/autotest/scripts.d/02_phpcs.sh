#!/bin/bash

cd ./ide
composer install
cd ..
./ide/vendor/bin/phpcs  --standard=./ide/anakeenPhpCs.xml --extensions=php ./src