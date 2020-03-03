include ../.devtool/Makefile.params.mk
DEVTOOLS_DIR = ../.devtool

app:
	$(YARN_CMD) build
app-test-autorelease:
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
beautify:
clean:
	rm -fr node_modules
