include ../.devtool/Makefile.params.mk
DEVTOOLS_DIR = ../.devtool

#Path
PHP_LIB_PATH=src/vendor/Anakeen/Routes/Admin/Lib
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

$(DEVTOOLS_DIR)/ci/check/checkPHP/vendor:
	${COMPOSER_BIN} install --working-dir=$(DEVTOOLS_DIR)/ci/check/checkPHP/ --ignore-platform-reqs

########################################################################################################################
##
## Deps
##
########################################################################################################################
install-deps: node_modules
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
	$(CHECK_VERSION_CMD) ./

lint: checkXML $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Lint PHP$(COLOR_RESET)\n"
	$(CS_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH} --extensions=php ./src

beautify: $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Beautify PHP$(COLOR_RESET)\n"
	$(CBF_BIN) --standard=${CS_PATH_NAME} --ignore=${PHP_LIB_PATH} --extensions=php ./src
########################################################################################################################
##
## Po and stub
##
########################################################################################################################
po: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Extract PO$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) extractPo

stub: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Generate Stubs$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) generateStubs

########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build -t ${APP_OUTPUT_PATH}

app-test:

app-all: app

app-autorelease: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app autotrelease$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Deploy$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) deploy --auto-release --sourcePath .

deploy-test:

deploy-all: deploy

########################################################################################################################
##
## Clean
##
########################################################################################################################
clean:
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Clean$(COLOR_RESET)\n"
	rm -fr node_modules
	rm -f *.src
	rm -f *.app
