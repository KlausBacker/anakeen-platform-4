#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

## control conf
port=80
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
COMPOSER_BIN=composer
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./ide/vendor/bin/phpcbf
CS_BIN=php ./ide/vendor/bin/phpcs
-include Makefile.local

node_modules:
	yarn install

install: node_modules

app: install
	${ANAKEEN_CLI_BIN} build

app-test: node_modules
	${ANAKEEN_CLI_BIN} build --sourcePath ./Tests

autotest: install
	rm -f *app
	${ANAKEEN_CLI_BIN} build --auto-release
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./Tests

deploy: install
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./ -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-test: install
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./Tests -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

po: node_modules
	${ANAKEEN_CLI_BIN} extractPo -s .

beautify:
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}src

lint:
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}/src

checkXML: node_modules
	${ANAKEEN_CLI_BIN} check -s .

stub: node_modules
	${ANAKEEN_CLI_BIN} generateStubs

.PHONY: app app-test deploy deploy-test po stub install
