VERSION = 1.1.0
RELEASE = 2.1
localpub=$(shell pwd)/localpub


stub:
	./dynacase-devtool.phar generateStub -s Document-uis -o ./stubs/
	./dynacase-devtool.phar generateStub -s Document-uis-selenium -o ./stubs/


webinst-selenium:
	-mkdir -p $(localpub)/selenium/
	rsync --delete -azvr Tests $(localpub)/selenium/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/selenium/Tests/build.json $(localpub)/selenium/Tests/src/Apps/TEST_DOCUMENT_SELENIUM/TEST_DOCUMENT_SELENIUM_init.php
	./dynacase-devtool.phar generateWebinst -s $(localpub)/selenium/Tests/ -o .

webinst-full:
	-mkdir -p $(localpub)/webinst
	rsync --delete -azvr Document-uis $(localpub)/webinst/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/webinst/Document-uis/build.json $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/DOCUMENT_init.php
	r.js -o $(localpub)/webinst/Document-uis/src/public/uiAssets/anakeen/IHM/build.js
	php ./prepareElementForLight.php --modeFull -f $(localpub)/webinst/Document-uis/info.xml
	./dynacase-devtool.phar generateWebinst -s $(localpub)/webinst/Document-uis/ -o .


webinst-light:
	-mkdir -p $(localpub)/webinst
	rsync --delete -azvr Document-uis $(localpub)/webinst/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/webinst/Document-uis/build.json $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/DOCUMENT_init.php
	sed -i -e "s/$(PACKAGE)/$(PACKAGE)-light/" $(localpub)/webinst/Document-uis/build.json
	php ./prepareElementForLight.php -f $(localpub)/webinst/Document-uis/info.xml
	php ./prepareElementForLight.php -f $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/DOCUMENT_init.php
	php ./prepareElementForLight.php -f $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/DOCUMENT.app
	find $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/IHM/ -mindepth 2 -name "*.js" -type f -delete
	find $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/IHM/ -name "*.less" -type f -delete
	./dynacase-devtool.phar generateWebinst -s $(localpub)/webinst/Document-uis/ -o .

webinst-all: webinst webinst-selenium

webinst:
	cd ui && npm run build
	# TODO Move some assets to Document-uis/src/public
	cd Document-uis/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; composer install
	make webinst-full

po:
	./dynacase-devtool.phar extractPo -s Document-uis

clean: 
	rm -rf $(localpub)

deploy:
	rm -f *webinst
	make webinst
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w anakeen-document-uis-1*webinst -- --force
