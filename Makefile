#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

NODE_BIN=node
NPX_BIN=npx

TOPTARGETS := deploy deploy-test deploy-all lint po stub checkXML

BUILDTARGETS := app app-autorelease app-test app-all

SUBDIRS := control smart-data-engine security workflow internal-components user-interfaces hub-station admin-center business-app development-center transformation migration-tools

BUILDDIRS := app-control app-smart-data-engine app-security app-workflow app-internal-components app-user-interfaces app-hub-station app-admin-center app-business-app app-development-center app-transformation app-migration-tools

$(TOPTARGETS): $(SUBDIRS)

$(SUBDIRS):
	$(MAKE) -C $@ $(MAKECMDGOALS)

$(BUILDTARGETS): $(BUILDDIRS)

$(BUILDDIRS):
	rm -rf $(MK_DIR)/build/$(subst app-,,$@)
	$(MAKE) APP_OUTPUT_PATH=$(MK_DIR)/build/$(subst app-,,$@) -C $(subst app-,,$@) $(MAKECMDGOALS)
	node ./.devtool/script/generateLocalRepo.js

lintJS:
	npx eslint ./ --cache

beautifyJS:
	npx eslint ./ --cache --fix

checkPo:
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

initDocker: start-env
	make -C ./.devtool/docker init
	make -C ./.devtool/docker register-local-repo
	make -C ./.devtool/docker install

statusControl:
	docker exec monorepo_php_1 /var/www/html/control/anakeen-control status

.PHONY: $(TOPTARGETS) $(SUBDIRS)
