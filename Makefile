#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

MODULE_NAME=development-center
NODE_MODULE_PATH=node_modules
PHP_LIB_PATH=src/vendor/Anakeen/Routes/Devel/Lib
## control conf
port=80
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn
COMPOSER_BIN=composer
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./ide/vendor/bin/phpcbf
CS_BIN=php ./ide/vendor/bin/phpcs

-include Makefile.local


########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################
$(NODE_MODULE_PATH):
	$(YARN_BIN) install

${PHP_LIB_PATH}/autoload.php:
	cd ${PHP_LIB_PATH}; ${COMPOSER_BIN} install --ignore-platform-reqs

install: ${PHP_LIB_PATH}/autoload.php

compile: $(NODE_MODULE_PATH) install
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) run buildJs

app: compile
	${ANAKEEN_CLI_BIN} build

autotest: compile
	${ANAKEEN_CLI_BIN} build --auto-release

deploy: compile
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./ -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean $@${RESET_COLOR}\n"
	rm -fr ./src/public/Anakeen/
	rm -fr ${PHP_LIB_PATH}/vendor
	rm -rf ${MODULE_NAME}*.app

########################################################################################################################
##
## PO TARGET
##
########################################################################################################################

po:
	${ANAKEEN_CLI_BIN} extractPo -s .

stub:
	${ANAKEEN_CLI_BIN} generateStubs

########################################################################################################################
##
## Beautify TARGET
##
########################################################################################################################

beautify:
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify $@${RESET_COLOR}\n"
	$(YARN_BIN) run beautify
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --ignore=${PHP_LIB_PATH} --extensions=php ${MK_DIR}src

lint:
	@${PRINT_COLOR} "${DEBUG_COLOR}lint $@${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --ignore=${PHP_LIB_PATH} --extensions=php ${MK_DIR}/src


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
