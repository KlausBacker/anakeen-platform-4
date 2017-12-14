VERSION = $(shell cat VERSION)
RELEASE = $(shell cat RELEASE)
localpub=$(shell pwd)/localpub
port=80

stub:
	./anakeen-devtool.phar generateStub -s Document-uis -o ./stubs/
	./anakeen-devtool.phar generateStub -s Tests -o ./stubs/
	./anakeen-devtool.phar generateStub -s Samples/BusinessApp -o ./stubs/


app-selenium:
	-mkdir -p $(localpub)/selenium/
	rsync --delete -azvr Tests $(localpub)/selenium/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/selenium/Tests/build.json $(localpub)/selenium/Tests/src/Apps/TEST_DOCUMENT_SELENIUM/TEST_DOCUMENT_SELENIUM_init.php
	./anakeen-devtool.phar generateWebinst -s $(localpub)/selenium/Tests/ -o .


deploy-test:
	rm -f *app
	make app-selenium
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w user-interfaces-test*app -- --force


app:
	cd Document-uis && yarn install
	make -f pojs.make compile
	cd Document-uis && yarn buildAsset && yarn build
	cd Document-uis/src/vendor/Anakeen/Ui/PhpLib; rm -rf ./vendor; composer install

	-mkdir -p $(localpub)/webinst
	rsync --delete -azvr --exclude 'node_modules' Document-uis $(localpub)/webinst/
	sed -i -e "s/{{VERSION}}/$(VERSION)/" -e "s/{{RELEASE}}/$(RELEASE)/" $(localpub)/webinst/Document-uis/build.json $(localpub)/webinst/Document-uis/src/Apps/DOCUMENT/DOCUMENT_init.php
	./anakeen-devtool.phar generateWebinst -s $(localpub)/webinst/Document-uis/ -o .

app-showcase:
	cd Document-uis && yarn install && yarn buildAsset
	make -f pojs.make OUTPUT_DIR=Samples/BusinessApp/src/public/BUSINESS_APP/src/components compile
	cd Samples/BusinessApp && yarn install && yarn build
	-mkdir -p $(localpub)/Samples
	rsync --delete -azvr --exclude 'node_modules' Samples $(localpub)
	./anakeen-devtool.phar generateWebinst -s $(localpub)/Samples/BusinessApp -o .


po:
	./anakeen-devtool.phar extractPo -s Document-uis -o Document-uis/src


pojs:
	make -f pojs.make
	make -f pojs.make OUTPUT_DIR=Samples/BusinessApp/src/public/BUSINESS_APP/src/components

clean: 
	rm -rf $(localpub)
	rm -f *app
	make -f pojs.make clean

mrproper: clean
	rm -rf Document-uis/node_modules
	rm -rf Samples/BusinessApp/node_modules
	rm -f *.app

deploy:
	rm -f *app
	make app
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w user-interfaces-*app -- --force
	make clean

deploy-showcase:
	rm -f showcase*app
	make app-showcase
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control --port=$(port) -c $(ctx) -w showcase-*app -- --force
	make clean
