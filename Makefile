port=80

webinst:
	php ./dynacase-devtool.phar generateWebinst -s .

po:
	php ./dynacase-devtool.phar extractPo -s .

deploy:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -p $(port) -a -s .
