define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (/*Mustache*/) {
    'use strict';

    $.widget("dcp.dcpWindow",  {

        options : {
            animation: {
                open: {
                    effects: "fade:in"
                }
            },
            height : "300px",
            width : "400px"
        },

        _create : function () {
            this.element.kendoWindow(this.options);
        },

        _getWindowTemplate : function (templateId) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.window && window.dcp.templates.window[templateId]) {
                return window.dcp.templates.window[templateId];
            }
            throw new Error("Unknown window template  "+templateId);
        }
    });
});