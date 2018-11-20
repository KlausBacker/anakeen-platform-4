#!/bin/bash

set -eo pipefail

#register npm
npm config set @anakeen:registry http://npm.corp.anakeen.com:4873

make autotest

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo
