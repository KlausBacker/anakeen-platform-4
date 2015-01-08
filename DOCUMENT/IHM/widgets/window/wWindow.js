define([
    'underscore',
    'widgets/widget',
    'kendo/kendo.window'
], function (_) {
    'use strict';

    $.widget("dcp.dcpWindow", {

        options : {
            animation : {
                open :     {
                    effects :  "fade:in",
                    duration : 1000
                }, close : {
                    effects :  "fade:out",
                    duration : 1000
                }
            },
            actions :   [
                "Maximize",
                "Close"
            ],
            visible :   false,
            height :    "300px",
            width :     "400px",
            /**
             * Try to add iframe title if no title is set
             */
            open :      function () {
                if (!this.options.title) {
                    try {
                        var kendoWindow = this;
                        var iframeTitle = this.element.find('iframe').contents().find("title").html();
                        if (typeof iframeTitle === "undefined") {
                            _.defer(function () {
                                kendoWindow.element.find('iframe').on("load", function () {
                                    try {
                                        kendoWindow.setOptions({
                                            title : $(this).contents().find("title").html()
                                        });
                                    } catch (exp) {
                                    }
                                });
                            });
                        } else {
                            kendoWindow.setOptions({
                                title : $(this).contents().find("title").html()
                            });
                        }
                    } catch (exp) {
                    }
                }
            }
        },

        currentWidget : null,
        _create :       function () {
            this.currentWidget = $('<div class="dialog-window"/>');
            this.element.append(this.currentWidget);
            this.element.data("dcpWindow", this);

            this.currentWidget.kendoWindow(this.options);
        },

        _getWindowTemplate : function (templateId) {
            if (this.options.templateData && this.options.templateData.templates &&
                this.options.templateData.templates.window && this.options.templateData.templates.window[templateId]) {
                return this.options.templateData.templates.window[templateId];
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates.window && window.dcp.templates.window[templateId]) {
                return window.dcp.templates.window[templateId];
            }
            throw new Error("Unknown window template  " + templateId);
        },
        destroy :            function wWindowDestroy () {
            if (this.currentWidget && this.currentWidget.data("kendoWindow")) {
                this.currentWidget.data("kendoWindow").destroy();
            }
            this._super();
        },
        open :               function wWindowopen() {
            this.currentWidget.data("kendoWindow").open();
        },
        close :              function close() {
            this.currentWidget.data("kendoWindow").close();
        },
        kendoWindow :        function wWindowkendoWindow() {
            return this.currentWidget.data("kendoWindow");
        }
    });
});