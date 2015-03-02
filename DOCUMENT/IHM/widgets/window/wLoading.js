define([
    'underscore',
    'dcpDocument/widgets/widget',
    'kendo/kendo.window',
    'kendo/kendo.progressbar'
], function (_) {
    'use strict';

    $.widget("dcp.dcpLoading", {
        pc :            0,
        rest :          0,
        restItem :      0,
        currentWidget : null,
        doneItems :     0,
        barElement :    null,
        original :      null,

        _create : function _create() {
            this.barElement = $('<div class="dcpLoading--progressbar dcpLoading--progressbar-complete" />');

            this.element.find('.dcpLoading--progressbar').hide();
            this.element.append(this.barElement);
            this.barElement.kendoProgressBar({
                    type :      "percent",
                    animation : {
                        duration : 1
                    }
                }
            );
        },

        reset : function reset() {
            this.element.find('.dcpLoading--progressbar').show();
            this.element.find('.dcpLoading--progressbar-complete').hide();
        },

        setTitle : function setTitle(val) {
            this.element.find('.dcpLoading--title').html(val);
        },

        modalMode : function modalMode() {
            var scopeElement = this.element;
            this.element.kendoWindow({
                modal :     true,
                actions :   [],
                visible :   false,
                draggable : false,
                title :     false,
                width :     "400px",
                height :    "100px"
            });
            this.element.show();
            this.element.data('kendoWindow').center();
            this.element.data('kendoWindow').open();
            _.defer(function () {
                scopeElement.show();
            });

        },

        hide : function hide() {
            this.element.hide();
            this.element.removeClass("dcpLoading--hide");
            if (this.element.data('kendoWindow')) {
                this.element.data('kendoWindow').close();
            }
        },

        show : function show() {
            this.barElement.data("kendoProgressBar").value(0);
            this.reset();
            this.element.show();
        },

        complete : function complete(onComplete) {
            this.barElement.data("kendoProgressBar").bind("complete", onComplete);
        },

        setPercent : function setPercent(pc) {
            this.pc = pc;
            this.barElement.data("kendoProgressBar").value(Math.round(this.pc));
        },

        setNbItem : function setNbItem(restItem) {
            this.rest = 100 - this.pc;
            this.restItem = restItem;
        },

        addItem : function addItem(number) {
            number = number || 1;
            number = parseInt(number, 10);
            this.doneItems += number;
            var pv = (this.rest / this.restItem) * number;
            this.setPercent(this.pc + pv);
        },

        _destroy : function _destroy() {
            if (this.element.data('kendoWindow')) {
                this.element.data('kendoWindow').destroy();
            }
            if (this.barElement.data("kendoProgressBar")) {
                this.barElement.data("kendoProgressBar").destroy();
            }
            this._trigger("destroy");
            this._super();
        }

    });
});