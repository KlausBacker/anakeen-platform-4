define([
    'kendo/kendo.datetimepicker',
    'widgets/attributes/date/wDate'
], function (kendo) {
    'use strict';

    $.widget("dcp.dcpTimestamp", $.dcp.dcpDate, {

        options: {
            type: "timestamp",
            minDate: new Date(1700, 0, 1),
            renderOptions: {
                kendoDateConfiguration: {
                    timeFormat: "HH:mm", //24 hours format
                    parseFormats: ["yyyy-MM-dd HH:mm:ss", "yyyy-MM-ddTHH:mm:ss"],
                    format:null
                }
            }
        },

        kendoWidgetClass: "kendoDateTimePicker",


        _initDom: function wTimeStampInitDom() {
            if (this.options.attributeValue.value) {
                // Add T (iso date) if not set
                if (this.options.attributeValue.value.length > 10) {
                    this.options.attributeValue.value[11]='T';
                }
            }
            console.log("NEW TS", this.options.attributeValue);
            this._super();
        },


        _activateDate: function (inputValue) {
            var scope = this;
            var kOptions = this.getKendoOptions();
            kOptions.change = function () {
                if (this.value() !== null) {
                    // only valid date are setted
                    // wrong date are set by blur event
                    var isoDate = scope.convertDateToPseudoIsoString(this.value());
                    // Need to set by widget to use raw date
                    scope.setValue({value: isoDate, displayValue: inputValue.val()});
                }
            };
            inputValue.kendoDateTimePicker(kOptions);

            this._controlDate(inputValue);
        },

        convertDateToPseudoIsoString: function (oDate) {
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

        formatDate: function formatDate(value) {
            if (this.options.renderOptions.kendoDateConfiguration.format) {
                return kendo.toString(value, this.options.renderOptions.kendoDateConfiguration.format);
            }
            return kendo.toString(value, "g");
        },
        getType: function () {
            return "timestamp";
        }

    });

    return $.fn.dcpTimestamp;
});