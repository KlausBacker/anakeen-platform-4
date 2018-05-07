#!/bin/bash

set -eo pipefail

function wiff {
    /var/www/html/dynacase-control/wiff "$@"
}

wiff repository delete --all
wiff repository add --default anakeen_core http://eec-integration.corp.anakeen.com/anakeen/repo/4.0/webinst/
wiff repository add --default local        file:///var/www/html/repo
wiff create context dynacase /var/www/html/dynacase
wiff context dynacase repository enable --default

wiff context dynacase module install --unattended smart-data-engine-test
