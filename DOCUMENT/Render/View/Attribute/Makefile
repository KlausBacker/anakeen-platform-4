include $(utildir)/PubRule

subpub=Render/View/Attribute

pages_4 = $(patsubst %,$(pubdir)/$(appname)/$(subpub)/%,$(wildcard *.*))

$(pubdir)/$(appname)/$(subpub) :
	mkdir -p $@

$(pubdir)/$(appname)/$(subpub)/% : % $(pubdir)/$(appname)/$(subpub)
	$(installcp) $< $@


publish:  $(pages_4)