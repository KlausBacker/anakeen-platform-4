webinst:
	php ./dynacase-devtool.phar generateWebinst -s .

po:
	php ./dynacase-devtool.phar extractPo -s .

deploy:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@gers/control/ -c tmp32 -a -s .
