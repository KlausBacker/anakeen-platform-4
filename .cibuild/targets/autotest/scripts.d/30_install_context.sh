#!/bin/bash

set -eo pipefail

function wiff {
    /var/www/html/dynacase-control/wiff "$@"
}


echo "Init repo"
wiff repository delete --all
if [[ $CI_MERGE_REQUEST_TARGET_BRANCH_NAME =~ -stable$ ]]; then
    echo "Repository stable $CIBUILD_STABLE_REPO_AUTOTEST"
    wiff repository add --default anakeen_core $CIBUILD_STABLE_REPO_AUTOTEST
else
    echo "Repository integration $CIBUILD_INTEGRATION_REPO_AUTOTEST"
    wiff repository add --default anakeen_core $CIBUILD_INTEGRATION_REPO_AUTOTEST
fi
echo "Init repo"
wiff repository add --default local        file:///var/www/html/repo
echo "Init context"
wiff create context dynacase /var/www/html/dynacase
wiff context dynacase repository enable --default

echo "Install app"
wiff context dynacase module install --unattended user-interfaces
wiff context dynacase module install --unattended user-interfaces-test
