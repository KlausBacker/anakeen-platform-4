#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))
NODE_MODULE_PATH=node_modules
SCSS_SRC=scss
CSS_BUILD=dist/css
BUILD_SCRIPTS=build
BUILD_SCSS=$(SCSS_SRC)/gen


YARN_BIN=yarn
YARN_LOCKFILE=yarn.lock

VERSION=$(shell node -p "require('./package.json').version")

-include Makefile.local


install:
	@${PRINT_COLOR} "${DEBUG_COLOR}Install${RESET_COLOR}\n"
	$(YARN_BIN) install

########################################################################################################################
##
## BUILD ASSETS TARGET
##
########################################################################################################################
$(BUILD_SCSS):
	@${PRINT_COLOR} "${DEBUG_COLOR}Create $@${RESET_COLOR}\n"
	mkdir -p $@
	touch "$@"

# Generate SCSS intermediate files
$(BUILD_SCSS)/*.scss: $(NODE_MODULE_PATH) $(YARN_LOCKFILE) $(BUILD_SCSS) $(shell find ${BUILD_SCRIPTS} -type f -print | sed 's/ /\\ /g')
	@${PRINT_COLOR} "${DEBUG_COLOR}$@${RESET_COLOR}\n"
	$(YARN_BIN) buildAssets

genAssets: $(NODE_MODULE_PATH) $(YARN_LOCKFILE) $(BUILD_SCSS) $(BUILD_SCSS)/*.scss
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate SCSS intermediate files${RESET_COLOR}\n"

# Build CSS files
$(CSS_BUILD)/*.css: $(NODE_MODULE_PATH) $(YARN_LOCKFILE) $(BUILD_SCSS) $(BUILD_SCSS)/*.scss
	@${PRINT_COLOR} "${DEBUG_COLOR}Generate CSS${RESET_COLOR}\n"
	$(YARN_BIN) build

build: $(NODE_MODULE_PATH) $(YARN_LOCKFILE) $(BUILD_SCSS) $(CSS_BUILD)/*.css
	@${PRINT_COLOR} "${DEBUG_COLOR}$@${RESET_COLOR}\n"

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

autoPublish: updateDeps build
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
	rm -rf $(CSS_BUILD)/*

cleanAll: clean ## clean the local pub and the node_module
	@${PRINT_COLOR} "${DEBUG_COLOR}Build $@${RESET_COLOR}\n"
	rm -rf $(NODE_MODULE_PATH)
	rm -rf $(BUILD_SCSS)
	touch package.json

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################

.PHONY: app po deploy install app-test deploy-test pojs clean cleanAll stub

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
