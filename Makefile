port=80

app:
	rm -f admin-center*.app
	yarn install && yarn build
	php ./anakeen-devtool.phar generateWebinst -s .

po:
	php ./anakeen-devtool.phar extractPo -s .

deploy:
	make app
	php ./anakeen-devtool.phar deploy -u http://admin:anakeen@$(host)/control/ -c $(ctx) -p $(port) -w admin-center*.app -- --force
