/**
 * Main bootstraper
 */
/*global require*/
require([
    'underscore',
    'jquery',
    'dcpDocument/widgets/documentController/documentController'
], function (_, $) {
    'use strict';
    console.timeEnd("js loading");

    var $document = $(".document");

    $document.documentController({
        "initid" :   window.dcp.viewData.documentIdentifier,
        "viewId" :   window.dcp.viewData.vid,
        "revision" : window.dcp.viewData.revision
    });

    window.dcp.document = $document;
    if (window.documentLoaded && _.isFunction(window.documentLoaded)) {
        window.documentLoaded($document);
    }
    if (window.documentUnloaded && _.isFunction(window.documentUnloaded)) {
        window.addEventListener('unload', window.documentUnloaded);
    }
});
