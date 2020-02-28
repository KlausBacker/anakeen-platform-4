include ../.devtool/Makefile.params.mk
DEVTOOLS_DIR = ../.devtool

#path
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

lint: checkXML checkVersion $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Lint PHP$(COLOR_RESET)\n"
	$(CS_BIN) --standard=${CS_PATH_NAME} --extensions=php ./src

beautify: $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Beautify PHP$(COLOR_RESET)\n"
	$(CBF_BIN) --standard=${CS_PATH_NAME} --extensions=php ./src
	$(CBF_BIN) --standard=${CS_PATH_NAME} --extensions=php ./Tests

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

app-test: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app test$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build --sourcePath ./Tests -t ${APP_OUTPUT_PATH}

app-all: app app-test

app-autorelease: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app autotrelease$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-test-autorelease: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app test autotrelease$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build --sourcePath ./Tests --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease app-test-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Deploy$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) deploy --auto-release --sourcePath .

deploy-test: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Deploy test$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) deploy --auto-release --sourcePath ./Tests

deploy-all: deploy deploy-test

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
