#!/bin/bash

export AUTOTEST_HOME=$(dirname "$(realpath "$0")")
export PATH=${AUTOTEST_HOME}/bin:$PATH

export AUTOTEST_CONF
if [ -z "${AUTOTEST_CONF}" ]; then
	AUTOTEST_CONF="${AUTOTEST_HOME}/autotest.json"
fi

set -o pipefail

function main {
	local WORK_DIR=$1
	if [ -z "${WORK_DIR}" ]; then
		WORK_DIR=${PWD}
	fi

	if [ ! -f /.dockerenv ]; then
		echo "Error: you should not manually run this script on your workstation." 2>&2
		echo "This script must be be run inside a docker!" 1>&2
		return 1
	fi

	cd "${WORK_DIR}"
	if [ $? -ne 0 ]; then
		echo "Error: could not change directory to '${WORK_DIR}'!" 1>&2
		return 1
	fi
	mkdir "outputs"

	(
		set -ex
		# Install autotest npm requirements
		(
			cd "${AUTOTEST_HOME}"
			npm --no-progress --no-color install
		)
		# Setup ssh-agent
		if [ -z "${SSH_AUTH_SOCK}" ]; then
			which ssh-agent && eval $(ssh-agent -s)
		fi
		# Run scripts.d
		for SCRIPT in "${AUTOTEST_HOME}"/scripts.d/*; do
			if [ ! -f "${SCRIPT}" ]; then
				continue
			fi
			"${SCRIPT}"
		done
	) 2>&1 | timestamplog
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Error: tests failed!" 1>&2
	fi
	if [ -n "${SHARE_USER}" ]; then
		chown -R "${SHARE_USER}:${SHARE_GROUP}" "${WORK_DIR}"
	fi

	return $RET
}

main "$@"
