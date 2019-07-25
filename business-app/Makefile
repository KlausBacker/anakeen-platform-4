#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
MODULE_NAME=anakeen-hub-business-app
CS_PATH_NAME=./../.devtool/ci/check/checkPHP/anakeenPhpCs.xml


#Build
APP_OUTPUT_PATH=../build/

##bin
YARN_BIN=yarn
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./../.devtool/ci/check/checkPHP/vendor/bin/phpcbf
CS_BIN=php ./../.devtool/ci/check/checkPHP/vendor/bin/phpcbf
COMPOSER_BIN=composer
CHECK_VERSION=node ./../.devtool/ci/check/checkVersionNpmApp.js

-include Makefile.local
########################################################################################################################
##
## Deps
##
########################################################################################################################

node_modules:
	${YARN_BIN} install

install-deps: node_modules
	@${PRINT_COLOR} "${DEBUG_COLOR}Install deps${RESET_COLOR}\n"

########################################################################################################################
##
## Static analyze
##
########################################################################################################################

checkXML: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Check XML${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} check

checkVersion:
	@${PRINT_COLOR} "${DEBUG_COLOR}Check version between info and package${RESET_COLOR}\n"
	${CHECK_VERSION} ./

lint: checkXML checkVersion ./../.devtool/ci/check/checkPHP/vendor
	@${PRINT_COLOR} "${DEBUG_COLOR}Lint PHP${RESET_COLOR}\n"
	$(CS_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH} --extensions=php ./src

beautify: ./../.devtool/ci/check/checkPHP/vendor
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	$(CBF_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH} --extensions=php ./src

checkPackage:
	${MK_DIR}/.cibuild/targets/autotest/scripts.d/01_checkPackage.sh


########################################################################################################################
##
## Po and stub
##
########################################################################################################################
po: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Extract PO${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} extractPo

stubs: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate Stubs${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} generateStubs

########################################################################################################################
##
## BUILD JS
##
########################################################################################################################

buildJS: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build JS $@${RESET_COLOR}\n"
	$(YARN_BIN) buildJsRender
	$(YARN_BIN) buildJs

########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -t ${APP_OUTPUT_PATH}

app-test:
app-test-autorelease:

app-all: app

app-autorelease: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . 

deploy-test:

deploy-all: deploy

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean $@${RESET_COLOR}\n"
	rm -fr src/public/Anakeen/businessApp src/public/Anakeen/manifest
	rm -fr node_modules
	rm -rf ${MODULE_NAME}*.app
	rm -rf ${MODULE_NAME}*.src

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################

.PHONY: app po deploy install pojs clean cleanAll stub

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
