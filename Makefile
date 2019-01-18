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
	cd src/vendor/Anakeen/lib; ${COMPOSER} install --ignore-platform-reqs
	cd Tests/src/vendor/Anakeen/TestUnits/lib; ${COMPOSER} install --ignore-platform-reqs
	yarn install

########################################################################################################################
##
## Static analyze
##
########################################################################################################################

checkXML: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Check XML${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} check -s .
	${ANAKEEN_CLI_BIN} check -s Tests

lint: checkXML
	@${PRINT_COLOR} "${DEBUG_COLOR}Lint PHP${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php --ignore=./src/vendor/Anakeen/lib,src/API/setSessionHandler.php,src/API/importDocuments.php,src/vendor/Dcp/Core/Utils/Types.php,src/vendor/Dcp/Vault/VaultAnalyzerCLI.php,src/vendor/Dcp/Vault/VaultAnalyzer.php,src/vendor/Dcp/ConsoleProgressOMeter.php,src/vendor/Dcp/Style/ParserStyle.php,src/vendor/Dcp/Style/ICssParser.php,src/vendor/Dcp/Router/NotHandler.php,src/vendor/Dcp/Utils/htmlclean.php,src/vendor/Dcp/Utils/Types.php,src/vendor/Anakeen/Core/Internal/Format/noAccessAttributeValue.php,src/vendor/Anakeen/Core/Internal/Authenticator.php,src/vendor/Anakeen/Core/Internal/SmartElement.php,src/vendor/Anakeen/Core/SmartStructure/NormalAttribute.php,src/vendor/Anakeen/Core/SmartStructure/DocEnum.php,src/vendor/Anakeen/Core/SmartStructure/Attributes.php,src/vendor/Anakeen/Core/Utils/Strings.php,src/vendor/Anakeen/Core/Utils/Gettext.php,src/vendor/Anakeen/Script/Stdio.php,src/vendor/Anakeen/Script/IStdio.php,src/vendor/Anakeen/Routes/Middleware/Log.php,src/vendor/Anakeen/Search/Filters/_OneEquals.php,src/vendor/Anakeen/Search/Filters/Exception.php,src/vendor/Anakeen/Search/Filters/OneContains.php,src/vendor/Anakeen/Search/Filters/OneGreaterThan.php,src/vendor/Anakeen/Search/Filters/OneLesserThan.php,src/vendor/Anakeen/FDL/Lib.Vault.php,src/vendor/Anakeen/FDL/LegacyDocManager.php,src/vendor/Anakeen/FDL/Lib.Util.php,src/vendor/Anakeen/SmartStructures/Mailtemplate/MailTemplateHooks.php,src/vendor/Anakeen/SmartStructures/Group/GroupHooks.php,src/vendor/Root/Class.DocLDAP.php,src/vendor/Root/Class.DocCollection.php,src/vendor/Root/Class.SearchHighlight.php,src/vendor/Root/Class.VGroup.php,src/vendor/Root/Class.processExecuteAPI.php,src/vendor/Root/ErrorCodePMGT.php,src/vendor/Root/CheckDoc.php,src/vendor/Root/EnumAttributeTools.php,src/vendor/Root/Class.DocVaultIndex.php,src/vendor/Root/CheckKeys.php,src/vendor/Root/Class.CheckDb.php,src/vendor/Root/Class.Lang.php,src/vendor/Root/Class.InstallUtils.php,src/vendor/Root/Class.Fdl_Mail_Mime.php,src/vendor/Root/Class.Out.php,src/vendor/Root/Class.DocHtmlFormat.php,src/vendor/Root/Class.DocPerm.php,src/vendor/Root/Class.DocTitle.php,src/vendor/Root/Class.VaultDiskFsStorage.php,src/vendor/Root/Class.VaultDiskDirStorage.php,src/vendor/Root/Class.ImportDocument.php,src/vendor/Root/Class.Layout.php,src/vendor/Root/Class.QueryDir.php,src/vendor/Root/Class.Group.php,src/vendor/Root/Class.OOoLayout.php ${MK_DIR}/src

