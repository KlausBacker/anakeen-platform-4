#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

## path
STUB_PATH=stubs/
LOCALPUB_PATH=$(MK_DIR)/localpub
LOCALPUB_ANAKEEN_UI_PATH=$(LOCALPUB_PATH)/anakeen-ui
LOCALPUB_TEST_PATH=$(LOCALPUB_PATH)/Tests
VERSION_PATH=$(MK_DIR)/VERSION
NODE_MODULE_PATH=node_modules
JS_CONF_PATH=$(MK_DIR)
WEBPACK_CONF_PATH=webpackConfig/
PHP_LIB_PATH=anakeen-ui/src/vendor/Anakeen/Ui/PhpLib
JS_ASSET_PATH=anakeen-ui/src/public/uiAssets/externals/
JS_COMPONENT_SOURCE_PATH=anakeen-ui/src/vendor/Anakeen/Components
JS_COMPONENT_BUILD_PATH=anakeen-ui/src/public/Anakeen/ank-components/
JS_DDUI_BUILD_PATH=anakeen-ui/src/public/Anakeen/smartElement/
JS_DDUI_SOURCE_PATH=anakeen-ui/src/Apps/DOCUMENT/IHM/
JS_ROUTE_SOURCE_PATH=anakeen-ui/src/vendor/Anakeen/Routes/Ui
JS_FAMILY_BUILD_PATH=anakeen-ui/src/public/Anakeen/smartStructures/
JS_FAMILY_SOURCE_PATH=anakeen-ui/src/vendor/Anakeen/SmartStructures/
JS_TEST_BUILD_PATH=Tests/src/public/
JS_TEST_SOURCE_PATH=Tests/src/vendor/Anakeen/
JS_POLYFILL_BUILD_PATH=anakeen-ui/src/public/Anakeen/polyfill/
ANAKEEN_UI_SRC_PATH=anakeen-ui/
TEST_SRC_PATH=Tests/

## Version and release
VERSION=$(shell node -p "require('./package.json').version")

## control conf
port=80
CONTROL_PROTOCOL=http
CONTROL_PORT=$(port)
CONTROL_USER= admin
CONTROL_PASSWORD= anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn
DEVTOOL_BIN=php ./anakeen-devtool.phar
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
COMPOSER_BIN=composer

-include Makefile.local

########################################################################################################################
##
## devtools
##
########################################################################################################################


$(JS_CONF_PATH)/node_modules:
	$(YARN_BIN) install

$(JS_CONF_PATH)/yarn.lock: $(JS_CONF_PATH)/package.json
	$(YARN_BIN) install
	touch "$@"

$(PHP_LIB_PATH)/composer.lock: $(PHP_LIB_PATH)/composer.json
	cd anakeen-ui/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; $(COMPOSER_BIN) install

install: $(JS_CONF_PATH)/yarn.lock $(PHP_LIB_PATH)/composer.lock ## Install deps (js and php)

stub: ## Generate stubs
	${ANAKEEN_CLI_BIN} generateStubs -s anakeen-ui

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################

$(JS_ASSET_PATH): $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/assets.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build asset $@${RESET_COLOR}\n"
	$(YARN_BIN) buildAsset
	touch "$@"

$(JS_DDUI_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_DDUI_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(WEBPACK_CONF_PATH)/smartElement.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build smart element $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) buildSmartElement
	touch "$@"

$(JS_COMPONENT_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_COMPONENT_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(WEBPACK_CONF_PATH)/components.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build ank component $@${RESET_COLOR}\n"
	$(YARN_BIN) buildComponent
	touch "$@"

$(JS_FAMILY_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_FAMILY_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(WEBPACK_CONF_PATH)/smartStructures.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build smart structures $@${RESET_COLOR}\n"
	$(YARN_BIN) buildSmartStructures
	touch "$@"

$(JS_POLYFILL_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/polyfill.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build polyfill $@${RESET_COLOR}\n"
	$(YARN_BIN) buildPolyfill
	touch "$@"


compilation: $(JS_CONF_PATH)/node_modules $(JS_ASSET_PATH) $(JS_POLYFILL_BUILD_PATH) $(JS_COMPONENT_BUILD_PATH) $(JS_DDUI_BUILD_PATH) $(JS_FAMILY_BUILD_PATH)

app: compilation
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --sourcePath ./anakeen-ui

autotest: compilation
	rm -f *app
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./anakeen-ui
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./Tests

deploy: compilation ## deploy the project
	rm -f user-interfaces-1*app
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./anakeen-ui
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(DEVTOOL_BIN) deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w user-interfaces-1*app -- --force
	make clean

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf ${LOCALPUB_PATH}
	rm -f *app
	make -f pojs.make clean

cleanAll: clean ## clean the local pub and the node_module
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf $(NODE_MODULE_PATH)
	touch $(JS_CONF_PATH)/package.json

########################################################################################################################
##
## PO TARGET
##
########################################################################################################################

po:
	${ANAKEEN_CLI_BIN} extractPo --sourcePath ./anakeen-ui

########################################################################################################################
##
## TEST TARGET
##
########################################################################################################################

$(JS_TEST_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_TEST_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/test.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) buildTest
	touch "$@"

compilation-test: $(JS_CONF_PATH)/node_modules $(TEST_SRC_PATH) $(JS_TEST_BUILD_PATH)

app-test: compilation-test ## Build the test package
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -f user-interfaces*test*app
	${ANAKEEN_CLI_BIN} build --sourcePath ./Tests

deploy-test: compilation-test ## Deploy the test package
	rm -f user-interfaces-test*app
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./Tests
	$(DEVTOOL_BIN) deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w user-interfaces-test*app -- --force

########################################################################################################################
##
## Node
##
########################################################################################################################

autorelease:
	@${PRINT_COLOR} "${DEBUG_COLOR}autorelease $@${RESET_COLOR}\n"
	npm version $(shell cat "VERSION")-$(shell date +%s)

nodePublish:
	@${PRINT_COLOR} "${DEBUG_COLOR}nodePublish $@${RESET_COLOR}\n"
	npm publish

autoPublish:
	@${PRINT_COLOR} "${DEBUG_COLOR}$@${RESET_COLOR}\n"
	npm version $(VERSION)-$(shell find . -type f -print0 | xargs -0 stat --format '%Y' | sort -nr | cut -d: -f2- | head -1)
	npm publish || echo "Already published"
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
