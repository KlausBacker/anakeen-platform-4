APP_OUTPUT_PATH=../build/control/
PACKAGE_VERSION=$(shell node -p -e "require('./package.json').version")

tmp:
	mkdir tmp
	cd ./tmp && ln -s ../src control && cd -

install:
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build

app: tmp install
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./tmp && zip -q -r ../anakeen-control-${PACKAGE_VERSION}.zip ./*
	rm -r ./tmp
	mv ./anakeen-control-${PACKAGE_VERSION}.zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-${VERSION}.zip ./anakeen-control-latest.zip
	cp ./src/version.json ${APP_OUTPUT_PATH}

compute-version:
	node ../.devtool/ci/utils/autorelease.js . "COMPUTE" "./src/version.json"

app-autorelease: tmp install compute-version
	$(eval AUTO_VERSION := $(shell node ./getVersion.js))
	mkdir -p ${APP_OUTPUT_PATH}
	cd ./tmp && zip -q -r ../anakeen-control-$(AUTO_VERSION).zip ./*
	rm -r ./tmp
	mv ./anakeen-control-$(AUTO_VERSION).zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./anakeen-control-$(AUTO_VERSION).zip ./anakeen-control-latest.zip
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
	rm -f src/version.json
	rm -fr tmp