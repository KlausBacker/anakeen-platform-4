port=80

app:
	php ./anakeen-devtool.phar generateWebinst -s .

app-test:
	php ./anakeen-devtool.phar generateWebinst -s Tests
	mv Tests/*app .

stub:
	php ./anakeen-devtool.phar generateStub -s . -o stubs/

po:
	php ./anakeen-devtool.phar extractPo -s .

deploy:
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -p $(port) -a -s .


deploy-test:
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s Tests
