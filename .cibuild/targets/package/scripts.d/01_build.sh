#!/bin/bash

set -eo pipefail
shopt -s nullglob

make app

for F in *.app; do
	mv -v "${F}" "${CIBUILD_OUTPUTS}/"
done
