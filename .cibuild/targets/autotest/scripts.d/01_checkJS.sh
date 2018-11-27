#!/bin/bash

set -eo pipefail

yarn install
node ./node_modules/eslint/bin/eslint.js ./
