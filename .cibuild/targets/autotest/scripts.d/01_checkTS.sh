#!/bin/bash

set -eo pipefail

yarn install
npx tslint './src/**/*.ts'
npx prettier --check './src/**/*.ts'
