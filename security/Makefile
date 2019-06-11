#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

## control conf
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
COMPOSER=composer
CBF_BIN=php ./ide/vendor/bin/phpcbf
CS_BIN=php ./ide/vendor/bin/phpcs
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
-include Makefile.local

########################################################################################################################
##
## Deps
##
########################################################################################################################
install-deps:
	@${PRINT_COLOR} "${DEBUG_COLOR}Install deps${RESET_COLOR}\n"
	yarn install

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
	cd ${MK_DIR}/ide; ${COMPOSER} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}/src

beautify:
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}/src
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
## Build
##
########################################################################################################################
app: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build

app-all: app

app-autorelease: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --auto-release

app-all-autorelease: app-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-all: deploy

########################################################################################################################
##
## Clean
##
########################################################################################################################
clean:
	@${PRINT_COLOR} "${DEBUG_COLOR}Clean${RESET_COLOR}\n"
	rm -f *.src
	rm -f *.app

########################################################################################################################
##
## MAKEFILE INTERNALS
##
########################################################################################################################
.PHONY: app app-test app-all app-autorelease app-test-autorelease app-all-autorelease deploy deploy-test deploy-all

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
