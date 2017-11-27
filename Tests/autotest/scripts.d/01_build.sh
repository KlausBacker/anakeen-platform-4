#!/bin/bash

set -eo pipefail

find . -type f -name "build.json" -exec autorelease {} \;

make webinst webinst-test

mv -v *.webinst /var/www/html/repo

mkwebinstrepo /var/www/html/repo
