APP_OUTPUT_PATH=../build/control/

tmp:
	mkdir tmp
	cd ./tmp && ln -s ../src control


app: tmp
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./tmp && zip -q -r ../anakeen-control-$(shell node ./getVersion.js).zip ./*
	rm -r ./tmp
	mv ./anakeen-control-$(shell node ./getVersion.js).zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-$(shell node ./getVersion.js).zip ./anakeen-control-latest.zip
	cp ./src/version.json ${APP_OUTPUT_PATH}

app-autorelease: tmp
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build
	node ../.devtool/ci/utils/autorelease.js . "COMPUTE" "./src/version.json"
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./tmp && zip -q -r ../anakeen-control-$(shell node ./getVersion.js).zip ./*
	rm -r ./tmp
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
	rm -fr tmp