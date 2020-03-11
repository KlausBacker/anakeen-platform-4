#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

include .devtool/Makefile.params.mk

SHELL = bash
.SHELLFLAGS := -eu -o pipefail -c
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

PROJECT_NAME = monorepo
PROJECT_POSTGRES_VERSION = 11.6
PROJECT_PHP_VERSION = 7.3
PROJECT_PSQL_PORT = 54321
PROJECT_HTTP_PORT = 8080
PROJECT_DEVSERVER_PORT = 8001
PROJECT_MAIL_PORT = 8081

TOPTARGETS := deploy deploy-test deploy-all lint po stub checkXML clean beautify

BUILDTARGETS := app app-autorelease app-test app-test-autorelease app-all

SUBDIRS := control smart-data-engine security workflow internal-components user-interfaces hub-station admin-center business-app development-center transformation migration-tools dev-data test-tools fulltext-search

CONTROL_ARCHIVE = $(BUILD_DIR)/control/anakeen-control-latest.zip

# User specific variables come last
-include Makefile.local.mk

BUILDDIR_PREFIX := app-

BUILDDIRS := $(addprefix $(BUILDDIR_PREFIX),$(SUBDIRS))

.PHONY: $(TOPTARGETS)
$(TOPTARGETS): $(SUBDIRS)

.PHONY: $(SUBDIRS)
$(SUBDIRS):
	$(MAKE) -C $@ $(MAKECMDGOALS)

.PHONY: $(BUILDTARGETS)
$(BUILDTARGETS): $(BUILDDIRS)

.PHONY: $(BUILDDIRS)
$(BUILDDIRS): $(BUILD_DIR)
	$(MAKE) APP_OUTPUT_PATH=$(MK_DIR)/$(BUILD_DIR)/$(subst $(BUILDDIR_PREFIX),,$@) -C $(subst $(BUILDDIR_PREFIX),,$@) $(MAKECMDGOALS)
	$(NVM_EXEC_CMD) $(DEVTOOLS_DIR)/script/generateLocalRepo.js

.PHONY: lint-JS
lint-JS: ## Check js files
	$(NPX_CMD) eslint ./ --cache

.PHONY: beautify-JS
beautify-JS: ## Lint js files
	$(NPX_CMD) eslint ./ --cache --fix

.PHONY: lint-po
lint-po: ## Lint po
	./$(DEVTOOLS_DIR)/ci/check/checkPo.sh

.PHONY: start-env
start-env: | $(VOLUMES_PHP_CONTROL_CONF)/contexts.xml $(VOLUMES_PRIVATE) ## Start docker environment
	@$(PRINT_COLOR) "$(COLOR_SUCCESS)"
	@$(MAKE) --no-print-directory env-list-ports
	@$(PRINT_COLOR) "$(COLOR_RESET)"

.PHONY: stop-env
stop-env: ## Stop docker environment
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Stop containers$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) down --remove-orphans $(DOCKER_COMPOSE_DOWN_OPTIONS)

.PHONY: clean-env
clean-env: | stop-env ## Clean docker environment
	@$(PRINT_COLOR) "$(COLOR_WARNING)Delete private volumes$(COLOR_RESET)\n"
	#rm -rf $(filter-out $(KEEP_VOLUMES),$(VOLUMES_PRIVATE))
	rm -rf $(VOLUMES_PRIVATE) $(BUILD_DIR)

.PHONY: reboot-env
reboot-env: ## Reboot docker environment (stop + start + set params)
	make clean-env
	make install-all
	make test-mail-set-params

.PHONY: reset-env
reset-env: ## Reset docker environment
	make -C ./$(DEVTOOLS_DIR)/docker reset-env

.PHONY: clean-env-full
clean-env-full: DOCKER_COMPOSE_DOWN_OPTIONS += --rmi local
clean-env-full: clean-env ## Clean docker environment and remove images
	@$(PRINT_COLOR) "$(COLOR_WARNING)Delete cached data$(COLOR_RESET)\n"
#	rm -rf $(NODE_MODULES_DIR) $(DEVTOOLS_TMP_DIR) $(KEEP_VOLUMES)
#	rm -rf $(NODE_MODULES_DIR) $(DEVTOOLS_TMP_DIR)

.PHONY: update-all
update-all: | start-env ## Update all modules
	$(_CONTROL_CMD) update --no-interaction --no-ansi

.PHONY: install-all
install-all: | start-env ## install all modules
	$(MAKE) app
	$(_CONTROL_CMD) install --no-interaction --no-ansi

.PHONY: run-dev-server
run-dev-server: ## Run webpack development server
	NO_CACHE=true $(NVM_EXEC_CMD) $(DEVTOOLS_DIR)/devserver/index.js

$(CONTROL_ARCHIVE):
	make APP_OUTPUT_PATH=$(MK_DIR)/$(BUILD_DIR)/control -C control app
	$(NVM_EXEC_CMD) $(DEVTOOLS_DIR)/script/generateLocalRepo.js

include .devtool/Makefile.rules.mk
