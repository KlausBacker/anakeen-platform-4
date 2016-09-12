webinst:
	php ./dynacase-devtool.phar generateWebinst -s .

deploy:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@localhost/control/ -c tmp32 -a -s .
