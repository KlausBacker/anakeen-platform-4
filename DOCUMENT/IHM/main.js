/**
 * Main bootstraper
 */
/*global require*/
require([
    'widgets/documentController/internal'
], function () {
    'use strict';
    console.timeEnd("js loading");

    var $div = $("<div></div>");

    $("body").append($div);

    $div.documentInternal({
        "initid" :   window.dcp.viewData.documentIdentifier,
        "viewId" :   window.dcp.viewData.vid,
        "revision" : window.dcp.viewData.revision
    });

    window.dcp.documentController = $div;
});
