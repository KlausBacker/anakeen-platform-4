define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_) {
    'use strict';

    $.widget("dcp.dcpWindow", {

        options: {
            animation: {
                open: {
                    effects: "fade:in",
                    duration: 1000
                }, close: {
                    effects: "fade:out",
                    duration: 1000
                }
            },
            visible: false,
            height: "300px",
            width: "400px",
            /**
             * Try to add iframe title if no title is set
             */
            open: function () {
                if (!this.options.title) {
                    try {
                        var kw = this;
                        var ititte = this.element.find('iframe').contents().find("title").html();
                        if (typeof ititte === "undefined") {
                            _.defer(function () {
                                kw.element.find('iframe').on("load", function () {
                                    try {
                                        kw.setOptions({
                                            title: $(this).contents().find("title").html()
                                        });
                                    } catch (exp) {
                                    }
                                });
                            });
                        } else {
                            kw.setOptions({
                                title: $(this).contents().find("title").html()
                            });
                        }
                    } catch (exp) {
                    }
                }
            }
        },

        currentWidget: null,
        _create: function () {
            this.currentWidget = $('<div class="dialog-window"/>');
            this.element.append(this.currentWidget);
            this.element.data("dcpWindow", this);

            this.currentWidget.kendoWindow(this.options);
        },

        _getWindowTemplate: function (templateId) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.window && window.dcp.templates.window[templateId]) {
                return window.dcp.templates.window[templateId];
            }
            throw new Error("Unknown window template  " + templateId);
        },
        destroy: function () {
            this.currentWidget.data("kendoWindow").destroy();
            this._super();
        },
        open: function open() {
            this.currentWidget.data("kendoWindow").open();
        },
        close: function close() {
            this.currentWidget.data("kendoWindow").close();
        },
        kendoWindow: function kendoWindow() {
            return this.currentWidget.data("kendoWindow");
        }
    });
});