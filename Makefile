CONTROL_USER= admin
CONTROL_PASSWORD= anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)
COMPOSER=composer
DEVTOOL=php ./anakeen-devtool.phar

install:
	cd src/vendor/Anakeen/lib; ${COMPOSER} install --ignore-platform-reqs

app: install
	${DEVTOOL} generateWebinst -s .

app-test:
	cd Tests/src/vendor/Anakeen/TestUnits/lib; ${COMPOSER} install --ignore-platform-reqs
	${DEVTOOL} generateWebinst -s Tests
	mv Tests/*app .

deploy:
	${DEVTOOL} deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -a -s .

deploy-test:
	${DEVTOOL} deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -a -s Tests

po:
	${DEVTOOL} extractPo -s . -o ./src

stub:
	${DEVTOOL} generateStub -s . -o stubs/

.PHONY: app app-test deploy deploy-test po stub install