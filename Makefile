#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))
NODE_MODULE_PATH=node_modules
JS_CONF_PATH=$(MK_DIR)
JS_BUILD_PATH=dist
########################################################################################################################
##
## Node
##
########################################################################################################################

autorelease:
	@${PRINT_COLOR} "${DEBUG_COLOR}autorelease $@${RESET_COLOR}\n"
	npm version $(shell node -p "require('./package.json').version")-$(shell date +%s)

nodePublish: autorelease
	@${PRINT_COLOR} "${DEBUG_COLOR}nodePublish $@${RESET_COLOR}\n"
	npm publish

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################

.PHONY: nodePublish

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
