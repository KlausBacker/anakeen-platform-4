#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

## path
STUB_PATH=stubs/
LOCALPUB_PATH=$(MK_DIR)/localpub
LOCALPUB_ANAKEEN_UI_PATH=$(LOCALPUB_PATH)/anakeen-ui
LOCALPUB_TEST_PATH=$(LOCALPUB_PATH)/Tests
VERSION_PATH=$(MK_DIR)/VERSION
RELEASE_PATH=$(MK_DIR)/RELEASE
NODE_MODULE_PATH=node_modules
JS_CONF_PATH=$(MK_DIR)
WEBPACK_CONF_PATH=webpackConfig/
PHP_LIB_PATH=anakeen-ui/src/vendor/Anakeen/Ui/PhpLib
JS_ASSET_PATH=anakeen-ui/src/public/uiAssets/externals/
JS_COMPONENT_SOURCE_PATH=anakeen-ui/src/vendor/Anakeen/Components
JS_COMPONENT_BUILD_PATH=anakeen-ui/src/public/components
JS_DDUI_BUILD_PATH=anakeen-ui/src/public/uiAssets
JS_DDUI_SOURCE_PATH=anakeen-ui/src/Apps/DOCUMENT/IHM/
JS_FAMILY_BUILD_PATH=anakeen-ui/src/public/uiAssets/Families/
JS_FAMILY_SOURCE_PATH=anakeen-ui/src/vendor/Anakeen/SmartStructures/
JS_TEST_BUILD_PATH=Tests/src/public/
JS_TEST_SOURCE_PATH=Tests/src/vendor/Anakeen/
ANAKEEN_UI_SRC_PATH=anakeen-ui/
TEST_SRC_PATH=Tests/

## Version and release
VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)

## control conf
port=80
CONTROL_PORT=$(port)
CONTROL_USER= admin
CONTROL_PASSWORD= anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn
DEVTOOL_BIN=php ./anakeen-devtool.phar
COMPOSER_BIN=composer

-include Makefile.local

########################################################################################################################
##
## devtools
##
########################################################################################################################

$(JS_CONF_PATH)/yarn.lock: $(JS_CONF_PATH)/package.json
	$(YARN_BIN) install
	touch "$@"

$(PHP_LIB_PATH)/composer.lock: $(PHP_LIB_PATH)/composer.json
	cd anakeen-ui/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; $(COMPOSER_BIN) install

install: $(JS_CONF_PATH)/yarn.lock $(PHP_LIB_PATH)/composer.lock ## Install deps (js an php)

stub: ## Generate stubs
	$(DEVTOOL_BIN) generateStub -s anakeen-ui -o $(STUB_PATH)
	$(DEVTOOL_BIN) generateStub -s Tests -o $(STUB_PATH)

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################

$(JS_ASSET_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${WEBPACK_CONF_PATH} -type f -print | sed 's/ /\\ /g')
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) buildAsset
	touch "$@"

$(JS_DDUI_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_DDUI_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/webpack.config.js $(WEBPACK_CONF_PATH)/webpack.parts.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) build
	touch "$@"

$(JS_COMPONENT_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_COMPONENT_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/webpack.component.js $(WEBPACK_CONF_PATH)/webpack.parts.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) buildComponent
	touch "$@"

$(JS_FAMILY_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_FAMILY_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/webpack.family.js $(WEBPACK_CONF_PATH)/webpack.parts.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) buildFamily
	touch "$@"

$(LOCALPUB_ANAKEEN_UI_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${ANAKEEN_UI_SRC_PATH} -type f -print | sed 's/ /\\ /g') $(VERSION_PATH) $(RELEASE_PATH) $(PHP_LIB_PATH)/composer.lock $(JS_ASSET_PATH) $(JS_DDUI_BUILD_PATH) $(JS_COMPONENT_BUILD_PATH) $(JS_FAMILY_BUILD_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -f user-interfaces-*.app
	-mkdir -p $(LOCALPUB_ANAKEEN_UI_PATH)
	rsync --delete -azvr $(ANAKEEN_UI_SRC_PATH) $(LOCALPUB_ANAKEEN_UI_PATH)
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(LOCALPUB_ANAKEEN_UI_PATH)/build.json $(LOCALPUB_ANAKEEN_UI_PATH)/src/Apps/DOCUMENT/DOCUMENT_init.php
	$(DEVTOOL_BIN) generateWebinst -s $(LOCALPUB_ANAKEEN_UI_PATH) -o .
	touch "$@"

app: $(JS_ASSET_PATH) $(JS_COMPONENT_BUILD_PATH) $(JS_DDUI_BUILD_PATH) $(JS_FAMILY_BUILD_PATH) $(LOCALPUB_ANAKEEN_UI_PATH) ## build the project
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"

deploy: app ## deploy the project
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(DEVTOOL_BIN) deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w user-interfaces-*app -- --force
	make clean

quick-deploy:
	rm -f *app
	-mkdir -p ${LOCALPUB_PATH}/webinst
	rsync --delete -azvr anakeen-ui ${LOCALPUB_PATH}/webinst/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" ${LOCALPUB_PATH}/webinst/anakeen-ui/build.json ${LOCALPUB_PATH}/webinst/anakeen-ui/src/Apps/DOCUMENT/DOCUMENT_init.php
	$(DEVTOOL_BIN) generateWebinst -s ${LOCALPUB_PATH}/webinst/anakeen-ui/ -o .
	$(DEVTOOL_BIN) deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w user-interfaces-*app -- --force

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf ${LOCALPUB_PATH}
	rm -f *app
	-rm -rf $(STUB_PATH)
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

po: pojs ## extract the po
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(DEVTOOL_BIN) extractPo -s anakeen-ui -o anakeen-ui/src

pojs:
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make

########################################################################################################################
##
## TEST TARGET
##
########################################################################################################################

$(JS_TEST_BUILD_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_TEST_SOURCE_PATH} -type f -print | sed 's/ /\\ /g') $(JS_CONF_PATH)/yarn.lock $(WEBPACK_CONF_PATH)/webpack.test.js $(WEBPACK_CONF_PATH)/webpack.parts.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	make -f pojs.make compile
	$(YARN_BIN) buildTest
	touch "$@"

app-test: $(TEST_SRC_PATH) $(JS_TEST_BUILD_PATH) ## Build the test package
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -f *app
	-mkdir -p ${LOCALPUB_TEST_PATH}
	rsync --delete -azvr $(TEST_SRC_PATH) ${LOCALPUB_TEST_PATH}
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" ${LOCALPUB_TEST_PATH}/Tests/build.json
	$(DEVTOOL_BIN) generateWebinst -s ${LOCALPUB_TEST_PATH} -o .

deploy-test: app-test ## Deploy the test package
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(DEVTOOL_BIN) deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w user-interfaces-test*app -- --force

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