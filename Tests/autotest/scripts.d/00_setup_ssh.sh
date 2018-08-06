#!/bin/bash

set -eo pipefail

function b64value {
	local V=$1
	if [ "${V:0:7}" = "base64:" ]; then
		echo "${V:7}" | base64 -d
	else
		echo "${V}"
	fi
}

function ssh_keyscan_gitlab {
	mkdir -p ~/.ssh
	chmod 700 ~/.ssh
	ssh-keyscan gitlab.anakeen.com >> ~/.ssh/known_hosts
	chmod 644 ~/.ssh/known_hosts
}

function main {
	if [ -n "${SSH_PRIVATE_KEY}" ]; then
		b64value "${SSH_PRIVATE_KEY}" | tr -d '\r' | ssh-add - > /dev/null
	fi
	ssh_keyscan_gitlab
	ssh -T git@gitlab.anakeen.com
}

main "$@"
