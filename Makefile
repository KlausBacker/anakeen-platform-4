

app:
	cd src/vendor/Anakeen/lib; composer install
	php ./anakeen-devtool.phar generateWebinst -s .


app-test:
	php ./anakeen-devtool.phar generateWebinst -s Tests
	mv Tests/*app .

deploy:
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s .


deploy-test:
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s Tests


po:
	php ./anakeen-devtool.phar extractPo -s . -o ./src

stub:
	php ./anakeen-devtool.phar generateStub -s . -o stubs/




