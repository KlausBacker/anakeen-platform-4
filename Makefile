## control conf
CONTROL_PROTOCOL=http
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
COMPOSER=composer
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
-include Makefile.local

node_modules:
	npm install

install: node_modules
	cd src/vendor/Anakeen/lib; ${COMPOSER} install --ignore-platform-reqs
	cd Tests/src/vendor/Anakeen/TestUnits/lib; ${COMPOSER} install --ignore-platform-reqs

app: install
	${ANAKEEN_CLI_BIN} build

app-test:
	${ANAKEEN_CLI_BIN} build --sourcePath ./Tests

autotest: install
	rm -f *app
	${ANAKEEN_CLI_BIN} build --auto-release
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./Tests

deploy:
	rm -f smart-data-engine-1*app
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath . -c $(CONTROL_PROTOCOL)://${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

deploy-test:
	rm -f smart-data-engine-test*app
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./Tests -c $(CONTROL_PROTOCOL)://${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

po:
	${ANAKEEN_CLI_BIN} extractPo -s .

stub:
	${ANAKEEN_CLI_BIN} generateStubs

.PHONY: app app-test deploy deploy-test po stub install
