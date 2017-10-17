VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)
localpub=$(shell pwd)/localpub
port=80

stub:
	./dynacase-devtool.phar generateStub -s Document-uis -o ./stubs/
	./dynacase-devtool.phar generateStub -s Document-uis-selenium -o ./stubs/


webinst-selenium:
	-mkdir -p $(localpub)/selenium/
	rsync --delete -azvr Tests $(localpub)/selenium/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/selenium/Tests/build.json $(localpub)/selenium/Tests/src/Apps/TEST_DOCUMENT_SELENIUM/TEST_DOCUMENT_SELENIUM_init.php
	./dynacase-devtool.phar generateWebinst -s $(localpub)/selenium/Tests/ -o .


deploy-test:
	rm -f *webinst
	make webinst-selenium
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w anakeen-ui-test*webinst -- --force


webinst-full:
	-mkdir -p $(localpub)/webinst
	rsync --delete -azvr Document-uis $(localpub)/webinst/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/webinst/Document-uis/build.json $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/DOCUMENT_init.php
	r.js -o $(localpub)/webinst/Document-uis/src/public/uiAssets/anakeen/IHM/build.js
	r.js -o $(localpub)/webinst/Document-uis/src/public/DOCUMENT_GRID_HTML5/widgets/builder.js
	./dynacase-devtool.phar generateWebinst -s $(localpub)/webinst/Document-uis/ -o .

webinst-business:
	cd ui && yarn install && yarn run build
	-mkdir -p $(localpub)/Samples
	rsync --delete -azvr Samples $(localpub)
	cd $(localpub)/Samples/BusinessApp && yarn install && yarn run build
	./dynacase-devtool.phar generateWebinst -s $(localpub)/Samples/BusinessApp -o .

webinst-all: webinst webinst-selenium

webinst:
	cd ui && yarn install && yarn run build
	cd Document-uis && yarn install
	make -f pojs.make compile
	cd Document-uis && yarn run build
	cd Document-uis/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; composer install

	make webinst-full

po:
	./dynacase-devtool.phar extractPo -s Document-uis


pojs:
	make -f pojs.make

clean: 
	rm -rf $(localpub)

deploy:
	rm -f *webinst
	make webinst
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w anakeen-document-uis-*webinst -- --force
	make clean

deploy-business:
	rm -f sample*webinst
	make webinst-business
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w sample-business-*webinst -- --force
	make clean
