define([
    'kendo/kendo.datetimepicker',
    'dcpDocument/widgets/attributes/date/wDate'
], function (kendo) {
    'use strict';

    $.widget("dcp.dcpTimestamp", $.dcp.dcpDate, {

        options: {
            type: "timestamp",
            minDate: new Date(1700, 0, 1),
            renderOptions: {
                kendoDateConfiguration: {
                    timeFormat: "HH:mm", //24 hours format
                    parseFormats: ["yyyy-MM-dd HH:mm:ss", "yyyy-MM-ddTHH:mm:ss", "yyyy-MM-ddTHH:mm"],
                    format:null
                }
            }
        },

        kendoWidgetClass: "kendoDateTimePicker",


        _initDom: function wTimeStampInitDom() {
            if (this.options.attributeValue.value) {
                // Add T (iso date) if not set

                this.options.attributeValue.value= this.replaceAt(this.options.attributeValue.value, 10, 'T');

            }
            this._super();
        },

        replaceAt : function wTimeStampReplaceAt(s, n, t) {
            return s.substring(0, n) + t + s.substring(n + 1);
        },
        setValue: function wDateSetValue(value) {
            if (value.value) {
                // Add T (iso date) if not set
                value.value= this.replaceAt(value.value, 10, 'T');
            }
            this._super(value);
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