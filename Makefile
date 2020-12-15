#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

include .devtool/Makefile.params.mk

SHELL = bash
.SHELLFLAGS := -eu -o pipefail -c
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

PROJECT_NAME = monorepo
PROJECT_POSTGRES_VERSION = 12.1-latest
PROJECT_APACHE_PHP_VERSION = 8.0-latest
PROJECT_PSQL_PORT = 54321
PROJECT_HTTP_PORT = 8080
PROJECT_DEVSERVER_PORT = 8001

TOPTARGETS := deploy deploy-test deploy-all lint po stub checkXML clean beautify

BUILDTARGETS := app app-autorelease app-test app-test-autorelease app-all

SUBDIRS := control smart-data-engine internal-components user-interfaces security workflow hub-station admin-center about business-app development-center transformation transformation-server migration-tools dev-data test-tools fulltext-search

CONTROL_ARCHIVE = $(BUILD_DIR)/control/anakeen-control-latest.zip

DOCKER_COMPOSE_ENV += LC_ALL=C.UTF-8 LANG=C.UTF-8 LANGUAGE=C.UTF-8

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

.PHONY: env-start
env-start: | $(VOLUMES_WEBROOT_CONTROL_CONF)/contexts.xml $(VOLUMES_PRIVATE) ## Start docker environment
	@$(PRINT_COLOR) "$(COLOR_SUCCESS)"
	@$(MAKE) --no-print-directory env-list-ports
	@$(PRINT_COLOR) "$(COLOR_RESET)"

.PHONY: env-stop
env-stop: | _env-stop ## Stop docker environment
	@:

.PHONY: env-clean
env-clean: | env-stop ## Clean docker environment
	@$(PRINT_COLOR) "$(COLOR_WARNING)Delete private volumes$(COLOR_RESET)\n"
	rm -rf $(filter-out $(KEEP_VOLUMES),$(VOLUMES_PRIVATE))
#	rm -rf $(VOLUMES_PRIVATE) $(BUILD_DIR)

.PHONY: env-reboot
env-reboot: ## Reboot docker environment (stop + start + set params)
	make env-clean
	make install-all
	make test-mail-set-params

.PHONY: env-reset
env-reset: ## Reset docker environment
	make -C ./$(DEVTOOLS_DIR)/docker env-reset

.PHONY: env-clean-full
env-clean-full: DOCKER_COMPOSE_DOWN_OPTIONS += --rmi local
env-clean-full: env-clean ## Clean docker environment and remove images
	@$(PRINT_COLOR) "$(COLOR_WARNING)Delete cached data$(COLOR_RESET)\n"
#	rm -rf $(NODE_MODULES_DIR) $(DEVTOOLS_TMP_DIR) $(KEEP_VOLUMES)
#	rm -rf $(NODE_MODULES_DIR) $(DEVTOOLS_TMP_DIR)

.PHONY: update-all
update-all: | env-start ## Update all modules
	$(_CONTROL_CMD) update --no-interaction --no-ansi

.PHONY: install-all
install-all: | env-start ## install all modules
	$(MAKE) app
	$(_CONTROL_CMD) install --no-interaction --no-ansi

.PHONY: run-dev-server
run-dev-server: ## Run webpack development server
	NO_CACHE=true $(NVM_EXEC_CMD) $(DEVTOOLS_DIR)/devserver/index.js

.PHONY: run-tests
run-tests:
	$(_CONTROL_SHELL_CMD) "cd vendor/Anakeen/TestUnits/; php ./lib/vendor/phpunit/phpunit/phpunit"

$(CONTROL_ARCHIVE):
	make APP_OUTPUT_PATH=$(MK_DIR)/$(BUILD_DIR)/control -C control app
	$(NVM_EXEC_CMD) $(DEVTOOLS_DIR)/script/generateLocalRepo.js

include .devtool/Makefile.rules.mk
