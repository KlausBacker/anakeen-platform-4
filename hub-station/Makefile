include ../.devtool/Makefile.params.mk
DEVTOOLS_DIR = ../.devtool

#path
MODULE_NAME=anakeen-hub
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

install-deps: node_modules
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Install deps$(COLOR_RESET)\n"

########################################################################################################################
##
## Static analyze
##
########################################################################################################################

checkXML: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Check XML$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) check -s ./

checkVersion:
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Check version between info and package$(COLOR_RESET)\n"
	$(CHECK_VERSION_CMD) ./

lint: checkXML checkVersion $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Lint PHP$(COLOR_RESET)\n"
	$(CS_BIN) --standard=${CS_PATH_NAME} --extensions=php ./src

beautify: $(DEVTOOLS_DIR)/ci/check/checkPHP/vendor
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Beautify PHP$(COLOR_RESET)\n"
	$(CBF_BIN) --standard=${CS_PATH_NAME} --extensions=php ./src

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
## BUILD JS
##
########################################################################################################################

buildJS: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build JS $@$(COLOR_RESET)\n"
	$(YARN_CMD) lib
	$(YARN_CMD) buildJsRender
	$(YARN_CMD) buildJs

lib: install-deps
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Build JS lib $@$(COLOR_RESET)\n"
	$(YARN_CMD) lib

########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps buildJS
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build -t ${APP_OUTPUT_PATH}

app-test:
app-test-autorelease:

app-all: app

app-autorelease: install-deps buildJS
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Make app autotrelease$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) build --autoRelease="dev" -t ${APP_OUTPUT_PATH}

app-all-autorelease: app-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Deploy$(COLOR_RESET)\n"
	$(ANAKEEN_CLI_CMD) deploy --auto-release --sourcePath .

deploy-test:

deploy-all: deploy

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Clean $@$(COLOR_RESET)\n"
	rm -fr ./src/public/Anakeen/
	rm -fr node_modules
	rm -rf ${MODULE_NAME}*.app
	rm -rf ${MODULE_NAME}*.src
