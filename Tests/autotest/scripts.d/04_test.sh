#!/bin/bash

set -o pipefail

function wiff {
	/var/www/html/dynacase-control/wiff "$@"
}

OUTPUT_DIR="${PWD}/outputs"

(
	RET=0

	set -x

	wiff context dynacase exec /bin/sh -c 'cd vendor/Anakeen/TestUnits && ./lib/vendor/phpunit/phpunit/phpunit --globals-backup --log-junit ../../../phpunit.xml UiTests.php'
	(( RET = RET || $? ))
	
	cp /var/tmp/pudcp.{log,msg} "${OUTPUT_DIR}"
	(( RET = RET || $? ))

	if [ "${AUTOTEST_VARIANT}" = "full" ]; then
		mkdir -p "${OUTPUT_DIR}/wdio.outputs"
		(( RET = RET || $? ))

		npm install
		(( RET = RET || $? ))

		if [ "${AUTOTEST_WDIO_CONFIG}" != "" ]; then
			npx wdio ./Tests/webdriver/${AUTOTEST_WDIO_CONFIG} -b http://localhost/
			(( RET = RET || $? ))
		else
			npx wdio ./Tests/webdriver/wdio.conf.js -b http://localhost/
			(( RET = RET || $? ))
		fi

		cp -pR ./wdio.outputs "${OUTPUT_DIR}/"
		(( RET = RET || $? ))
	fi

	exit $RET
)
RET=$?

if [ -f "/var/www/html/dynacase/phpunit.xml" ]; then
	mv "/var/www/html/dynacase/phpunit.xml" "${OUTPUT_DIR}/phpunit.xml"
	junit2html "${OUTPUT_DIR}/phpunit.xml" "${OUTPUT_DIR}/junitresults.xml.html"
fi

exit $RET
