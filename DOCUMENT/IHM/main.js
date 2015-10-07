/**
 * Main bootstraper
 */
/*global require, console*/
require([
    'underscore',
    'jquery',
    'dcpDocument/widgets/documentController/documentController'
], function require_main(_, $)
{
    'use strict';
    console.timeEnd("js loading");

    var $document = $(".document");

    window.dcp = window.dcp || {};

    window.dcp.documentReady = false;

    window.dcp.triggerReload = function triggerReload()
    {
        // Init bind events in case of use extern document controller
        if (window.documentLoaded && _.isFunction(window.documentLoaded) && !window.dcp.documentReady) {
            window.documentLoaded($document, !window.dcp.viewData);
            window.dcp.documentReady = true;
        }
    };

    if (window.dcp.viewData !== false) {
        $document.documentController(window.dcp.viewData);
        $document.one("documentready", function launchReady()
        {
            window.dcp.triggerReload();
        });
    } else {
        $document.documentController();
        window.dcp.triggerReload();
    }

    window.dcp.document = $document;

});
