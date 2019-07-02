#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
CS_PATH_NAME=./../.devtool/ci/check/checkPHP/anakeenPhpCs.xml

#Control
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=http://localhost:8080/control/
CONTROL_CONTEXT="anakeen-platform"

#Build
APP_OUTPUT_PATH=../build/

##bin
YARN_BIN=yarn
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./../.devtool/ci/check/checkPHP/vendor/bin/phpcbf
CS_BIN=php ./../.devtool/ci/check/checkPHP/vendor/bin/phpcbf
COMPOSER_BIN=composer
CHECK_VERSION=node ./../.devtool/ci/check/checkVersionNpmApp.js

-include ../Makefile.local
########################################################################################################################
##
## Deps
##
########################################################################################################################
install-deps:
	$(YARN_BIN) install

########################################################################################################################
##
## Po and stub
##
########################################################################################################################
checkXML: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Check XML${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} check

checkVersion:
	@${PRINT_COLOR} "${DEBUG_COLOR}Check version between info and package${RESET_COLOR}\n"
	${CHECK_VERSION} ./

po: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Extract PO${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} extractPo

stubs: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate Stubs${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} generateStubs

########################################################################################################################
##
## build and deploy
##
########################################################################################################################
buildJS: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build JS $@${RESET_COLOR}\n"
	$(YARN_BIN) buildJs

app:
	${ANAKEEN_CLI_BIN} build -t ${APP_OUTPUT_PATH}

app-test:

app-all: app

app-autorelease: buildJS
	rm -f *app
	${ANAKEEN_CLI_BIN} build --auto-release -t ${APP_OUTPUT_PATH}

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}


deploy-test:

deploy-all:deploy

########################################################################################################################
##
## Beautify
##
########################################################################################################################

beautify:
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs
	$(CBF_BIN) --standard=${CS_PATH_NAME} --extensions=php ./src

lint: checkVersion checkXML
	@${PRINT_COLOR} "${DEBUG_COLOR}Lint PHP${RESET_COLOR}\n"
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs
	$(CS_BIN) --standard=${CS_PATH_NAME} --extensions=php ./src

########################################################################################################################
##
## Clean
##
########################################################################################################################
clean:
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean${RESET_COLOR}\n"
	rm -f *.src
	rm -f *.app

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################

.PHONY: app po deploy install app-test deploy-test pojs clean cleanAll stub

PRINT_COLOR = printf
SUCCESS_COLOR = \033[1;32m
DEBUG_COLOR = \033[36m
HINT_COLOR = \033[33m
WARNING_COLOR = \033[31m
RESET_COLOR = \033[0m

.DEFAULT_GOAL := help

help: HELP_WIDTH = 25
help: ## Show this help message
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-${HELP_WIDTH}s\033[0m %s\n", $$1, $$2}'
