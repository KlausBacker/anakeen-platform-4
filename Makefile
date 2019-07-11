#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

NODE_BIN=node
NPX_BIN=npx

TOPTARGETS := deploy deploy-test deploy-all lint po stub checkXML clean

BUILDTARGETS := app app-autorelease app-test app-all

SUBDIRS := control smart-data-engine security workflow internal-components user-interfaces hub-station admin-center business-app development-center transformation migration-tools dev-data

BUILDDIRS := app-control app-smart-data-engine app-security app-workflow app-internal-components app-user-interfaces app-hub-station app-admin-center app-business-app app-development-center app-transformation app-migration-tools app-dev-data

$(TOPTARGETS): $(SUBDIRS)

$(SUBDIRS):
	$(MAKE) -C $@ $(MAKECMDGOALS)

$(BUILDTARGETS): $(BUILDDIRS)

$(BUILDDIRS):
	rm -rf $(MK_DIR)/build/$(subst app-,,$@)
	mkdir -p build
	$(MAKE) APP_OUTPUT_PATH=$(MK_DIR)/build/$(subst app-,,$@) -C $(subst app-,,$@) $(MAKECMDGOALS)
	node ./.devtool/script/generateLocalRepo.js

lint-JS:
	$(NPX_BIN) eslint ./ --cache

beautify-JS:
	$(NPX_BIN) eslint ./ --cache --fix

lint-po:
	./.devtool/ci/check/checkPo.sh

start-env:
	make -C ./.devtool/docker start-env

stop-env:
	make -C ./.devtool/docker stop-env

clean-env:
	make -C ./.devtool/docker clean-env

clean-env-full:
	make -C ./.devtool/docker clean-env-full

update-all: app-autorelease
	docker exec monorepo_php_1 /var/www/html/control/anakeen-control update -n

init-docker: start-env
	make -C ./.devtool/docker init
	make -C ./.devtool/docker register-local-repo
	make -C ./.devtool/docker install

control-status:
	docker exec monorepo_php_1 /var/www/html/control/anakeen-control status


control-bash:
	make -C ./.devtool/docker docker-prompt-platform

run-bash:
	make -C ./.devtool/docker docker-prompt-root

run-sql:
	make -C ./.devtool/docker docker-prompt-psql


run-dev-server:
	$(NODE_BIN) .devtool/devserver/index.js

.PHONY: $(TOPTARGETS) $(SUBDIRS) $(BUILDDIRS) $(BUILDTARGETS) lint-JS beautify-JS lint-po start-env stop-env clean-env clean-env-full update-all init-docker control-status run-dev-server
