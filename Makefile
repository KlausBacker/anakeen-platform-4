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
	-mkdir -p $(localpub)/Samples
	rsync --delete -azvr Samples $(localpub)
	cd $(localpub)/Samples/BusinessApp && yarn install && yarn run build
	./dynacase-devtool.phar generateWebinst -s $(localpub)/Samples/BusinessApp -o .

webinst-all: webinst webinst-selenium

webinst:
	cd ui && npm run build
	cd Document-uis && npm install && npm run build
	cd Document-uis/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; composer install
	make webinst-full

po:
	./dynacase-devtool.phar extractPo -s Document-uis

clean: 
	rm -rf $(localpub)

deploy-cool:
	rm -f *webinst
	cd Document-uis &&  npm run build
	make webinst-full
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w anakeen-document-uis-*webinst -- --force
	make clean
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
