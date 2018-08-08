#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
MODULE_NAME=anakeen-hub
JS_CONF_PATH=$(MK_DIR)
NODE_MODULE_PATH=node_modules

## control conf
port=80
CONTROL_PROTOCOL=http
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn
DEVTOOL_BIN=php ./anakeen-devtool.phar
ANAKEEN_CLI=node ./node_modules/@anakeen/anakeen-cli
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

install: $(JS_CONF_PATH)/yarn.lock ## Install deps (js an php)

stub: ## Generate stubs
	$(ANAKEEN_CLI) generateStubs

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################
$(NODE_MODULE_PATH):
	$(YARN_BIN) install

app: $(NODE_MODULE_PATH) $(JS_CONF_PATH)/yarn.lock
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) run buildJs
	$(YARN_BIN) run build

deploy: app ## deploy sample
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w ${MODULE_NAME}*.app -- --force
	make clean

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf ${MODULE_NAME}*.app

########################################################################################################################
##
## PO TARGET
##
########################################################################################################################

po: ## extract the po
	${DEVTOOL_BIN} extractPo -s $(SAMPLE_SRC_PATH)/ -o $(MK_DIR)/$(SAMPLE_SRC_PATH)/src/

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
