#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
MODULE_NAME=admin-center
JS_CONF_PATH=$(MK_DIR)
NODE_MODULE_PATH=node_modules
PHP_LIB_PATH=src/vendor/Anakeen/Routes/Admin/Lib

## control conf
port=80
CONTROL_PROTOCOL=http
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=http://$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./ide/vendor/bin/phpcbf
CS_BIN=php ./ide/vendor/bin/phpcs
COMPOSER_BIN=composer

-include Makefile.local
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

checkXML: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Check XML${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} check -s .

lint: checkXML
	@${PRINT_COLOR} "${DEBUG_COLOR}Lint PHP${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --ignore=${PHP_LIB_PATH} --extensions=php ${MK_DIR}/src

beautify:
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --ignore=${PHP_LIB_PATH} --extensions=php ${MK_DIR}/src

checkPackage:
	${MK_DIR}/.cibuild/targets/autotest/scripts.d/01_checkPackage.sh

########################################################################################################################
##
## Po and stub
##
########################################################################################################################
po: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Extract PO${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} extractPo -s .

stub: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate Stubs${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} generateStubs

########################################################################################################################
##
## BUILD JS
##
########################################################################################################################

buildJS: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Build JS $@${RESET_COLOR}\n"
	$(YARN_BIN) buildJs

########################################################################################################################
##
## Build
##
########################################################################################################################
app: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build

app-all: app

app-autorelease: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --auto-release

app-all-autorelease: app-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps buildJS
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-all: deploy


########################################################################################################################
##
## publishNpm
##
########################################################################################################################

publishNpm:
	@${PRINT_COLOR} "${DEBUG_COLOR}publishNpm $@${RESET_COLOR}\n"
	npm publish

publishNpm--autorelease:
	@${PRINT_COLOR} "${DEBUG_COLOR} publishNpm--autorelase $@${RESET_COLOR}\n"
	npm version $(VERSION)-$(shell find . -type f -print0 | xargs -0 stat --format '%Y' | sort -nr | cut -d: -f2- | head -1)
	npm publish || echo "Already published"

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean $@${RESET_COLOR}\n"
	rm -fr src/public/Anakeen/accountManager src/public/Anakeen/parameterManager src/public/Anakeen/tokenManager src/public/Anakeen/vaultManager src/public/Anakeen/manifest src/public/Anakeen/adminCenter/dev src/public/Anakeen/adminCenter/prod src/public/Anakeen/adminCenter/legacy
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
