#!/bin/bash

set -e

function main {
  local WORK_DIR
  WORK_DIR=$(realpath -e "$(dirname "${BASH_SOURCE[0]}")")
  cd "${WORK_DIR}"

  if [ ! -f "DocumentRoot/index.html" ]; then
    echo "Error: missing 'DocumentRoot/index.html' (you might need to run './web-build.sh' first)!" 1>&2
    return 1
  fi
  if [ -z "${SCHEMA_DEPLOY_HOST}" ]; then
    echo "Error: missing or empty 'SCHEMA_DEPLOY_HOST'!" 1>&2
    return 1
  fi
  if [ -z "${SCHEMA_DEPLOY_USER}" ]; then
    echo "Error: missing or empty 'SCHEMA_DEPLOY_USER'!" 1>&2
    return 1
  fi
  if [ -z "${SCHEMA_DEPLOY_PATH}" ]; then
    echo "Error: missing or empty 'SCHEMA_DEPLOY_PATH'!" 1>&2
    return 1
  fi
  if [ -z "${SCHEMA_DEPLOY_SSH_PRIVATE_KEY}" ]; then
    echo "Error: missing or undefined 'SCHEMA_DEPLOY_SSH_PRIVATE_KEY'!" 1>&2
    return 1
  fi

  rsync -az --delete "DocumentRoot/" "${SCHEMA_DEPLOY_USER}@${SCHEMA_DEPLOY_HOST}:${SCHEMA_DEPLOY_PATH}/"
}

main "$@"

# vim: sw=2 sts=2 et
