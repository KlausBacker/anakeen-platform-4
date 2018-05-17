#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
STUB_PATH=stubs/
LOCALPUB_PATH=$(MK_DIR)/localpub
LOCALPUB_ADMIN_CENTER_PATH=$(LOCALPUB_PATH)/admin-center
VERSION_PATH=$(MK_DIR)/VERSION
RELEASE_PATH=$(MK_DIR)/RELEASE
JS_CONF_PATH=$(MK_DIR)
NODE_MODULE_PATH=node_modules
WEBPACK_CONF_PATH=webpack
ADMIN_CENTER_SRC_PATH=admin-center
JS_ADMIN_CENTER_PATH=admin-center/src/public/AdminCenter

## Version and release
VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)

## control conf
port=80
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
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

install: $(JS_CONF_PATH)/yarn.lock ## Install deps (js an php)

stub: ## Generate stubs
	$(DEVTOOL_BIN) generateStub -s $(ADMIN_CENTER_SRC_PATH) -o $(STUB_PATH)

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################

$(JS_ADMIN_CENTER_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${ADMIN_CENTER_SRC_PATH} -type f -print | sed 's/ /\\ /g') $(shell find ${WEBPACK_CONF_PATH} -type f -print | sed 's/ /\\ /g')
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) build
	touch "$@"

$(LOCALPUB_ADMIN_CENTER_PATH): $(JS_CONF_PATH)/yarn.lock $(JS_ADMIN_CENTER_PATH) $(VERSION_PATH) $(RELEASE_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	-mkdir -p $(LOCALPUB_ADMIN_CENTER_PATH)
	rsync --delete -azvr $(ADMIN_CENTER_SRC_PATH)/ $(LOCALPUB_ADMIN_CENTER_PATH)
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(LOCALPUB_ADMIN_CENTER_PATH)/build.json
	$(DEVTOOL_BIN) generateWebinst --force -s $(LOCALPUB_ADMIN_CENTER_PATH) -o .
	touch "$@"

app: $(LOCALPUB_ADMIN_CENTER_PATH) $(JS_ADMIN_CENTER_PATH) ## build admin center
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"

deploy: app
	${DEVTOOL_BIN} deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w admin-center*.app -- --force

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf ${LOCALPUB_PATH}

cleanAll: clean ## clean the local pub and the node_module
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf $(NODE_MODULE_PATH)
	touch $(JS_CONF_PATH)/package.json

########################################################################################################################
##
## PO TARGET
##
########################################################################################################################

po: ## extract the po
	${DEVTOOL_BIN} extractPo -s $(ADMIN_CENTER_SRC_PATH)/ -o $(MK_DIR)/$(ADMIN_CENTER_SRC_PATH)/src/

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################

.PHONY: app po deploy install pojs clean cleanAll stub

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