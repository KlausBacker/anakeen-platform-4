SUBDIR= Render Layout Images IHM

include $(utildir)/PubRule


pages_csv = $(patsubst %.csv,$(pubdir)/$(style)/$(appname)/%.csv,$(filter-out $(pages_not_csv),$(wildcard *.csv)))
pages_xml = $(patsubst %.xml,$(pubdir)/$(style)/$(appname)/%.xml,$(filter-out $(pages_not_xml),$(wildcard *.xml)))

$(pubdir)/$(applib):
	mkdir $@


$(pubdir)/$(style)/$(appname)/%.csv : %.csv $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

$(pubdir)/$(style)/$(appname)/%.xml : %.xml $(pubdir)/$(style)/$(appname)
	$(installcp) $< $@

#$(pubdir)/$(appname)/$(appname).cron: $(appname).cron $(pubdir)/$(appname)
#	cp $< $@

publish: $(pubdir)/$(applib)  $(pages_csv) $(pages_xml)
