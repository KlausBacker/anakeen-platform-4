#!/bin/bash

set -eo pipefail

find . -type f -name "build.json" -exec autorelease {} \;

npm config set @anakeen:registry http://npm.corp.anakeen.com:4873

make app app-test

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo