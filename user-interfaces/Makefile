include ../.devtool/Makefile.params.mk
DEVTOOLS_DIR = ../.devtool

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
CS_PATH_NAME=$(DEVTOOLS_DIR)/ci/check/checkPHP/anakeenPhpCs.xml

#Build
APP_OUTPUT_PATH = ../$(BUILD_DIR)/

##bin
CBF_BIN=php $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor/bin/phpcbf
CS_BIN=php $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor/bin/phpcs
COMPOSER_BIN=composer

########################################################################################################################
##
## Deps
##
########################################################################################################################

node_modules:
	$(YARN_CMD) install --frozen-lock

$(ANAKEEN_UI_SRC_PATH)/src/vendor/Anakeen/Ui/PhpLib/vendor:
	cd $(ANAKEEN_UI_SRC_PATH)/src/vendor/Anakeen/Ui/PhpLib; $(COMPOSER_BIN) install

$(DEVTOOLS_DIR)/ci/check/checkPHP/vendor:
	${COMPOSER_BIN} install --working-dir=$(DEVTOOLS_DIR)/ci/check/checkPHP/ --ignore-platform-reqs
########################################################################################################################
##
## Deps
##
########################################################################################################################

install-deps: node_modules $(ANAKEEN_UI_SRC_PATH)/src/vendor/Anakeen/Ui/PhpLib/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Install deps$(COLOR_RESET)\n"

########################################################################################################################
##
## Static analyze
##
########################################################################################################################

checkXML: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Check XML$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) check

checkVersion:
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Check version between info and package$(COLOR_RESET)\n"
	$(CHECK_VERSION_CMD) ./ -s ${ANAKEEN_UI_SRC_PATH}
	$(ANAKEEN_CLI_CMD) check -s ${TEST_SRC_PATH}

lint: checkXML checkVersion $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Lint PHP$(COLOR_RESET)\n"
	$(CS_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH},${JS_ASSET_PATH} --extensions=php ${ANAKEEN_UI_SRC_PATH}/src

beautify: $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Beautify PHP$(COLOR_RESET)\n"
	$(CBF_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH},${JS_ASSET_PATH} --extensions=php  ${ANAKEEN_UI_SRC_PATH}/src

########################################################################################################################
##
## Po and stub
##
########################################################################################################################
po: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Extract PO$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) extractPo --sourcePath ${ANAKEEN_UI_SRC_PATH}

stub: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Generate Stubs$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) generateStubs -s ${ANAKEEN_UI_SRC_PATH}
	$(ANAKEEN_CLI_CMD) generateStubs -s ${TEST_SRC_PATH}

########################################################################################################################
##
## BUILD JS
##
########################################################################################################################

buildJS: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Extract component po $@$(COLOR_RESET)\n"
	make -f pojs.make compile
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build ank component $@$(COLOR_RESET)\n"
	$(YARN_CMD) lib:rollup
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build Css $@$(COLOR_RESET)\n"
	$(YARN_CMD) buildCss
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build asset $@$(COLOR_RESET)\n"
	$(YARN_CMD) buildAsset
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build smart element $@$(COLOR_RESET)\n"
	$(YARN_CMD) buildSmartElement
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build login $@$(COLOR_RESET)\n"
	$(YARN_CMD) buildLogin
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build smart structures $@$(COLOR_RESET)\n"
	$(YARN_CMD) buildSmartStructures

buildComponent: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build ank component $@$(COLOR_RESET)\n"
	$(YARN_CMD) lib:rollup

buildJS-test: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build $@$(COLOR_RESET)\n"
	make -f pojs.make compile
	$(YARN_CMD) buildTest

########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps buildJS
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build -s ${ANAKEEN_UI_SRC_PATH} -t ${APP_OUTPUT_PATH}

app-test: install-deps buildJS-test
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app test$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build -s ${TEST_SRC_PATH} -t ${APP_OUTPUT_PATH}

app-all: app app-test

app-autorelease: install-deps buildJS
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app autotrelease$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build -s ${ANAKEEN_UI_SRC_PATH} --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-test-autorelease: install-deps buildJS-test
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app test autotrelease$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build -s ${TEST_SRC_PATH} --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease app-test-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Deploy$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) deploy --auto-release --sourcePath ${ANAKEEN_UI_SRC_PATH}

deploy-test: install-deps buildJS-test
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Deploy test$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) deploy --auto-release --sourcePath ${TEST_SRC_PATH}

deploy-all: deploy deploy-test

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Clean $@$(COLOR_RESET)\n"
	rm -fr node_modules
	rm -fr components/lib	src/public/uiAssets/ src/public/Anakeen/ Tests/src/public/Anakeen/ src/public/Images
	rm -f *.app
	rm -f *.src
	make -f pojs.make clean
