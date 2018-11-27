#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

## control conf
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

compile:
	yarn install

app: compile
	${ANAKEEN_CLI_BIN} build

po: compile
	${ANAKEEN_CLI_BIN} extractPo -s .

deploy:	compile
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./ -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

stub: compile
	${ANAKEEN_CLI_BIN} generateStubs

beautify:
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}/src

lint:
	cd ${MK_DIR}/ide; ${COMPOSER_BIN} install --ignore-platform-reqs
	cd ${MK_DIR}
	$(CS_BIN) --standard=${MK_DIR}/ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}/src

autotest: compile
	rm -f *app
	${ANAKEEN_CLI_BIN} build --auto-release

checkXML: compile
	${ANAKEEN_CLI_BIN} check -s .
