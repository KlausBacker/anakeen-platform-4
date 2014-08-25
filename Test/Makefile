include $(utildir)/PubRule

publish:
	mkdir -p $(pubdir)/$(appname)
	rsync -rupE --exclude Makefile $(appname) $(pubdir)
	sed -i s/@VERSION@/$(VERSION)/g $(pubdir)/$(appname)/$(appname)_init.php
	cp template_info.xml info.xml
	sed -i s/@VERSION@/$(VERSION)/g info.xml
	sed -i s/@RELEASE@/$(RELEASE)/g info.xml
	sed -i s/@LICENSE@/$(LICENSE)/g info.xml

clean:
	/bin/rm -f info.xml
	/bin/rm -fr autom4te.cache