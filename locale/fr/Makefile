pages_src = $(patsubst %.po,$(localedir)/fr/LC_MESSAGES/src/%.po,$(wildcard DOC*.po))
pages_jssrc = $(patsubst %.po,$(localedir)/fr/js/src/%.po,$(wildcard js*.po))


localedir=$(pubdir)/locale
installcp = cp -f
#include $(utildir)/PubRule



$(localedir)/fr/js/src:
	mkdir -p $@
$(localedir)/fr/LC_MESSAGES/src:
	mkdir -p $@


$(localedir)/fr/LC_MESSAGES/src/%.po:  %.po $(localedir)/fr/LC_MESSAGES/src
	 $(installcp) $< $@

$(localedir)/fr/js/src/%.po:  %.po $(localedir)/fr/js/src
	 $(installcp) $< $@

publish : $(pages_src) $(pages_jssrc)
