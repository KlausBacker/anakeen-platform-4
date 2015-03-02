/**
 * Main bootstraper
 */
/*global require*/
require([
    'dcpDocument/widgets/documentController/documentController'
], function () {
    'use strict';
    console.timeEnd("js loading");

    var $document = $(".document");

    $document.documentController({
        "initid" :   window.dcp.viewData.documentIdentifier,
        "viewId" :   window.dcp.viewData.vid,
        "revision" : window.dcp.viewData.revision
    });

    window.dcp.document = $document;
});
