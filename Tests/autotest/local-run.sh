#!/bin/bash

AUTOTEST_SUBDIR="Tests/autotest"

set -o pipefail

function usage {
	cat <<EOF
Usage
-----

  $0 <docker-image-name> [<git-ref>]

Example
-------

  $0 php71pg96

EOF
}

function main {
	local AUTOTEST_USE=""
	while [ $# -gt 0 ]; do
		case $1 in
			--help)
				usage
				return 1
				;;
			--)
				shift
				break
				;;
			--*)
				echo "Error: unknown option '$1'!" 1>&2
				return 1
				;;
			*)
				break
				;;
		esac
	done

	local IMAGE=$1
	if [ -z "${IMAGE}" ]; then
		usage
		return 1
	fi
	local REF=$2

	local SOURCE=$PWD
	if [ ! -d "${SOURCE}/.git" ]; then
		echo "Error: '.git' directory not found in source directory '${SOURCE}'!" 1>&2
		exit 1
	fi

	export WORK_DIR=$(mktemp -d -t autotest.XXXXXX)
	if [ $? -ne 0 ]; then
		echo "Error: could not create temporary directory!" 1>&2
		exit 1
	fi

	mkdir -p "${SOURCE}/${AUTOTEST_SUBDIR}/outputs"
	if [ $? -ne 0 ]; then
		echo "Error: could not create directory '${SOURCE}/${AUTOTEST_SUBDIR}/outputs'!" 1>&2
		exit 1
	fi

	(
		set -e

		echo "[+] Copying '${SOURCE}' to '${WORK_DIR}'... "
		if [ -n "${REF}" ]; then
			tar -C "${SOURCE}" -cf - . --exclude=./Tests/autotest/outputs | tar -C "${WORK_DIR}" -xf -
			(
				cd "${WORK_DIR}"
				git checkout --force "${REF}"
			)
		else
			tar -C "${SOURCE}" -cf - . --exclude=./Tests/autotest/outputs | tar -C "${WORK_DIR}" -xf -
		fi
		echo "[+] Done."


		local PROPAGATE_SSH_AUTH_SOCK=""
		if [[ -v SSH_AUTH_SOCK && -n "${SSH_AUTH_SOCK}" ]]; then
			PROPAGATE_SSH_AUTH_SOCK="--volume ${SSH_AUTH_SOCK}:/SSH_AUTH_SOCK --env=SSH_AUTH_SOCK=/SSH_AUTH_SOCK"
		fi
		local PROPAGATE_SSH_PRIVATE_KEY=""
		if [[ -v SSH_PRIVATE_KEY && -n "${SSH_PRIVATE_KEY}" ]]; then
			PROPAGATE_SSH_PRIVATE_KEY=$(printf -- "--env=SSH_PRIVATE_KEY=base64:%s" "$(echo "${SSH_PRIVATE_KEY}" | base64 -w0)")
		fi

		echo "[+] Running docker '${IMAGE}'... "
		docker run -it --rm \
			--env="SHARE_USER=$(id -u)" \
			--env="SHARE_GROUP=$(id -g)" \
			${PROPAGATE_SSH_AUTH_SOCK} \
			${PROPAGATE_SSH_PRIVATE_KEY} \
			--volume "${WORK_DIR}:/autotest/work" \
			"${IMAGE}" \
			/autotest/work/${AUTOTEST_SUBDIR}/autotest.sh /autotest/work
		echo "[+] Done."
	) 2>&1 | tee "${SOURCE}/${AUTOTEST_SUBDIR}/outputs/local-run.log"
	RET=$?

	echo "[+] Collecting outputs..."
	tar -C "${WORK_DIR}/outputs" -cf - . | tar -C "${SOURCE}/${AUTOTEST_SUBDIR}/outputs" -xf -
	echo "[+] Done."

	rm -Rf "${WORK_DIR}"

	return $RET
}

main "$@"
