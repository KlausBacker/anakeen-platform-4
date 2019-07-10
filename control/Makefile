app:
	composer --working-dir=./src install
	yarn install --frozen-lockfile
	yarn run build

app-autorelease: app
app-test: app
app-all: app
deploy: app
deploy-test: app
deploy-all: app
lint:
po:
stub:
checkXML:
clean:
	rm -fr node_modules
	rm -fr src/vendor