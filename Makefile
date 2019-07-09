#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

NODE_BIN=node
NPX_BIN=npx

TOPTARGETS := app app-autorelease app-test app-all deploy deploy-test deploy-all lint po stub clean checkXML

SUBDIRS := smart-data-engine security workflow internal-components user-interfaces hub-station admin-center business-app development-center transformation migration-tools

$(TOPTARGETS): $(SUBDIRS)
$(SUBDIRS):
	$(MAKE) APP_OUTPUT_PATH=$(MK_DIR)/build/$@ -C $@ $(MAKECMDGOALS)

lintJS:
	npx eslint ./ --cache

beautifyJS:
	npx eslint ./ --cache --fix

localRepo:
	node ./.devtool/script/generateLocalRepo.js

checkPo:
	./.devtool/ci/check/checkPo.sh

.PHONY: $(TOPTARGETS) $(SUBDIRS)
