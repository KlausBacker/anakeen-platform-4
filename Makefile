VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)
localpub=$(shell pwd)/localpub
port=80

stub:
	php ./anakeen-devtool.phar generateStub -s anakeen-ui -o ./stubs/
	php ./anakeen-devtool.phar generateStub -s Tests -o ./stubs/


app-selenium:
	yarn install
	yarn buildTest
	-mkdir -p $(localpub)/selenium/
	rsync --delete -azvr Tests $(localpub)/selenium/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/selenium/Tests/build.json $(localpub)/selenium/Tests/src/Apps/TEST_DOCUMENT_SELENIUM/TEST_DOCUMENT_SELENIUM_init.php
	php ./anakeen-devtool.phar generateWebinst -s $(localpub)/selenium/Tests/ -o .


deploy-test:
	rm -f *app
	make app-selenium
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w user-interfaces-test*app -- --force


app:
	rm -f user-interfaces-*.app
	yarn install
	make -f pojs.make compile
	yarn buildAsset && yarn build && yarn buildFamily
	cd anakeen-ui/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; composer install
	-mkdir -p $(localpub)/webinst
	rsync --delete -azvr anakeen-ui $(localpub)/webinst/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/webinst/anakeen-ui/build.json $(localpub)/webinst/anakeen-ui/src/Apps/DOCUMENT/DOCUMENT_init.php
	php ./anakeen-devtool.phar generateWebinst -s $(localpub)/webinst/anakeen-ui/ -o .


po:
	php ./anakeen-devtool.phar extractPo -s anakeen-ui -o anakeen-ui/src


pojs:
	make -f pojs.make

clean: 
	rm -rf $(localpub)
	rm -f *app
	make -f pojs.make clean

mrproper: clean
	rm -rf node_modules
	rm -f *.app

deploy:
	make app
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w user-interfaces-*app -- --force
	make clean
