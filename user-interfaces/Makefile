#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

## path
STUB_PATH=stubs/
NODE_MODULE_PATH=node_modules
JS_CONF_PATH=./
WEBPACK_CONF_PATH=webpackConfig/
PHP_LIB_PATH=./src/vendor/Anakeen/Ui/PhpLib
JS_ASSET_PATH=./src/public/uiAssets/externals/
JS_COMPONENT_SOURCE_PATH=./src/vendor/Anakeen/Components
JS_COMPONENT_BUILD_PATH=./src/public/Anakeen/ank-components/
JS_DDUI_BUILD_PATH=./src/public/Anakeen/smartElement/
JS_DDUI_SOURCE_PATH=./src/vendor/Anakeen/DOCUMENT/IHM/
JS_ROUTE_SOURCE_PATH=./src/vendor/Anakeen/Routes/Ui
JS_FAMILY_BUILD_PATH=./src/public/Anakeen/smartStructures/
JS_FAMILY_SOURCE_PATH=./src/vendor/Anakeen/SmartStructures/
JS_TEST_BUILD_PATH=Tests/src/public/
JS_TEST_SOURCE_PATH=Tests/src/vendor/Anakeen/
JS_POLYFILL_BUILD_PATH=./src/public/Anakeen/polyfill/
ANAKEEN_UI_SRC_PATH=./
TEST_SRC_PATH=Tests/
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

install-deps:
	cd $(ANAKEEN_UI_SRC_PATH)/src/vendor/Anakeen/Ui/PhpLib; $(COMPOSER_BIN) install
	$(YARN_BIN) install

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
	${CHECK_VERSION} ./ -s ${ANAKEEN_UI_SRC_PATH}
	${ANAKEEN_CLI_BIN} check -s ${TEST_SRC_PATH}

lint: checkXML checkVersion
	@${PRINT_COLOR} "${DEBUG_COLOR}Lint PHP${RESET_COLOR}\n"
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs
	$(CS_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH},${JS_ASSET_PATH} --extensions=php ${ANAKEEN_UI_SRC_PATH}/src

beautify:
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	${COMPOSER_BIN} install --working-dir=./../.devtool/ci/check/checkPHP/ --ignore-platform-reqs
	$(CBF_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH},${JS_ASSET_PATH} --extensions=php  ${ANAKEEN_UI_SRC_PATH}/src

########################################################################################################################
##
## Po and stub
##
########################################################################################################################
po: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Extract PO${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} extractPo --sourcePath ${ANAKEEN_UI_SRC_PATH}

stub: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate Stubs${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} generateStubs -s ${ANAKEEN_UI_SRC_PATH}
	${ANAKEEN_CLI_BIN} generateStubs -s ${TEST_SRC_PATH}

########################################################################################################################
##
## BUILD JS
##
########################################################################################################################

buildJS: install-deps
	$(YARN_BIN) lib
	@${PRINT_COLOR} "${DEBUG_COLOR}Extract component po $@${RESET_COLOR}\n"
	make -f pojs.make compile
	@${PRINT_COLOR} "${DEBUG_COLOR}Build Css $@${RESET_COLOR}\n"
	$(YARN_BIN) buildCss
	@${PRINT_COLOR} "${DEBUG_COLOR}Build asset $@${RESET_COLOR}\n"
	$(YARN_BIN) buildAsset
	@${PRINT_COLOR} "${DEBUG_COLOR}Build smart structures $@${RESET_COLOR}\n"
	$(YARN_BIN) buildPolyfill
	@${PRINT_COLOR} "${DEBUG_COLOR}Build dll $@${RESET_COLOR}\n"
	$(YARN_BIN) buildDll
	@${PRINT_COLOR} "${DEBUG_COLOR}Build smart element $@${RESET_COLOR}\n"
	$(YARN_BIN) buildSmartElement
	@${PRINT_COLOR} "${DEBUG_COLOR}Build ank component $@${RESET_COLOR}\n"
	$(YARN_BIN) buildComponent
	@${PRINT_COLOR} "${DEBUG_COLOR}Build smart structures $@${RESET_COLOR}\n"
	$(YARN_BIN) buildSmartStructures

buildDLL: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build asset $@${RESET_COLOR}\n"
	$(YARN_BIN) buildAsset
	@${PRINT_COLOR} "${DEBUG_COLOR}Build dll $@${RESET_COLOR}\n"
	$(YARN_BIN) buildDll
	@${PRINT_COLOR} "${DEBUG_COLOR}Build ank component $@${RESET_COLOR}\n"
	$(YARN_BIN) buildComponent
	@${PRINT_COLOR} "${DEBUG_COLOR}Build lib $@${RESET_COLOR}\n"
	$(YARN_BIN) lib

buildJS-test: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) buildTest

########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -s ${ANAKEEN_UI_SRC_PATH} -t ${APP_OUTPUT_PATH}

app-test: install-deps buildJS-test
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app test${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -s ${TEST_SRC_PATH} -t ${APP_OUTPUT_PATH}

app-all: app app-test

app-autorelease: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -s ${ANAKEEN_UI_SRC_PATH} --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-test-autorelease: install-deps buildJS-test
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app test autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build -s ${TEST_SRC_PATH} --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease app-test-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ${ANAKEEN_UI_SRC_PATH} 

deploy-test: install-deps buildJS-test
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy test${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ${TEST_SRC_PATH} 

deploy-all: deploy deploy-test

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean $@${RESET_COLOR}\n"
	rm -f *.app
	rm -f *.src
	make -f pojs.make clean

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
