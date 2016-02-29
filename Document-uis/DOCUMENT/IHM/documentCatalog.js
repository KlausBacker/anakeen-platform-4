window.dcp = window.dcp || {};

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(["dcpDocument/i18n", "text!dcpContextRoot/api/v1/i18n/DOCUMENT"], factory);
    } else {
        //Load translation and load i18n handler for non require project
        $.get("api/v1/i18n/DOCUMENT").done(function translationLoaded(catalog) {
            factory(window.dcp.i18n, catalog);
            //Trigger an event when translation loaded
            $(window).trigger("documentCatalogLoaded");
        });
    }
}(window, function documentCatalog(i18n, catalog)
{
    "use strict";
    //Register document translation in the global window.dcp.documentCatalog
    window.dcp.documentCatalog = i18n(catalog);
    return window.dcp.documentCatalog;
}));
