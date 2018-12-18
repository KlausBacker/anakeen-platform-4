#!/bin/bash

set -eo pipefail

make autotest

mv -v *.app /var/www/html/repo

mkwebinstrepo /var/www/html/repo
