pages_src = $(patsubst %.po,$(localedir)/en/LC_MESSAGES/src/%.po,$(wildcard DOC*.po))
pages_jssrc = $(patsubst %.po,$(localedir)/en/js/src/%.po,$(wildcard js*.po))


localedir=$(pubdir)/locale
installcp = cp -f
#include $(utildir)/PubRule



$(localedir)/en/js/src:
	mkdir -p $@
$(localedir)/en/LC_MESSAGES/src:
	mkdir -p $@


$(localedir)/en/LC_MESSAGES/src/%.po:  %.po $(localedir)/en/LC_MESSAGES/src
	 $(installcp) $< $@

$(localedir)/en/js/src/%.po:  %.po $(localedir)/en/js/src
	 $(installcp) $< $@

publish : $(pages_src) $(pages_jssrc)
