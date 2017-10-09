#!/bin/bash

AUTOTEST_SUBDIR="Tests/autotest"

set -o pipefail

function wiff {
	/var/www/html/dynacase-control/wiff "$@"
}

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

	if [ "${AUTOTEST_OVERRIDE}" = "yes" -a "${AUTOTEST_OVERRIDE_LOADED}" != "yes" -a -x "${AUTOTEST_SUBDIR}/autotest.override.sh" ]; then
		export AUTOTEST_OVERRIDE_LOADED=yes
		exec "${AUTOTEST_SUBDIR}/autotest.override.sh"
	fi

	(
		set -ex

		find . -type f -name "build.json" -exec "${PWD}/${AUTOTEST_SUBDIR}/autorelease" {} \;

		make webinst webinst-test

		mv *.webinst /var/www/html/repo

		"${AUTOTEST_SUBDIR}/mkwebinstrepo" /var/www/html/repo

		service apache2 restart
		service postgresql restart

		wiff repository delete --all
		wiff repository add --default anakeen_core http://eec.corp.anakeen.com/integration/repo/anakeen/a4/webinst/
		wiff repository add --default local        file:///var/www/html/repo
		wiff create context dynacase /var/www/html/dynacase
		wiff context dynacase repository enable --default

		wiff context dynacase module install --unattended anakeen-core
		wiff context dynacase module install --unattended anakeen-core-test
		wiff context dynacase module install --unattended anakeen-legacy
	)
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Error: installation failed!" 1>&2
		return $RET
	fi
	
	wiff context dynacase exec /bin/sh -c 'cd vendor/Anakeen/TestUnits && phpunit PU_dcp.php'
	RET=$?
	if [ $RET -ne 0 ]; then
		cp /var/tmp/pudcp.{log,msg} /autotest/share/
		echo "Error: phpunit failed!" 1>&2
		return $RET
	fi
}

main "$@"
