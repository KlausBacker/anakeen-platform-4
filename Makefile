## control conf
port=80
CONTROL_PORT=$(port)
CONTROL_USER=admin
CONTROL_PASSWORD=anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)

##bin
ANAKEEN_CLI_BIN=npx @anakeen/anakeen-cli
CBF_BIN=php ./ide/vendor/bin/phpcbf
-include Makefile.local

app:
	${ANAKEEN_CLI_BIN} build

po:
	${ANAKEEN_CLI_BIN} extractPo -s .

deploy:
	${ANAKEEN_CLI_BIN} deploy --auto-release --sourcePath ./ -c ${CONTROL_URL} -u ${CONTROL_USER} -p ${CONTROL_PASSWORD} --context ${CONTROL_CONTEXT}

stub:
	npx anakeen-cli generateStubs
