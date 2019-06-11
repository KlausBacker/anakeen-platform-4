#!/bin/bash

set -eo pipefail

yarn install
npx tslint './components/**/*.ts'
npx prettier --check './components/**/*.ts'
