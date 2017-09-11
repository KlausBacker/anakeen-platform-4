VERSION = 1.1.0
RELEASE = 2.1
localpub=$(shell pwd)/localpub


stub:
	./dynacase-devtool.phar generateStub -s Document-uis -o ./stubs/
	./dynacase-devtool.phar generateStub -s Document-uis-selenium -o ./stubs/


webinst-selenium:
	-rm -fr $(localpub)
	cp -ra Tests $(localpub)
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/build.json $(localpub)/src/Apps/TEST_DOCUMENT_SELENIUM/TEST_DOCUMENT_SELENIUM_init.php
	./dynacase-devtool.phar generateWebinst -s $(localpub) -o .
	-rm -fr $(localpub)


webinst-full:
	-rm -fr $(localpub)
	cp -ra Document-uis $(localpub)
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/build.json $(localpub)/src/Apps/DOCUMENT/DOCUMENT_init.php
	r.js -o $(localpub)/src/Apps/DOCUMENT/IHM/build.js
	r.js -o $(localpub)/src/Apps/DOCUMENT/IHM/widgets/buildWidget.js
	php ./prepareElementForLight.php --modeFull -f $(localpub)/info.xml
	./dynacase-devtool.phar generateWebinst -s $(localpub) -o .
	-rm -fr $(localpub)


webinst-light: 
	-rm -fr $(localpub)
	cp -ra Document-uis $(localpub)
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/build.json $(localpub)/src/Apps/DOCUMENT/DOCUMENT_init.php
	sed -i -e "s/$(PACKAGE)/$(PACKAGE)-light/" $(localpub)/build.json
	php ./prepareElementForLight.php -f $(localpub)/info.xml
	php ./prepareElementForLight.php -f $(localpub)/src/Apps/DOCUMENT/DOCUMENT_init.php
	php ./prepareElementForLight.php -f $(localpub)/src/Apps/DOCUMENT/DOCUMENT.app
	find $(localpub)/src/Apps/DOCUMENT/IHM/ -mindepth 2 -name "*.js" -type f -delete
	find $(localpub)/src/Apps/DOCUMENT/IHM/ -name "*.less" -type f -delete
	./dynacase-devtool.phar generateWebinst -s $(localpub) -o .
	-rm -fr $(localpub)

webinst-all: webinst webinst-selenium

webinst:
	cd ui; npm install
	# TODO Move some assets to Document-uis/src/public
	cd Document-uis/src/vendor/Anakeen/Ui/PhpLib; composer install
	make webinst-full

po:
	./dynacase-devtool.phar extractPo -s Document-uis

clean: 
	/bin/rm -f *.*~ config.* Makefile configure \
		Document-uis/DOCUMENT/IHM/main-built.js	\
		Document-uis/DOCUMENT/IHM/main-built.js.map \
		Document-uis/DOCUMENT/IHM/widgets/mainWidget-min.js \
		Document-uis/DOCUMENT/IHM/widgets/mainWidget-min.js.map \
		 *.webinst
	/bin/rm -fr autom4te.cache



deploy:
	rm -f *webinst
	cd ui; npm run build
	make webinst
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx)  -w anakeen-document-uis-1*webinst -- --force
