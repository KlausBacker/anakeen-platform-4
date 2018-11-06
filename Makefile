#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

MODULE_NAME=development-center
NODE_MODULE_PATH=node_modules

## control conf
port=80
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn
COMPOSER=composer
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./ide/vendor/bin/phpcbf
-include Makefile.local


########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################
$(NODE_MODULE_PATH):
	$(YARN_BIN) install

compile: $(NODE_MODULE_PATH)
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
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -fr ./src/public/Anakeen/
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
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}src

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
