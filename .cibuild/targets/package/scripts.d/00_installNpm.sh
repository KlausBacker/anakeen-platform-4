#!/usr/bin/env bash

#register npm
npm config set @anakeen:registry "${CIBUILD_STABLE_ANAKEEN_NPM_REGISTRY}"
npx npm-cli-login -u ${CIBUILD_STABLE_ANAKEEN_NPM_LOGIN} -p ${CIBUILD_STABLE_ANAKEEN_NPM_PASSWORD} -e ${CIBUILD_ANAKEEN_NPM_EMAIL} -r ${CIBUILD_STABLE_ANAKEEN_NPM_REGISTRY} -s "@anakeen"
yarn install