include $(utildir)/PubRule

publish:
	mkdir -p $(pubdir)/$(appname)
	rsync -rupE --exclude Makefile $(appname) $(pubdir)

clean:
	/bin/rm -f info.xml
	/bin/rm -fr autom4te.cache
