## control conf
port=80
CONTROL_PROTOCOL=http
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
COMPOSER=composer
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./ide/vendor/bin/phpcbf
-include Makefile.local

node_modules:
	yarn install

install: node_modules

app: install
	${ANAKEEN_CLI_BIN} build

app-test:
	${ANAKEEN_CLI_BIN} build --sourcePath ./Tests

autotest: install
	rm -f *app
	${ANAKEEN_CLI_BIN} build --auto-release
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./Tests

deploy: install
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./ -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-test: install
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./Tests -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

po:
	${ANAKEEN_CLI_BIN} extractPo -s .

beautify:
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}src
	$(CBF_BIN) --standard=${MK_DIR}ide/anakeenPhpCs.xml --extensions=php ${MK_DIR}Tests/src

stub:
	${ANAKEEN_CLI_BIN} generateStubs

.PHONY: app app-test deploy deploy-test po stub install
