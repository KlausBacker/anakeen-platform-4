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
-include Makefile.local

install:
	cd src/vendor/Anakeen/lib; ${COMPOSER} install --ignore-platform-reqs

app: install
	${DEVTOOL_BIN} generateWebinst -s .

app-test:
	cd Tests/src/vendor/Anakeen/TestUnits/lib; ${COMPOSER} install --ignore-platform-reqs
	${DEVTOOL_BIN} generateWebinst -s Tests
	mv Tests/*app .

deploy: app
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -a -s .

deploy-test: app-test
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -a -s Tests

po:
	${DEVTOOL_BIN} extractPo -s . -o ./src

stub:
	${DEVTOOL_BIN} generateStub -s . -o stubs/

.PHONY: app app-test deploy deploy-test po stub install