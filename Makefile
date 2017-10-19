port=80

webinst:
	php ./dynacase-devtool.phar generateWebinst -s .

webinst-test:
	php ./dynacase-devtool.phar generateWebinst -s Tests
	mv Tests/*webinst .

po:
	php ./dynacase-devtool.phar extractPo -s .

deploy:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -p $(port) -a -s .


deploy-test:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s Tests
