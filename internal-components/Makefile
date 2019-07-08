#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

##bin
YARN_BIN=yarn

app:
	$(YARN_BIN) build

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