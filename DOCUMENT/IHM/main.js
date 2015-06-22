/**
 * Main bootstraper
 */
/*global require*/
require([
    'underscore',
    'jquery',
    'dcpDocument/widgets/documentController/documentController'
], function (_, $)
{
    'use strict';
    console.timeEnd("js loading");

    var $document = $(".document");

    if (window.dcp.viewData !== false) {
        $document.documentController({
            "initid": window.dcp.viewData.documentIdentifier,
            "viewId": window.dcp.viewData.vid,
            "revision": window.dcp.viewData.revision
        });
        $document.one("documentready", function ()
        {
            // Init bind events in case of use extern document controller
            if (window.documentLoaded && _.isFunction(window.documentLoaded)) {
                window.documentLoaded($document, false);
            }
        });
    } else {
        $document.documentController();
        if (window.documentLoaded && _.isFunction(window.documentLoaded)) {
            window.documentLoaded($document, true);
        }
    }

    window.dcp.document = $document;

});
