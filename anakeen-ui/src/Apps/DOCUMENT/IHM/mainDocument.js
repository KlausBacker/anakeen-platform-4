/**
 * Main bootstraper
 */
/*global require, console*/
import "./loading/loading.css";
import $ from "jquery";

$.get("api/v1/i18n/DOCUMENT").done(function translationLoaded(catalog) {

    //Trigger an event when translation loaded
    window.dcp.i18n = catalog;
    const _ = require('underscore');
    require('dcpDocument/widgets/documentController/documentController');
    require('kendo');

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
            _.each(window.dcp.messages, function (msg) {
                $document.documentController("showMessage", {
                    type: msg.type,
                    message: msg.contentText
                });
            });
        });
    } else {
        $document.documentController();
        window.dcp.triggerReload();
    }

    window.dcp.document = $document;

});
