#!/bin/bash

set -o pipefail

function wiff {
	/var/www/html/dynacase-control/wiff "$@"
}

OUTPUT_DIR="${PWD}/outputs"

(
	set -x
	wiff context dynacase exec /bin/sh -c 'cd vendor/Anakeen/TestUnits && phpunit PU_dcp.php --log-junit ../../../phpunit.xml'
)
RET=$?

cp /var/tmp/pudcp.{log,msg} "${OUTPUT_DIR}"

if [ -f "/var/www/html/dynacase/phpunit.xml" ]; then
	mv "/var/www/html/dynacase/phpunit.xml" "${OUTPUT_DIR}/phpunit.xml"
	junit2html "${OUTPUT_DIR}/phpunit.xml" "${OUTPUT_DIR}/junitresults.xml.html"
fi

exit $RET