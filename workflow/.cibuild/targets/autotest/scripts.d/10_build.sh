#!/bin/bash

set -eo pipefail

#register npm
npm config set @anakeen:registry http://npm.corp.anakeen.com:4873

make clean
make app-all-autorelease

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo
