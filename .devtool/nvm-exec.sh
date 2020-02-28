#!/bin/bash

set -o pipefail  # trace ERR through pipes
set -o errtrace  # trace ERR through 'time command' and other functions
set -o nounset   ## set -u : exit the script if you try to use an uninitialised variable
set -o errexit   ## set -e : exit the script if any statement returns a non-true return value

usage() {
  cat <<'EO_USAGE'
usage: nvm-exec.sh [-p path/to/nvm.sh --] command

Run a node command according to .nvmrc file

Options
  -p  path/to/nvm.sh (default: ~/.nvm/nvm.sh)
      NOTE: if p is specified, it must exist.
            Otherwise, if it is not specified,
            and default value e is not a file,
            current node version will be used.
  -q  disable nvm output

EO_USAGE
}

NVM_PATH=~/.nvm/nvm.sh

while getopts "p:hq" option; do
  case $option in
    p )
      if [ -r "${OPTARG}" ]; then
        NVM_PATH=${OPTARG}
      else
        >&2 echo "${OPTARG} does not exists or is not readable"
        exit 2
      fi
      ;;
    h )
      usage
      exit 0
      ;;
    q )
      NVM_SILENT='--silent'
      ;;
    * )
      usage
      exit 1
      ;;
  esac
done

# remove consumed arguments
shift $((OPTIND - 1))

if [ $# = 0 ]; then
  >&2 echo "command is mandatory"
  exit 2
fi

if [ -r "${NVM_PATH}" ]; then
  . "${NVM_PATH}"
  nvm exec ${NVM_SILENT-} "$@"
else
  "$@"
fi
