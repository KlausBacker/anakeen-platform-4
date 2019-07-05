TAR_DIST_NAME=anakeen-control
TAR_DIST_DIR=anakeen/control

TAR_DIST_OPTS=--owner 0 --group 0

VERSION=$(shell head -1 VERSION)
RELEASE=$(shell head -1 RELEASE)

OBJECTS=

all:
	@echo ""
	@echo "  Available targets:"
	@echo ""
	@echo "    tarball"
	@echo "    clean"
	@echo ""

app:
	composer install
	yarn install
	mkdir -p tmp/$(TAR_DIST_DIR)
	sed -e "s/{{VR}}/$(VERSION)-$(RELEASE)/g" index-tpl.html > index.html
	tar -cf - \
		--exclude Makefile \
		--exclude tmp \
		--exclude test \
		--exclude mk.sh \
		--exclude $(TAR_DIST_NAME)-*-*.tar.gz \
		--exclude $(TAR_DIST_NAME)-*-*.autoinstall.php \
		--exclude "*~" \
		--exclude .git \
		--exclude .gitmodules \
		--exclude node_modules \
		--exclude PubRule \
		--exclude po2js.php \
		. | tar -C tmp/$(TAR_DIST_DIR) -xf -
	cp -r node_modules/bootstrap/dist tmp/$(TAR_DIST_DIR)/public/bootstrap
	cp -r node_modules/jquery/dist tmp/$(TAR_DIST_DIR)/public/jquery
	cp -r node_modules/popper.js/dist tmp/$(TAR_DIST_DIR)/public/popper
	cd tmp;zip -q -r ../$(TAR_DIST_NAME)-$(VERSION)-$(RELEASE).zip .

	rm -Rf tmp


clean:
	find . -name "*~" -exec rm -f {} \;
	rm -Rf tmp
	rm -f $(TAR_DIST_NAME)-*.tar.gz
	rm -f $(TAR_DIST_NAME)-*.autoinstall.php
