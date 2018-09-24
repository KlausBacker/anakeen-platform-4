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
DEVTOOL_BIN=php ./anakeen-devtool.phar
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
	${ANAKEEN_CLI_BIN} build --auto-release
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c "${CONTROL_CONTEXT}" -p ${CONTROL_PORT}  -w smart-data-engine-1*app

deploy-test:
	rm -f smart-data-engine-test*app
	${ANAKEEN_CLI_BIN} build --auto-release --sourcePath ./Tests
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c "${CONTROL_CONTEXT}" -p ${CONTROL_PORT}  -w smart-data-engine-test*app

po:
	${DEVTOOL_BIN} extractPo -s . -o ./src

stub:
	${ANAKEEN_CLI_BIN} generateStubs

.PHONY: app app-test deploy deploy-test po stub install
