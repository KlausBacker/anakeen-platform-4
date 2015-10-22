#!/bin/bash
# vim: tabstop=8 softtabstop=4 shiftwidth=4 noexpandtab

MY_DIR=$(cd "$(dirname "$0")" && pwd)
if [ -z "$MY_DIR" ]; then
    echo "Error: could not get directory of '$0'!"
    exit 1
fi

function usage {
    cat <<EOF
Usage
-----

    $0 <context-url> [<local-root>]

EOF
}

function genKarmaSpecificConf {
    cat <<EOF
{
    "proxies": {
	"/dynacase//api/v1/" :  "${CONTEXT_URL}/TEST_DOCUMENT/FALSE_API/",
	"/dynacase/": "${CONTEXT_URL}/",
	"/resizeimg.php": "${CONTEXT_URL}/resizeimg.php",
	"/lib/": "${CONTEXT_URL}/lib/",
	"/css/": "${CONTEXT_URL}/css/",
	"/FDL/": "${CONTEXT_URL}/FDL/",
	"/file/" : "${CONTEXT_URL}/TEST_DOCUMENT/FALSE_API/"
    },
    "files": [
	"test-main.js",
	{"pattern": "test-css.js", "watched": false, "served": true, "included": false},
	{"pattern": "${LOCAL_ROOT}DOCUMENT/IHM/widgets/attributes/**/test*.js", "included": false},
	{"pattern": "${LOCAL_ROOT}DOCUMENT/IHM/test/test*.js", "included": false}
    ],
    "reporters": [${REPORTERS}],
    "junitReporter": {
	"outputDir": "${MY_DIR}",
	"suite": ""
    }
}
EOF
}

function main {
    CONTEXT_URL=${1%/}
    if [ -z "$CONTEXT_URL" ]; then
	echo "Error: missing context's base url argument!"
	usage
	return 1
    fi
    LOCAL_ROOT=$2
    if [ -z "$LOCAL_ROOT" ]; then
	LOCAL_ROOT="${MY_DIR}/../../"
    fi
    if [ ! -d "${LOCAL_ROOT}/DOCUMENT" ]; then
	echo "Error: missing subdir 'DOCUMENT' in local root '${LOCAL_ROOT}'!"
	usage
	return 1
    fi
    REPORTERS='"junit"'
    if [ -t 1 ]; then
	# Add progress reporter only if running with an interactive tty
	REPORTERS=$REPORTERS',"progress"'
    fi

    cd "$MY_DIR"
    if [ $? -ne 0 ]; then
	echo "Error: could not chdir to '${MY_DIR}'!"
	return 1
    fi

    genKarmaSpecificConf > "karma.specific.conf.json"
    if [ $? -ne 0 ]; then
	echo "Error: eror generating '${MY_DIR}/karma.specific.conf.js'!"
	return 1
    fi

    npm install
    if [ $? -ne 0 ]; then
	echo "Error: error installing npm modules!"
	return 1
    fi

    ./node_modules/karma/bin/karma start
}


main "$@"
