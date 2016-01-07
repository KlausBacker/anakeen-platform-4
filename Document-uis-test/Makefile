include $(utildir)/PubRule

$(pubdir)/$(appname)/karma: karma $(pubdir)/$(appname)
	cp -pR $< $@

publish: $(pubdir)/$(appname)/karma
	mkdir -p $(pubdir)/$(appname)
	rsync -rupE --exclude Makefile $(appname) $(pubdir)

clean:
	/bin/rm -f info.xml
	/bin/rm -fr autom4te.cache
