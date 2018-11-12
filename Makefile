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
JS_ADMIN_CENTER_PATH=admin-center/src/public/Anakeen/adminCenter/prod

## Version and release
VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)

## control conf
port=80
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=http://$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
YARN_BIN=yarn

ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
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
	${ANAKEEN_CLI_BIN} generateStubs --sourcePath $(LOCALPUB_ADMIN_CENTER_PATH)

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################

$(JS_ADMIN_CENTER_PATH): $(JS_CONF_PATH)/yarn.lock $(shell find ${ADMIN_CENTER_SRC_PATH} -type f -print | sed 's/ /\\ /g') $(shell find ${WEBPACK_CONF_PATH} -type f -print | sed 's/ /\\ /g')
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) build
	touch "$@"

$(NODE_MODULE_PATH):
	$(YARN_BIN) install

$(LOCALPUB_ADMIN_CENTER_PATH): $(JS_CONF_PATH)/yarn.lock $(JS_ADMIN_CENTER_PATH) $(VERSION_PATH) $(RELEASE_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	-mkdir -p $(LOCALPUB_ADMIN_CENTER_PATH)
	rsync --delete -azvr $(ADMIN_CENTER_SRC_PATH)/ $(LOCALPUB_ADMIN_CENTER_PATH)
	${ANAKEEN_CLI_BIN} build --sourcePath $(LOCALPUB_ADMIN_CENTER_PATH)
	touch "$@"


app: $(NODE_MODULE_PATH) $(LOCALPUB_ADMIN_CENTER_PATH) $(JS_ADMIN_CENTER_PATH) ## build admin center
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"

deploy: app ## deploy admin center
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath $(LOCALPUB_ADMIN_CENTER_PATH) -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

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
	${ANAKEEN_CLI_BIN} extractPo --sourcePath $(LOCALPUB_ADMIN_CENTER_PATH)

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
