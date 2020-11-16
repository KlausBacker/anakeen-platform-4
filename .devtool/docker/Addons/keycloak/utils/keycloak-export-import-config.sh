#!/bin/bash

function usage() {
  cat <<EOF
Usage
-----

  $0 export /path/to/exportDir
  $0 import /path/to/importDir

EOF
}

function keycloak-export() {
  /opt/jboss/tools/docker-entrypoint.sh \
    -Djboss.socket.binding.port-offset=100 \
    -Dkeycloak.migration.action=export \
    -Dkeycloak.migration.provider=dir \
    -Dkeycloak.migration.dir="$1" \
    ;
}

function keycloak-import() {
  /opt/jboss/tools/docker-entrypoint.sh \
    -Djboss.socket.binding.port-offset=100 \
    -Dkeycloak.migration.action=import \
    -Dkeycloak.migration.provider=dir \
    -Dkeycloak.migration.dir="$1" \
    -Dkeycloak.migration.strategy=OVERWRITE_EXISTING \
    ;
}

function wait-finish() {
  timeout 90s awk '{print};/(Export|Import) finished successfully/{exit}'
}

function main() {
  if [ "${DEBUG}" = "yes" ]; then
    set -x
  fi

  local OP=""
  local DIR=""
  if [ $# -ne 2 ]; then
    usage
    exit 1
  fi
  case "$1" in
  export | import)
    OP=$1
    shift
    ;;
  *)
    echo "Error: unknown operation '$1'!" 1>&2
    ;;
  esac
  DIR=$1
  if [ ! -d "${DIR}" ]; then
    echo "Error: not a directory '${DIR}'!" 1>&2
    exit 1
  fi

  if ! TMPLOG=$(mktemp -t "keycloak-export-import-config.log.XXXXXX"); then
    echo "Error: could not create temporary file!" 1>&2
    exit 1
  fi

  if [ "${OP}" = "export" ]; then
    keycloak-export "${DIR}" </dev/null >"${TMPLOG}" 2>&1 &
  else
    keycloak-import "${DIR}" </dev/null >"${TMPLOG}" 2>&1 &
  fi

  tail -f -n +1 "${TMPLOG}" | wait-finish

  sleep 3

  kill -TERM %1
}

main "$@"
