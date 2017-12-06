port=80

app:
	php ./anakeen-devtool.phar generateWebinst -s .

po:
	php ./anakeen-devtool.phar extractPo -s .

deploy:
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -p $(port) -a -s .
