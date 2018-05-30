
LOCALES=en fr
#OUTPUT_DIR=anakeen-ui/src/vendor/Anakeen/Components/Authent
OUTPUT_DIR=anakeen-ui/src/vendor/Anakeen/Components
COMPONENTS_DIRS= $(shell find $(OUTPUT_DIR)/* -maxdepth 0 -type d)
COMPONENTS_NAME= $(notdir $(COMPONENTS_DIRS))
TEMPLATES= $(COMPONENTS_NAME:%=./tmpot/%/template.pot)
GETTEXT_HTML_SOURCES = $(shell find $(OUTPUT_DIR) -name '*.vue' -o -name '*.html' 2> /dev/null)
GETTEXT_JS_SOURCES =   $(shell find $(OUTPUT_DIR) -name '*.vue' -o -name '*.js')

# Name of the generated .po files for each available locale.
#LOCALE_FILES ?= $(foreach dir, $(COMPONENTS_DIRS), $(patsubst %,$(dir)/locale/%/LC_MESSAGES/app.po,$(LOCALES)))
LOCALE_FILES ?= $(shell find $(OUTPUT_DIR) -name 'app.po' )

define get_html_sources
	find $(OUTPUT_DIR)/$(1) -name '*.vue' -o -name '*.html' 2> /dev/null
endef

define get_js_sources
	find $(OUTPUT_DIR)/$(1) -name '*.vue' -o -name '*.js' 2> /dev/null
endef

.PHONY: po stub pojs

pojs: $(TEMPLATES)

./tmpot/%/template.pot: $(GETTEXT_HTML_SOURCES) $(GETTEXT_JS_SOURCES)
	$(eval GETTEXT_HTML_SOURCES=$(shell find $(OUTPUT_DIR)/$* -name '*.vue' -o -name '*.html' 2> /dev/null))
	$(eval GETTEXT_JS_SOURCES=$(shell find $(OUTPUT_DIR)/$* -name '*.vue' -o -name '*.js' 2> /dev/null))
# `dir` is a Makefile built-in expansion function which extracts the directory-part of `$@`.
# `$@` is a Makefile automatic variable: the file name of the target of the rule.
# => `mkdir -p /tmpot/`
	mkdir -p $(dir $@)

# Extract gettext strings from templates files and create a POT dictionary template.
	@if [ "$(GETTEXT_HTML_SOURCES)" != "" ]; then \
	   ./node_modules/.bin/gettext-extract --attribute v-translate --quiet --output $@ $(GETTEXT_HTML_SOURCES); \
	else \
		./node_modules/.bin/gettext-extract --attribute v-translate --quiet --output $@; \
	fi

# Extract gettext strings from JavaScript files.
	@if [ "$(GETTEXT_JS_SOURCES)" != "" ]; then \
		xgettext --language=JavaScript --keyword=npgettext:1c,2,3 \
		--from-code=utf-8 --join-existing --no-wrap \
		--package-name=$(shell node -e "console.log(require('./package.json').name);") \
		--package-version=$(shell node -e "console.log(require('./package.json').version);") \
		--output $@  $(GETTEXT_JS_SOURCES); \
	fi
# Generate .po files for each available language and for each component.
	@if [ -f "$@" ]; then \
		for lang in $(LOCALES); do \
			export PO_FILE=$(OUTPUT_DIR)/$*/locale/$$lang/LC_MESSAGES/app.po; \
			echo "msgmerge --update $$PO_FILE $@"; \
			mkdir -p $$(dirname $$PO_FILE); \
			[ -f $$PO_FILE ] && msgmerge --lang=$$lang --update $$PO_FILE $@ || msginit --no-translator --locale=$$lang --input=$@ --output-file=$$PO_FILE; \
			msgattrib --no-wrap --no-obsolete -o $$PO_FILE $$PO_FILE; \
		done; \
	fi

clean: 
	rm -fr ./tmpot/ $(OUTPUT_DIR)/translation.json

compile: $(OUTPUT_DIR)/translation.json

$(OUTPUT_DIR)/translation.json: $(LOCALE_FILES)
	mkdir -p  $(dir $@)
	./node_modules/.bin/gettext-compile --output $@ $(LOCALE_FILES)
