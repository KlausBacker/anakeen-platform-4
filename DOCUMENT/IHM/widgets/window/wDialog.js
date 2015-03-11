define([
    'underscore',
    'dcpDocument/widgets/widget',
    'kendo/kendo.window'
], function (_) {
    'use strict';

    $.widget("dcp.dcpDialog", {
        options : {
            window : {
                modal :    true,
                actions :  [
                    "Maximize",
                    "Close"
                ],
                visible :  false,
                height :   "300px",
                maxWidth : "500px",
                title :    "-"
            }
        },

        _create : function dcpDialog_create() {
            var currentWidget = this;
            this.element.data("dcpDialog", this);
            if (!this.options.window.close) {
                this.options.window.close = function dcpDialog_onclose() {
                    _.defer(_.bind(currentWidget.destroy, currentWidget));
                };
            } else {
                this.options.window.close = _.wrap(this.options.window.close, function dcpDialog_closeWrap(close, argument) {
                    var event = arguments[1];
                    close.apply(this, _.rest(arguments));
                    if (!event.isDefaultPrevented()) {
                        _.defer(_.bind(currentWidget.destroy, currentWidget));
                    }
                });
            }

            this.element.kendoWindow(this.options.window);
        },

        open : function dcpDialog_Open() {
            var kWindow=this.element.data("kendoWindow");
            console.log("open transition");
            if ($(window).width() <= 480) {
                kWindow.setOptions({
                    actions : ["Close"]
                });
                kWindow.maximize();
                kWindow.open();
            } else {
                kWindow.setOptions({
                    actions : this.options.window.actions
                });
                kWindow.center();
                kWindow.open();
            }
        },

        close : function dcpDialog_close() {
            var kendoWindow = this.element.data("kendoWindow");
            if (kendoWindow) {
                kendoWindow.close();
            }
        },

        _destroy : function dcpDialog_destroy() {
            if (this.element && this.element.data("kendoWindow")) {
                this.element.data("kendoWindow").destroy();
            }
            this._super();
        }

    });
});