define([
    'underscore',
    'widgets/widget',
    'kendo/kendo.window'
], function (_) {
    'use strict';

    $.widget("dcp.dcpTransitionWindow", {
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

        _create : function dcpTransitionWindow_create() {
            var currentWidget = this;
            this.element.data("dcpTransitionWindow", this);
            if (!this.options.window.close) {
                this.options.window.close = function dcpTransitionWindow_onclose() {
                    _.defer(_.bind(currentWidget.destroy, currentWidget));
                };
            } else {
                this.options.window.close = _.wrap(this.options.window.close, function dcpTransitionWindow_closeWrap(close, argument) {
                    var event = arguments[1];
                    close.apply(this, _.rest(arguments));
                    if (!event.isDefaultPrevented()) {
                        _.defer(_.bind(currentWidget.destroy, currentWidget));
                    }
                });
            }

            this.element.kendoWindow(this.options.window);
        },

        open : function dcpTransitionWindow_Open() {
            this.element.data("kendoWindow").center();
            this.element.data("kendoWindow").open();
        },

        close : function dcpTransitionWindow_close() {
            var kendoWindow = this.element.data("kendoWindow");
            if (kendoWindow) {
                kendoWindow.close();
            }
        },

        _destroy : function dcpTransitionWindow_destroy() {
            if (this.element && this.element.data("kendoWindow")) {
                this.element.data("kendoWindow").destroy();
            }
            this._super();
        }

    });
});