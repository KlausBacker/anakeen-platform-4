define([
    'widgets/attributes/date/wDate',
    'kendo/kendo.datetimepicker'
], function () {
    'use strict';

    $.widget("dcp.dcpTimestamp", $.dcp.dcpDate, {

        options : {
            type : "timestamp",
            minDate :        new Date(1700, 0, 1),
            dateDataFormat : ["yyyy-MM-dd"]
        },

        kendoWidgetClass : "kendoDateTimePicker",

        _activateDate : function (inputValue) {
            var scope = this;
            if (!scope.options.renderOptions) {
                scope.options.renderOptions = {};
            }
            inputValue.kendoDateTimePicker({
                parseFormats : ["yyyy-MM-dd"],
                timeFormat :   "HH:mm", //24 hours format
                min :          new Date(1700, 0, 1),
                change :       function () {
                    if (this.value() !== null) {
                        // only valid date are setted
                        // wrong date are set by blur event
                        var isoDate = scope.convertDateToPseudoIsoString(this.value());
                        // Need to set by widget to use raw date
                        scope.setValue({value : isoDate, displayValue : inputValue.val()});
                    }
                }
            });

            this._controlDate(inputValue);
        },

        convertDateToPseudoIsoString : function (oDate) {
            if (oDate && typeof oDate === "object") {
                return oDate.getFullYear() + '-' +
                    this.padNumber(oDate.getMonth() + 1) + '-' +
                    this.padNumber(oDate.getDate()) + 'T' +
                    this.padNumber(oDate.getHours()) + ':' +
                    this.padNumber(oDate.getMinutes()) + ':' +
                    this.padNumber(oDate.getSeconds());
            }
            return '';
        },
        getType :     function () {
            return "timestamp";
        }

    });

    return $.fn.dcpTimestamp;
});