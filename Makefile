.DEFAULT_GOAL := help
#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

NODE_BIN=node
NPX_BIN=npx

TOPTARGETS := deploy deploy-test deploy-all lint po stub checkXML clean

BUILDTARGETS := app app-autorelease app-test app-test-autorelease app-all

SUBDIRS := control smart-data-engine security workflow internal-components user-interfaces hub-station admin-center business-app development-center transformation migration-tools dev-data

BUILDDIRS := app-control app-smart-data-engine app-security app-workflow app-internal-components app-user-interfaces app-hub-station app-admin-center app-business-app app-development-center app-transformation app-migration-tools app-dev-data

$(TOPTARGETS): $(SUBDIRS)

$(SUBDIRS):
	$(MAKE) -C $@ $(MAKECMDGOALS)

$(BUILDTARGETS): $(BUILDDIRS)

$(BUILDDIRS):
#rm -rf $(MK_DIR)/build/$(subst app-,,$@)
	mkdir -p build
	$(MAKE) APP_OUTPUT_PATH=$(MK_DIR)/build/$(subst app-,,$@) -C $(subst app-,,$@) $(MAKECMDGOALS)
	node ./.devtool/script/generateLocalRepo.js

lint-JS: ## Check js files
	$(NPX_BIN) eslint ./ --cache

beautify-JS: ## Lint js files
	$(NPX_BIN) eslint ./ --cache --fix

lint-po: ## Lint po
	./.devtool/ci/check/checkPo.sh

start-env: ## Start docker environment
	make -C ./.devtool/docker start-env

stop-env: ## Stop docker environment
	make -C ./.devtool/docker stop-env

clean-env: ## Clean docker environment
	make -C ./.devtool/docker clean-env

reboot-env: ## Reboot docker environment (stop + start + set params)
	make -C ./.devtool/docker reboot-env

reset-env: ## Reset docker environment
	make -C ./.devtool/docker reset-env

clean-env-full: ## Clean docker environment and remove images
	make -C ./.devtool/docker clean-env-full

update-all: app-autorelease ## Update all modules
	docker exec monorepo_php_1 /var/www/html/control/anakeen-control update -n

init-docker: start-env ## Init docker environment
	make -C ./.devtool/docker init
	make -C ./.devtool/docker register-local-repo
	make -C ./.devtool/docker install

control-status: ## Get controll status
	watch docker exec monorepo_php_1 /var/www/html/control/anakeen-control status

control-bash: ## Run www-data bash
	make -C ./.devtool/docker docker-prompt-platform

run-bash: ## Run bash in php container
	make -C ./.devtool/docker docker-prompt-root

run-sql: ## Run psql in postgres container
	make -C ./.devtool/docker docker-prompt-psql

run-dev-server: ## Run webpack development server
	$(NODE_BIN) .devtool/devserver/index.js

.PHONY: $(TOPTARGETS) $(SUBDIRS) $(BUILDDIRS) $(BUILDTARGETS) help lint-JS beautify-JS lint-po start-env stop-env clean-env reset-env reboot-env clean-env-full update-all init-docker control-status run-dev-server

help: HELP_WIDTH = 25
help: ## Show this help message
	@grep -h -E -e '^[a-zA-Z_%-]+:.*?## .*$$' -e '^##: ##' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-$(HELP_WIDTH)s\033[0m %s\n", $$1, $$2}'
