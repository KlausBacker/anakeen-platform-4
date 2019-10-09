PACKAGE_VERSION=$(shell node -p -e "require('./package.json').version")
APP_OUTPUT_PATH=../build/transformation-server/
ZIP_DIST_NAME=transformation-server
ZIP_DIST_DIR=transformation-server-$(PACKAGE_VERSION)

app:
	mkdir -p ${APP_OUTPUT_PATH}
	mkdir -p tmp/$(ZIP_DIST_DIR)
	tar -cf - \
		etc test-data lib src bin README.md package.json\
		| tar -C tmp/$(ZIP_DIST_DIR) -xf -
	rm -f ../$(ZIP_DIST_NAME)-$(PACKAGE_VERSION).zip
	cd ./tmp && zip -q -r ../$(ZIP_DIST_NAME)-$(PACKAGE_VERSION).zip $(ZIP_DIST_DIR)
	rm -Rf tmp
	pwd
	mv ./$(ZIP_DIST_NAME)-${PACKAGE_VERSION}.zip ${APP_OUTPUT_PATH}
	cd ${APP_OUTPUT_PATH} && ln -sf ./$(ZIP_DIST_NAME)-${PACKAGE_VERSION}.zip ./$(ZIP_DIST_NAME)-latest.zip
	cp ./package.json ${APP_OUTPUT_PATH}

clean:
	rm -Rf tmp
	rm -f $(APP_OUTPUT_PATH)

app-autorelease:
app-test-autorelease:
lint:
po:
app-test:
app-all: app
deploy:
deploy-test:
deploy-all:
