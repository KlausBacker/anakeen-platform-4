#!/bin/bash

set -eo pipefail

if [ -n "${A4PPM_UPLOAD_SSH_PRIVATE_KEY}" ]; then
	eval "$(decode_base64_env_var A4PPM_UPLOAD_SSH_PRIVATE_KEY)"
fi

if [ -n "${A4PPM_UPLOAD_SSH_PRIVATE_KEY}" ]; then
	echo "${A4PPM_UPLOAD_SSH_PRIVATE_KEY}" | ssh-add -
fi
