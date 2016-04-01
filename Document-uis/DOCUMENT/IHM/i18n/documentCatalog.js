window.dcp = window.dcp || {};

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(["dcpDocument/i18n/translatorFactory", "text!dcpContextRoot/api/v1/i18n/DOCUMENT"], factory);
    } else {
        //Load translation and load i18n handler for non require project
        $.get("api/v1/i18n/DOCUMENT").done(function translationLoaded(catalog) {
            factory(window.dcp.translatorFactory, catalog);
            //Trigger an event when translation loaded
            $(window).trigger("documentCatalogLoaded");
        });
    }
}(window, function documentCatalog(translatorFactory, catalog)
{
    "use strict";
    //Register document translation in the global window.dcp.documentCatalog
    window.dcp.documentCatalog = translatorFactory(catalog);
    return window.dcp.documentCatalog;
}));
