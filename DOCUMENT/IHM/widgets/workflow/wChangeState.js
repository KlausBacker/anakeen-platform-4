define([
    'underscore',
    'mustache',
    'widgets/workflow/mChangeState',
    'widgets/workflow/vChangeState',
    'kendo/kendo.core',
    'widgets/widget',
    'kendo/kendo.window'
], function (_, Mustache, mChangeState, vChangeState, kendo) {
    'use strict';

    $.widget("dcp.dcpChangeState", {
        view: null, // Backbone view
        model: null, // Backbone model
        options: {
            documentId: 0,
            documentModel: null,
            transition: null,
            nextState: null,
            window: {
                modal: true,
                actions: [
                    "Maximize",
                    "Close"
                ],

                visible: false,
                height: "300px",
                width: "500px",
                title: "Change state"
            },
            labels: {
                confirm: "Confirm",
                cancel: "Cancel"
            }
        },
        htmlCaneva: function () {

            return '<div class="dcpChangeState">' +
            '<div class="dcpChangeState--content">Loading</div>' +

            '</div>';
        },

        currentWidget: null,
        _create: function () {
            var scope = this;
            this.currentWidget = $('<div class="dcpChangeState"/>');

            this.element.append(this.currentWidget);

            this.element.data("dcpChangeState", this);
            this.currentWidget.attr("data-state", this.options.nextState);
            if (this.options.transition) {
                this.currentWidget.attr("data-transition", this.options.transition);
            }
            this.options.window.close = function () {
                _.delay(function () {
                    scope.destroy();
                }, 1000);
            };


            this.currentWidget.kendoWindow(this.options.window);
            this._initContent();
        },


        _initContent: function () {
            var $content = this.currentWidget.find(".dcpChangeState--content");

            this.model = new mChangeState({
                documentId: this.options.documentId,
                documentModel: this.options.documentModel,
                state: this.options.nextState,
                transition: this.options.transition

            });

            this.view = new vChangeState({
                model: this.model,
                el: this.currentWidget,
                dialogWindow: this.currentWidget
            });

            this.model.fetch();
        },

        open: function wChangeStateOpen() {
            this.currentWidget.data("kendoWindow").center();
            this.currentWidget.data("kendoWindow").open();
        },
        close: function wChangeStateOpen() {
            var kw=this.currentWidget.data("kendoWindow");
            if (kw) {
            kw.close();
            }
        },


        _destroy: function wChangeStateDestroy() {
            if (this.view) {
                this.view.remove();
            }

            if (this.currentWidget && this.currentWidget.data("kendoWindow")) {
                this.currentWidget.data("kendoWindow").destroy();
            }


            this._super();
        }

    });
});