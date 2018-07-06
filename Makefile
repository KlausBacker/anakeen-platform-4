## control conf
port=80
CONTROL_PROTOCOL=http
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
DEVTOOL_BIN=php ./anakeen-devtool.phar
-include Makefile.local

app:
	${DEVTOOL_BIN} generateWebinst -s .

po:
	${DEVTOOL_BIN} extractPo -s .

deploy:
	${DEVTOOL_BIN} deploy -u $(CONTROL_PROTOCOL)://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -a -s .

stubs:
	npx anakeen-cli generateStubs -s ./src/vendor/Anakeen/SmartStructures/FieldAccessLayer/ -t Stubs/
	npx anakeen-cli generateStubs -s ./src/vendor/Anakeen/SmartStructures/FieldAccessLayerList/ -t Stubs/
