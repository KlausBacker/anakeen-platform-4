CONTROL_PORT=80
CONTROL_USER= admin
CONTROL_PASSWORD= anakeen
CONTROL_URL=$(host)/control/
CONTROL_CONTEXT=$(ctx)
YARN=yarn
DEVTOOL=php ./anakeen-devtool.phar

install:
	${YARN} install

app: install
	rm -f admin-center*.app
	${YARN} build
	${DEVTOOL} generateWebinst -s .

po:
	${DEVTOOL} extractPo -s .

deploy: app
	${DEVTOOL} deploy -u http://${CONTROL_USER}:${CONTROL_PASSWORD}@${CONTROL_URL} -c ${CONTROL_CONTEXT} -p ${CONTROL_PORT} -w admin-center*.app -- --force

.PHONY: app po deploy install