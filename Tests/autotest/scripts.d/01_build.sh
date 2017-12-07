#!/bin/bash

set -eo pipefail

find . -type f -name "build.json" -exec autorelease {} \;

make app app-test

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo
