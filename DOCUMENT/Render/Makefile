SUBDIR =View Edit
include $(utildir)/PubRule

pages_4 = $(patsubst %,$(pubdir)/$(appname)/Render/%,$(wildcard *.*))

$(pubdir)/$(appname)/Render :
	mkdir -p $@

$(pubdir)/$(appname)/Render/% : % $(pubdir)/$(appname)/Render
	$(installcp) $< $@


publish:  $(pages_4)
