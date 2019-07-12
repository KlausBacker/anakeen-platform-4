APP_OUTPUT_PATH=../build/control/

app:
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./src && zip -q -r ../anakeen-control-$(shell node ./getVersion.js).zip ./*
	mv ./anakeen-control-$(shell node ./getVersion.js).zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-$(shell node ./getVersion.js).zip ./anakeen-control-latest.zip
	cp ./src/version.json ${APP_OUTPUT_PATH}

app-autorelease:
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build
	node ../.devtool/ci/utils/autorelease.js . "COMPUTE" "./src/version.json"
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./src && zip -q -r ../anakeen-control-$(shell node ./getVersion.js).zip ./*
	mv ./anakeen-control-$(shell node ./getVersion.js).zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-$(shell node ./getVersion.js).zip ./anakeen-control-latest.zip
	cp ./src/version.json ${APP_OUTPUT_PATH}

app-test: app
app-all: app
deploy: app
deploy-test: app
deploy-all: app
lint:
po:
stub:
	mkdir ./stubs
checkXML:
clean:
	rm -rf ${APP_OUTPUT_PATH}
	rm -fr node_modules
	rm -fr src/vendor