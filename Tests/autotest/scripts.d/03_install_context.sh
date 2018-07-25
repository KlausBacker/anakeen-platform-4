#!/bin/bash

set -eo pipefail

function wiff {
    /var/www/html/dynacase-control/wiff "$@"
}

wiff repository delete --all
wiff repository add --default anakeen_core http://eec.corp.anakeen.com/integration/repo/anakeen/a4/webinst/
wiff repository add --default local        file:///var/www/html/repo
wiff create context dynacase /var/www/html/dynacase
wiff context dynacase repository enable --default

wiff context dynacase module install --unattended user-interfaces
wiff context dynacase module install --unattended user-interfaces-test
