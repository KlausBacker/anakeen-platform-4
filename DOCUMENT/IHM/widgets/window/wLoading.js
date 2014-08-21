define([
    'underscore',
    'widgets/widget',
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

        _create : function () {
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

        reset : function () {
            this.element.find('.dcpLoading--progressbar').show();
            this.element.find('.dcpLoading--progressbar-complete').hide();
        },

        title : function (val) {
            this.element.find('.dcpLoading--title').html(val);
        },

        modalMode : function () {
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

        hide : function () {
            this.element.hide();
            this.element.removeClass("dcpLoading--hide");
            if (this.element.data('kendoWindow')) {
                this.element.data('kendoWindow').close();
            }
        },

        complete : function (onComplete) {
            this.barElement.data("kendoProgressBar").bind("complete", onComplete);
        },

        percent : function (pc) {
            this.pc = pc;
            this.barElement.data("kendoProgressBar").value(Math.round(this.pc));
        },

        setRest : function (restItem) {
            this.rest = 100 - this.pc;
            this.restItem = restItem;
        },

        addItem : function (number) {
            number = parseInt(number);
            if (number <= 0) {
                number = 1;
            }
            this.doneItems += number;
            var pv = (this.rest / this.restItem) * number;
            this.percent(this.pc + pv);
        }

    });
});