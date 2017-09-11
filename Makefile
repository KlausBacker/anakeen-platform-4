

webinst:
	php ./dynacase-devtool.phar generateWebinst -s .


webinst-test:
	php ./dynacase-devtool.phar generateWebinst -s Tests
	mv Tests/*webinst .

deploy:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s .


deploy-test:
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -a -s Tests

stub:
	php ./dynacase-devtool.phar generateStub -s . -o stubs
	php ./dynacase-devtool.phar generateStub -f CCFD/Families/soutien_circuit_long/cycle_soutien_long.csv -o stubs/ -s .
	php ./dynacase-devtool.phar generateStub -f CCFD/Families/soutien_circuit_court/cycle_soutien_court.csv -o stubs/ -s .

po:
	php ./dynacase-devtool.phar extractPo -s src

webinst-all: webinst


