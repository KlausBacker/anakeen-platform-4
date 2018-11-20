#!/bin/bash

set -eo pipefail

npm config set @anakeen:registry http://npm.corp.anakeen.com:4873

if [ -z "${A4PPM_MAKE_TARGET}" ]; then
	A4PPM_MAKE_TARGET="app"
fi

make "${A4PPM_MAKE_TARGET}"

OUTPUT_DIR="${PWD}/outputs"

mv -v *.app "${PWD}/outputs"
