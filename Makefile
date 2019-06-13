TOPTARGETS := app app-test app-all deploy deploy-test deploy-all lint po stub

SUBDIRS := smart-data-engine security workflow internal-components user-interfaces hub-station admin-center business-app development-center transformation

$(TOPTARGETS): $(SUBDIRS)
$(SUBDIRS):
	$(MAKE) -C $@ $(MAKECMDGOALS)

.PHONY: $(TOPTARGETS) $(SUBDIRS)