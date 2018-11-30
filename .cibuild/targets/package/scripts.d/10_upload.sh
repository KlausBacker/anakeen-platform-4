#!/bin/bash

set -eo pipefail

if [ -n "${A4PPM_UPLOAD_GLOB}" -a -n "${A4PPM_UPLOAD_SCP}" ]; then
	scp -o StrictHostKeyChecking=no "${PWD}"/outputs/${A4PPM_UPLOAD_GLOB} "${A4PPM_UPLOAD_SCP}"
fi
