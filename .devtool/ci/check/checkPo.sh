#!/bin/bash

shopt -s globstar
set -e

#Smart data engine
echo "Check Po : Smart Data Engine"
for x in ./smart-data-engine/src/locale/**/*.po; do msgfmt --statistics -c -v -o /dev/null "$x"; done
echo "Check Po : security"
for x in ./security/src/locale/**/*.po; do msgfmt --statistics -c -v -o /dev/null "$x"; done
echo "Check Po : workflow"
for x in ./workflow/src/locale/**/*.po; do msgfmt --statistics -c -v -o /dev/null "$x"; done
echo "Check Po : user-interfaces"
for x in ./user-interfaces/src/locale/**/*.po; do msgfmt --statistics -c -v -o /dev/null "$x"; done
echo "Check Po : admin-center"
for x in ./admin-center/src/locale/**/*.po; do msgfmt --statistics -c -v -o /dev/null "$x"; done