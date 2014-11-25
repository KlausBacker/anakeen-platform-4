

include $(utildir)/PubRule

$(pubdir)/$(appname)/rules.d:
	mkdir -p $@

$(pubdir)/$(appname)/rules.d/%.json : %.json $(pubdir)/$(appname)/rules.d
	$(installcp) $< $@

publish : $(pubdir)/$(appname)/rules.d/20-document-ui.json
