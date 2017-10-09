#!/bin/bash

AUTOTEST_SUBDIR="Tests/autotest"

function usage {
	cat <<EOF
Usage
-----

  $0 [--autotest-override] <docker-image-name> [<git-ref> [<git-source-dir>]]

  Default <git-ref> is 'HEAD'.
  Default <git-source-dir> is '.' (current working directory).

Example
-------

  $0 php71pg96 HEAD ~/src/anakeen-core

EOF
}

function main {
	while [ $# -gt 0 ]; do
		case $1 in
			--help)
				usage
				return 1
				;;
			--autotest-override)
				export AUTOTEST_OVERRIDE=yes
				shift
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
	if [ -z "${REF}" ]; then
		REF="HEAD"
	fi
	local SOURCE=$3
	if [ -z "${SOURCE}" ]; then
		SOURCE=.
	fi

	SOURCE=$(realpath "$SOURCE")
	if [ ! -d "${SOURCE}/.git" ]; then
		echo "Error: '.git' directory not found in source directory '${SOURCE}'!" 1>&2
		exit 1
	fi

	if [ "${AUTOTEST_OVERRIDE}" = "yes" -a ! -f "${SOURCE}/${AUTOTEST_SUBDIR}/autotest.override.sh" ]; then
		echo "Error: missing '${SOURCE}/${AUTOTEST_SUBDIR}/autotest.override.sh' file required by '--autotest-override'!" 1>&2
		return 1
	fi

	export WORK_DIR=$(mktemp -d -t autotest.XXXXXX)
	if [ $? -ne 0 ]; then
		echo "Error: could not create temporary directory!" 1>&2
		exit 1
	fi

	(
		set -e

		echo "[+] Copying '${SOURCE}:${REF}' to '${WORK_DIR}'... "
		GIT_DIR="${SOURCE}/.git" git archive --format=tar "${REF}" | tar -C "${WORK_DIR}" -xf -
		echo "[+] Done."

		if [ "${AUTOTEST_OVERRIDE}" = "yes" -a -f "${SOURCE}/${AUTOTEST_SUBDIR}/autotest.override.sh" ]; then
			echo "[+] Copying '${AUTOTEST_SUBDIR}/autotest.override.sh' to '${WORK_DIR}/${AUTOTEST_SUBDIR}/autotest.override.sh'... "
			cp -p "${SOURCE}/${AUTOTEST_SUBDIR}/autotest.override.sh" "${WORK_DIR}/${AUTOTEST_SUBDIR}/"
			echo "[+] Done."
		fi

		echo "[+] Running docker '${IMAGE}'... "
		mkdir -p "${SOURCE}/${AUTOTEST_SUBDIR}/share"
		docker run -it --rm \
			--env="AUTOTEST_OVERRIDE=${AUTOTEST_OVERRIDE}" \
			--volume "${WORK_DIR}:/autotest/work" \
			--volume "${SOURCE}/${AUTOTEST_SUBDIR}/share:/autotest/share" \
			"${IMAGE}" \
			/autotest/work/${AUTOTEST_SUBDIR}/autotest.sh /autotest/work
		echo "[+] Done."
	) 2>&1 | tee "${SOURCE}/${AUTOTEST_SUBDIR}/share/local-run.log"
	RET=$?

	rm -Rf "${WORK_DIR}"

	return $RET
}

main "$@"
