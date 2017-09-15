

webinst:
	php ./dynacase-devtool.phar generateWebinst -s .


webinst-test:
	php ./dynacase-devtool.phar generateWebinst -s Tests
	mv Tests/*webinst .

deploy:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s .


deploy-test:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s Tests


po:
	php ./dynacase-devtool.phar extractPo -s src

stub:
	php ./dynacase-devtool.phar generateStub -s . -o stubs/


webinst-all: webinst


