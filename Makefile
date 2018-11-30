#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

#path
STUB_PATH=stubs/
VERSION_PATH=$(MK_DIR)/VERSION
RELEASE_PATH=$(MK_DIR)/RELEASE
JS_CONF_PATH=$(MK_DIR)
NODE_MODULE_PATH=node_modules
WEBPACK_CONF_PATH=webpack

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
CBF_BIN=php ./ide/vendor/bin/phpcbf
CS_BIN=php ./ide/vendor/bin/phpcs

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
	${ANAKEEN_CLI_BIN} generateStubs --sourcePath .

########################################################################################################################
##
## BUILD TARGET
##
########################################################################################################################


$(NODE_MODULE_PATH):
	$(YARN_BIN) install

compile: $(NODE_MODULE_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	$(YARN_BIN) run build

app: compile ## build admin center
	${ANAKEEN_CLI_BIN} build

deploy: app ## deploy admin center
	${ANAKEEN_CLI_BIN} deploy --auto-release --source-path . -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

autotest: compile
	${ANAKEEN_CLI_BIN} build --auto-release
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
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean $@${RESET_COLOR}\n"
	rm -fr ./src/public/Anakeen/ ./src/public/AdminCenter
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
	${ANAKEEN_CLI_BIN} extractPo --sourcePath $(ADMIN_CENTER_SRC_PATH)

########################################################################################################################
##
## Beautify TARGET
##
########################################################################################################################

beautify: $(NODE_MODULE_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify $@${RESET_COLOR}\n"
	$(YARN_BIN) run beautify
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}src

lint: $(NODE_MODULE_PATH)
	@${PRINT_COLOR} "${DEBUG_COLOR}lint $@${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}/src

checkXML: $(NODE_MODULE_PATH)
	${ANAKEEN_CLI_BIN} check -s .

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
