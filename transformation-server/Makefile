PACKAGE_VERSION=$(shell node -p -e "require('./package.json').version")
APP_OUTPUT_PATH=../build/transformation-server/
ZIP_DIST_NAME=transformation-server

tmp:
	mkdir -p ${APP_OUTPUT_PATH}
	mkdir -p tmp/

copyFiles: tmp
	tar -cf - etc test-data lib src bin README.md package.json | tar -C tmp/ -xf -

autoRelease: copyFiles
	node ../.devtool/ci/utils/autorelease.js . "COMPUTE" tmp/package.json

app: copyFiles
	rm -f ../$(ZIP_DIST_NAME)-$(PACKAGE_VERSION).zip
	cd ./tmp && echo "$(PACKAGE_VERSION)" > VERSION && echo "0" > RELEASE && zip -q -r ../$(ZIP_DIST_NAME)-$(PACKAGE_VERSION).zip ./*
	mv ./$(ZIP_DIST_NAME)-${PACKAGE_VERSION}.zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./$(ZIP_DIST_NAME)-${PACKAGE_VERSION}.zip ./$(ZIP_DIST_NAME)-latest.zip
	cp tmp/package.json ${APP_OUTPUT_PATH}
	rm -Rf tmp

app-autorelease: autoRelease
	$(eval PACKAGE_VERSION_AUTO_RELEASE := $(shell node ./getVersion.js))
	rm -f ../$(ZIP_DIST_NAME)-$(PACKAGE_VERSION_AUTO_RELEASE).zip
	cd ./tmp && echo "$(PACKAGE_VERSION_AUTO_RELEASE)" > VERSION && echo "0" > RELEASE && zip -q -r ../$(ZIP_DIST_NAME)-$(PACKAGE_VERSION_AUTO_RELEASE).zip ./*
	mv ./$(ZIP_DIST_NAME)-${PACKAGE_VERSION_AUTO_RELEASE}.zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./$(ZIP_DIST_NAME)-$(PACKAGE_VERSION_AUTO_RELEASE).zip ./$(ZIP_DIST_NAME)-latest.zip
	cp tmp/package.json ${APP_OUTPUT_PATH}
	rm -rf tmp

clean:
	rm -Rf tmp
	rm -f $(APP_OUTPUT_PATH)

app-test-autorelease:
lint:
po:
app-test:
app-all: app
deploy:
deploy-test:
deploy-all:
