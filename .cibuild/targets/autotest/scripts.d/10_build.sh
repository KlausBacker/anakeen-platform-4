#!/bin/bash

set -eo pipefail

npm config set @anakeen:registry http://npm.corp.anakeen.com:4873

yarn run build