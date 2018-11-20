#!/bin/bash

set -eo pipefail

#register npm
npm config set @anakeen:registry http://npm.corp.anakeen.com:4873

make app
