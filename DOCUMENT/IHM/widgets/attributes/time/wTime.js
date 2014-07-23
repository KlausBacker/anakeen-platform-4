define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/date/wDate'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpTime", $.dcp.dcpDate, {

        options: {
            id: "",
            type: "time"
        },

        kendoWidgetClass : "kendoTimePicker",


        _activateDate: function (inputValue) {
            var scope = this;
            if (!scope.options.renderOptions) {
                scope.options.renderOptions = {};
            }
            inputValue.kendoTimePicker({
                timeFormat: "HH:mm", //24 hours format
                change: function () {
                    if (this.value() !== null) {
                        // only valid date are setted
                        // wrong date are set by blur event
                        console.log("date raw", this.value());
                        var isoDate = scope.date2string(this.value());
                        console.log("date", isoDate);
                        // Need to set by widget to use raw date
                        scope._model().setValue({value: isoDate, displayValue: inputValue.val()}, scope._getIndex());
                    }
                }
            });

           this._controlDate(inputValue);
        },



        date2string: function (oDate) {
            if (oDate && typeof oDate === "object") {
                return this.padNumber(oDate.getHours())+ ':' +
                    this.padNumber(oDate.getMinutes())+ ':' +
                    this.padNumber(oDate.getSeconds());
            }
            return '';
        },



        getType: function () {
            return "time";
        }

    });
});