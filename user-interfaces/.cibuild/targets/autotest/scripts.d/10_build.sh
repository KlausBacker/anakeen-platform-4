#!/bin/bash

set -eo pipefail

make clean
make app-all-autorelease

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo
