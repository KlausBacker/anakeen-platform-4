APP_OUTPUT_PATH=../build/control/
VERSION=$(shell node ./getVersion.js)

tmp:
	mkdir tmp
	cd ./tmp && ln -s ../src control && cd -


app: tmp
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./tmp && zip -q -r ../anakeen-control-${VERSION}.zip ./*
	rm -r ./tmp
	mv ./anakeen-control-${VERSION}.zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-${VERSION}.zip ./anakeen-control-latest.zip
	cp ./src/version.json ${APP_OUTPUT_PATH}

app-autorelease: tmp
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build
	node ../.devtool/ci/utils/autorelease.js . "COMPUTE" "./src/version.json"
	VERSION=$(shell node ./getVersion.js)
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./tmp && zip -q -r ../anakeen-control-${VERSION}.zip ./*
	rm -r ./tmp
	mv ./anakeen-control-${VERSION}.zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-${VERSION}.zip ./anakeen-control-latest.zip
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