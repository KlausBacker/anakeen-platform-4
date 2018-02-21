window.dcp = window.dcp || {};

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(["dcpDocument/i18n/translatorFactory"], factory);
    } else {
        //Load translation and load i18n handler for non require project
        $.get("api/v2/i18n/DOCUMENT").done(function translationLoaded(catalog) {
            window.dcp.i18n = catalog;
            factory(window.dcp.translatorFactory);
            //Trigger an event when translation loaded
            $(window).trigger("documentCatalogLoaded");
        });
    }
}(window, function documentCatalog(translatorFactory)
{
    "use strict";
    //Register document translation in the global window.dcp.documentCatalog
    window.dcp.documentCatalog = translatorFactory(window.dcp.i18n);
    return window.dcp.documentCatalog;
}));
