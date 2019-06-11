#!/bin/bash

set -eo pipefail

yarn install
npx eslint ./
