include $(utildir)/PubRule
include $(localBuildRule)/localBuildRule

pages_mustache = $(patsubst %.mustache,$(pubdir)/$(style)/$(appname)/Layout/%.mustache,$(filter-out $(pages_not_mustache),$(wildcard *.mustache)))

$(pubdir)/$(style)/$(appname)/Layout/%.mustache : %.mustache $(pubdir)/$(style)/$(appname)/Layout
	$(installcp) $< $@


publish:  $(pages_mustache)
