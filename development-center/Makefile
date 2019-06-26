#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

MODULE_NAME=development-center
CS_PATH_NAME=./../.devtool/ci/check/checkPHP/anakeenPhpCs.xml
PHP_LIB_PATH=src/vendor/Anakeen/Routes/Devel/Lib

#Control
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=http://localhost:8080/control/
CONTROL_CONTEXT="anakeen-platform"

#Build
APP_OUTPUT_PATH=../build/

##bin
YARN_BIN=yarn
COMPOSER_BIN=composer
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./../.devtool/ci/check/checkPHP/vendor/bin/phpcbf
CS_BIN=php ./../.devtool/ci/check/checkPHP/vendor/bin/phpcbf
CHECK_VERSION=node ./../.devtool/ci/check/checkVersionNpmApp.js

-include ../Makefile.local
########################################################################################################################
##
## Deps
##
########################################################################################################################

install-deps:
	cd $(PHP_LIB_PATH); rm -rf ./vendor; $(COMPOSER_BIN) install
	$(YARN_BIN) install

########################################################################################################################
##
## Static analyze
##
########################################################################################################################

beautify: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify $@${RESET_COLOR}\n"
	$(YARN_BIN) run beautify
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs
	$(CBF_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH} --extensions=php ./src

lint: install-deps checkXML checkVersion
	@${PRINT_COLOR} "${DEBUG_COLOR}lint $@${RESET_COLOR}\n"
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs
	$(CS_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH} --extensions=php ./src

checkXML: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Check XML${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} check

checkVersion:
	@${PRINT_COLOR} "${DEBUG_COLOR}Check version between info and package${RESET_COLOR}\n"
	${CHECK_VERSION} ./

########################################################################################################################
##
## Po and stub
##
########################################################################################################################
po: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Extract PO${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} extractPo

stub: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate Stubs${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} generateStubs

########################################################################################################################
##
## BUILD JS
##
########################################################################################################################

buildJS: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build js $@${RESET_COLOR}\n"
	$(YARN_BIN) buildJs

buildCSS: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build css $@${RESET_COLOR}\n"
	$(YARN_BIN) buildCss


########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps buildJS buildCSS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -t ${APP_OUTPUT_PATH}

app-test:

app-all: app

app-autorelease: install-deps buildJS buildCSS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --auto-release  -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS buildCSS
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-test:

deploy-all: deploy

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean $@${RESET_COLOR}\n"
	rm -fr ./src/public/Anakeen/
	rm -fr ${PHP_LIB_PATH}/vendor
	rm -rf ${MODULE_NAME}*.app
	rm -rf ${MODULE_NAME}*.src

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################

.PHONY: app po deploy install pojs clean cleanAll stub nodePublish

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
