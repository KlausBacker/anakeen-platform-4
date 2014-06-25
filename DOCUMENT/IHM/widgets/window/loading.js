define([
    'underscore',
    'widgets/widget'
], function (_) {
    'use strict';

    $.widget("dcp.dcpLoading", {


        pc: 0,
        rest: 0,
        restItem: 0,
        currentWidget: null,
        doneItems: 0,
        barElement: null,

        _create: function () {
            this.barElement = $('<div class="dcpLoading--progressbar/">');
            this.element.append($('<br/>'));
            this.element.append(this.barElement);
            this.barElement.kendoProgressBar({
                    type: "percent",
                    animation: {
                        duration: 1
                    }

                }
            );
        },


        complete: function (onComplete) {
            this.barElement.data("kendoProgressBar").bind("complete", onComplete);
        },

        percent: function (pc) {
            this.pc = pc;
            this.barElement.data("kendoProgressBar").value(Math.round(this.pc));
        },

        setRest: function (restItem) {
            this.rest = 100 - this.pc;
            this.restItem = restItem;
        },

        addItem: function (number) {
            number = parseInt(number);
            if (!(number > 0)) {
                number = 1;
            }
            this.doneItems += number;
            var pv = (this.rest / this.restItem) * number;
            this.percent(this.pc + pv);
        }

    });
});