#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
MODULE_NAME=anakeen-hub
STUB_PATH=stubs/
LOCALPUB_PATH=$(MK_DIR)/localpub
LOCALPUB_SAMPLE_PATH=$(LOCALPUB_PATH)/anakeen-hub
VERSION_PATH=$(MK_DIR)/VERSION
RELEASE_PATH=$(MK_DIR)/RELEASE
JS_CONF_PATH=$(MK_DIR)
NODE_MODULE_PATH=node_modules
WEBPACK_CONF_PATH=webpack
SAMPLE_SRC_PATH=anakeen-hub
JS_BUILD_PATH=$(SAMPLE_SRC_PATH)/src/public/anakeenHub
JS_SRC_PATH=$(SAMPLE_SRC_PATH)/src/vendor/Anakeen/anakeenHub/Layout

## Version and release
VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)

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
	$(DEVTOOL_BIN) generateStub -s $(SAMPLE_SRC_PATH) -o $(STUB_PATH)

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################
$(NODE_MODULE_PATH):
	$(YARN_BIN) install

$(LOCALPUB_SAMPLE_PATH): $(NODE_MODULE_PATH) $(JS_CONF_PATH)/yarn.lock $(JS_BUILD_PATH) $(VERSION_PATH) $(RELEASE_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	-mkdir -p $(LOCALPUB_SAMPLE_PATH)
	rsync --delete -azvr $(SAMPLE_SRC_PATH)/ $(LOCALPUB_SAMPLE_PATH)
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(LOCALPUB_SAMPLE_PATH)/build.json
	$(DEVTOOL_BIN) generateWebinst --force -s $(LOCALPUB_SAMPLE_PATH) -o .
	touch "$@"

$(JS_BUILD_PATH): $(NODE_MODULE_PATH) $(JS_CONF_PATH)/yarn.lock $(shell find ${JS_SRC_PATH} -type f -print | sed 's/ /\\ /g') $(WEBPACK_CONF_PATH)/webpack.config.js $(WEBPACK_CONF_PATH)/webpack.parts.js
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) build
	touch "$@"


app: $(LOCALPUB_SAMPLE_PATH) ## build sample
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"

deploy: ## deploy sample
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w ${MODULE_NAME}*.app -- --force
	make clean

########################################################################################################################
##
## Node
##
########################################################################################################################
autorelease:
	@${PRINT_COLOR} "${DEBUG_COLOR}autorelease $@${RESET_COLOR}\n"
	npm version $(VERSION)-$(shell date +%s)

nodePublish:
	@${PRINT_COLOR} "${DEBUG_COLOR}nodePublish $@${RESET_COLOR}\n"
	npm publish

autoPublish:
	@${PRINT_COLOR} "${DEBUG_COLOR}$@${RESET_COLOR}\n"
	npm version $(VERSION)-$(shell find . -type f -print0 | xargs -0 stat --format '%Y' | sort -nr | cut -d: -f2- | head -1)
	npm publish || echo "Already published"

########################################################################################################################
##
## CLEAN TARGET
##
########################################################################################################################

clean: ## clean the local pub
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf ${LOCALPUB_PATH}
	rm -rf ${MODULE_NAME}*.app

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
