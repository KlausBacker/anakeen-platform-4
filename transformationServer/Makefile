VERSION=$(shell node getVersion.js)
TAR_DIST_NAME=anakeen-transformation-engine
TAR_DIST_DIR=anakeen-transformation-engine-$(VERSION)


app:
	mkdir -p tmp/$(TAR_DIST_DIR)
	tar -cf - \
		etc test-data lib src bin README.md package.json\
		| tar -C tmp/$(TAR_DIST_DIR) -xf -
	rm -f ../$(TAR_DIST_NAME)-$(VERSION).zip
	cd ./tmp && zip -q -r ../$(TAR_DIST_NAME)-$(VERSION).zip $(TAR_DIST_DIR)
	rm -Rf tmp

clean:
	find . -name "*~" -exec rm -f {} \;
	rm -Rf tmp
	rm -f $(TAR_DIST_NAME)-$(VERSION).zip
	rm -f $(TAR_DIST_NAME)-$(VERSION).autoinstall.php

app-test:
app-all: app
deploy:
deploy-test:
deploy-all: