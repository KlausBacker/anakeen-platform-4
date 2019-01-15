#!/bin/bash

shopt -s globstar
set -e

for x in ./src/**/*.po; do msgfmt --statistics -c -v -o /dev/null "$x"; done