publish:
	rsync -rupE . $(pubdir)/$(appname)/IHM --exclude "Makefile"
	r.js -o $(pubdir)/$(appname)/IHM/build.js
	r.js -o $(pubdir)/$(appname)/IHM/widgets/buildWidget.js