define([
    'underscore',
    'kendo/kendo.core',
    'widgets/widget',
    'kendo/kendo.window'
], function (_, kendo) {
    'use strict';

    $.widget("dcp.dcpTransitionWindow", {
        options: {
            window: {
                modal: true,
                actions: [
                    "Maximize",
                    "Close"
                ],

                visible: false,
                height: "300px",
                width: "500px",
                title: "-"
            }
        },


        _create: function () {
            var scope = this;


            this.element.data("dcpTransitionWindow", this);

            this.options.window.close = function () {
                _.delay(function () {
                    scope.destroy();
                }, 1000);
            };

            this.element.kendoWindow(this.options.window);
        },

        open: function wTransitionOpen() {
            this.element.data("kendoWindow").center();
            this.element.data("kendoWindow").open();
        },
        close: function wTransitionOpen() {
            var kw = this.element.data("kendoWindow");
            if (kw) {
                kw.close();
            }
        },

        _destroy: function wTransitionDestroy() {
            if (this.element && this.element.data("kendoWindow")) {
                this.element.data("kendoWindow").destroy();
            }

            this._super();
        }

    });
});