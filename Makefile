DHOST=$(word 2, $(MAKECMDGOALS) )
DCTX=$(word 3, $(MAKECMDGOALS) )

webinst:
	cp build.json info.xml src
	php ./dynacase-devtool.phar generateWebinst -s src
	mv src/*webinst .
	rm src/build.json src/info.xml

deploy:
	cp build.json info.xml src
	php ./dynacase-devtool.phar deploy -u http://admin:anakeen@$(DHOST)/control/ -c $(DCTX) -a -s src
	rm src/build.json src/info.xml

stub:
	php ./dynacase-devtool.phar generateStub -s . -o stubs
	php ./dynacase-devtool.phar generateStub -f CCFD/Families/soutien_circuit_long/cycle_soutien_long.csv -o stubs/ -s .
	php ./dynacase-devtool.phar generateStub -f CCFD/Families/soutien_circuit_court/cycle_soutien_court.csv -o stubs/ -s .

po:
	php ./dynacase-devtool.phar extractPo -s src

webinst-all: webinst


