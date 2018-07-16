#!/bin/bash

set -eo pipefail

npm install eslint eslint-plugin-prettier prettier
node ./node_modules/eslint/bin/eslint.js ./
