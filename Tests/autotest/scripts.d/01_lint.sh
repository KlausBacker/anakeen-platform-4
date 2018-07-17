#!/bin/bash

set -eo pipefail

npm install
node ./node_modules/eslint/bin/eslint.js ./
