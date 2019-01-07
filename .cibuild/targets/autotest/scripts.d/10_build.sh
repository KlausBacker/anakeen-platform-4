#!/bin/bash

set -eo pipefail

make app-all-autorelease

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo
