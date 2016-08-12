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

    var $document = $(".document"), currentValues;

    window.dcp = window.dcp || {};

    window.dcp.documentReady = false;

    if (!window.dcp.viewData && window.location.hash) {
        currentValues = window.location.hash;
        if (currentValues[0] === "#") {
            currentValues = currentValues.slice(1);
        }
        try {
            if (currentValues.slice(0, 9) === "initValue") {
                window.dcp.viewData = JSON.parse(currentValues.slice(9));
            }
        } catch(e) {}

    }

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
