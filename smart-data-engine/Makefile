#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
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

-include ../Makefile.local
########################################################################################################################
##
## Deps
##
########################################################################################################################

node_modules:
	${YARN_BIN} install

src/vendor/Anakeen/XmlSchemas: node_modules
	rm -fr $@
	cp -r ../node_modules/@anakeen/anakeen-module-validation/xsd $@

src/vendor/Anakeen/lib/vendor:
	cd src/vendor/Anakeen/lib; ${COMPOSER_BIN} install --ignore-platform-reqs

Tests/src/vendor/Anakeen/TestUnits/lib/vendor:
	cd Tests/src/vendor/Anakeen/TestUnits/lib; ${COMPOSER_BIN} install --ignore-platform-reqs

./../.devtool/ci/check/checkPHP/vendor:
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs


install-deps: node_modules src/vendor/Anakeen/XmlSchemas src/vendor/Anakeen/lib/vendor Tests/src/vendor/Anakeen/TestUnits/lib/vendor
	@${PRINT_COLOR} "${DEBUG_COLOR}Install deps${RESET_COLOR}\n"
########################################################################################################################
##
## Static analyze
##
########################################################################################################################

checkXML: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Check XML${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} check
	${ANAKEEN_CLI_BIN} check -s ./Tests

checkVersion:
	@${PRINT_COLOR} "${DEBUG_COLOR}Check version between info and package${RESET_COLOR}\n"
	${CHECK_VERSION} ./

lint: checkXML checkVersion ./../.devtool/ci/check/checkPHP/vendor
	@${PRINT_COLOR} "${DEBUG_COLOR}Lint PHP${RESET_COLOR}\n"
	$(CS_BIN) --standard=${CS_PATH_NAME} --extensions=php --ignore=./src/vendor/Anakeen/lib,src/vendor/Anakeen/Core/SmartStructure/NormalAttribute.php ./src


beautify: ./../.devtool/ci/check/checkPHP/vendor
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	$(CBF_BIN) --standard=${CS_PATH_NAME} --extensions=php --ignore=./src/vendor/Anakeen/lib,src/vendor/Anakeen/Core/SmartStructure/NormalAttribute.php ./src
	$(CBF_BIN) --standard=${CS_PATH_NAME}  --ignore=Tests/src/vendor/Anakeen/TestUnits/lib/vendor/,Tests/src/Apps/FDL --extensions=php ./Tests/src

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
## Build
##
########################################################################################################################
app: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -t ${APP_OUTPUT_PATH}

app-test: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app test${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -s ./Tests -t ${APP_OUTPUT_PATH}

app-all: app app-test

app-autorelease: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-test-autorelease: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app test autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -s ./Tests --autoRelease="dev"  -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease app-test-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . 

deploy-test: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy test${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./Tests 

deploy-all: deploy deploy-test

########################################################################################################################
##
## Clean
##
########################################################################################################################
clean:
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean${RESET_COLOR}\n"
	rm -fr node_modules
	rm -f *.src
	rm -f *.app

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################
.PHONY: app app-test app-all app-autorelease app-test-autorelease app-all-autorelease deploy deploy-test deploy-all

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