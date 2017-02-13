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

    var $document = $(".document"), currentValues, varWidgetValue="widgetValue";

    window.dcp = window.dcp || {};

    window.dcp.documentReady = false;

    if (!window.dcp.viewData && window.location.hash) {
        currentValues = window.location.hash;
        if (currentValues[0] === "#") {
            currentValues = currentValues.slice(1);
        }
        if (currentValues.indexOf(varWidgetValue) === 0) {
            try {
                window.dcp.viewData = JSON.parse(currentValues.slice(varWidgetValue.length));
            } catch(ex1) {
                try {
                    window.dcp.viewData = JSON.parse(decodeURI(currentValues.slice(varWidgetValue.length)));
                } catch(ex2) {
                    $document.documentController("showMessage", {
                        type: "error",
                        message: "unable to retrieve document"
                    });
                }
            }
        }

    }

    window.dcp.triggerReload = function triggerReload()
    {
        // Init bind events in case of use extern document controller
        if (window.documentLoaded && _.isFunction(window.documentLoaded) && !window.dcp.documentReady) {
            window.documentLoaded($document, window.dcp.viewData);
            window.dcp.documentReady = true;
        }
    };

    if (window.dcp.viewData !== false && window.dcp.viewData.initid) {
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