beautify:
	@${PRINT_COLOR} "${DEBUG_COLOR}Beautify PHP${RESET_COLOR}\n"
	cd ${MK_DIR}/ide; ${COMPOSER} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php --ignore=./src/vendor/Anakeen/lib,src/API/setSessionHandler.php,src/API/importDocuments.php,src/vendor/Dcp/Core/Utils/Types.php,src/vendor/Dcp/Vault/VaultAnalyzerCLI.php,src/vendor/Dcp/Vault/VaultAnalyzer.php,src/vendor/Dcp/ConsoleProgressOMeter.php,src/vendor/Dcp/Style/ParserStyle.php,src/vendor/Dcp/Style/ICssParser.php,src/vendor/Dcp/Router/NotHandler.php,src/vendor/Dcp/Utils/htmlclean.php,src/vendor/Dcp/Utils/Types.php,src/vendor/Anakeen/Core/Internal/Format/noAccessAttributeValue.php,src/vendor/Anakeen/Core/Internal/Authenticator.php,src/vendor/Anakeen/Core/Internal/SmartElement.php,src/vendor/Anakeen/Core/SmartStructure/NormalAttribute.php,src/vendor/Anakeen/Core/SmartStructure/DocEnum.php,src/vendor/Anakeen/Core/SmartStructure/Attributes.php,src/vendor/Anakeen/Core/Utils/Strings.php,src/vendor/Anakeen/Core/Utils/Gettext.php,src/vendor/Anakeen/Script/Stdio.php,src/vendor/Anakeen/Script/IStdio.php,src/vendor/Anakeen/Routes/Middleware/Log.php,src/vendor/Anakeen/Search/Filters/_OneEquals.php,src/vendor/Anakeen/Search/Filters/Exception.php,src/vendor/Anakeen/Search/Filters/OneContains.php,src/vendor/Anakeen/Search/Filters/OneGreaterThan.php,src/vendor/Anakeen/Search/Filters/OneLesserThan.php,src/vendor/Anakeen/FDL/Lib.Vault.php,src/vendor/Anakeen/FDL/LegacyDocManager.php,src/vendor/Anakeen/FDL/Lib.Util.php,src/vendor/Anakeen/SmartStructures/Mailtemplate/MailTemplateHooks.php,src/vendor/Anakeen/SmartStructures/Group/GroupHooks.php,src/vendor/Root/Class.DocLDAP.php,src/vendor/Root/Class.DocCollection.php,src/vendor/Root/Class.SearchHighlight.php,src/vendor/Root/Class.VGroup.php,src/vendor/Root/Class.processExecuteAPI.php,src/vendor/Root/ErrorCodePMGT.php,src/vendor/Root/CheckDoc.php,src/vendor/Root/EnumAttributeTools.php,src/vendor/Root/Class.DocVaultIndex.php,src/vendor/Root/CheckKeys.php,src/vendor/Root/Class.CheckDb.php,src/vendor/Root/Class.Lang.php,src/vendor/Root/Class.InstallUtils.php,src/vendor/Root/Class.Fdl_Mail_Mime.php,src/vendor/Root/Class.Out.php,src/vendor/Root/Class.DocHtmlFormat.php,src/vendor/Root/Class.DocPerm.php,src/vendor/Root/Class.DocTitle.php,src/vendor/Root/Class.VaultDiskFsStorage.php,src/vendor/Root/Class.VaultDiskDirStorage.php,src/vendor/Root/Class.ImportDocument.php,src/vendor/Root/Class.Layout.php,src/vendor/Root/Class.QueryDir.php,src/vendor/Root/Class.Group.php,src/vendor/Root/Class.OOoLayout.php ${MK_DIR}/src
	$(CBF_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml  --ignore=Tests/src/vendor/Anakeen/TestUnits/lib/vendor/,Tests/src/Apps/FDL --extensions=php  ${MK_DIR}/Tests/src

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

app-test: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app test${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --sourcePath ./Tests

app-all: app app-test

app-autorelease: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --autoRelease

app-test-autorelease: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Make app test autotrelease${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} build --sourcePath ./Tests --autoRelease

app-all-autorelease: app-autorelease app-test-autorelease

########################################################################################################################
##
## Deploy
##
########################################################################################################################
deploy: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-test: install-deps
	@${PRINT_COLOR} "${DEBUG_COLOR}Deploy test${RESET_COLOR}\n"
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./Tests -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-all: deploy deploy-test

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
