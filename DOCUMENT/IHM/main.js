/**
 * Main bootstraper
 */
/*global require*/
require([
    'widgets/documentController/internal'
], function () {
    'use strict';
    console.timeEnd("js loading");

    var $document = $(".document");

    $document.documentInternal({
        "initid" :   window.dcp.viewData.documentIdentifier,
        "viewId" :   window.dcp.viewData.vid,
        "revision" : window.dcp.viewData.revision
    });

    window.dcp.documentController = $document;
});
