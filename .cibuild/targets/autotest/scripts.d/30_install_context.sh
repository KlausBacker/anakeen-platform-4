#!/bin/bash

set -eo pipefail

function wiff {
    /var/www/html/dynacase-control/wiff "$@"
}

wiff repository delete --all
wiff repository add --default anakeen_core http://eec.corp.anakeen.com/integration/repo/anakeen/a4/webinst/
wiff repository add --default local        file:///var/www/html/repo
wiff create context anakeen /var/www/html/dynacase
wiff context anakeen repository enable --default

wiff context anakeen module install --unattended anakeen-hub
