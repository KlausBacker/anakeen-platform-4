(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'dcpDocument/widgets/attributes/date/wDate'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window.kendo);
    }
}(window,  function requireTimestamp($, kendo) {
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

        setValue: function wTimeStampSetValue(value) {
            if (value.value) {
                // Add T (iso date) if not set
                value.value= this.replaceAt(value.value, 10, 'T');
            }
            this._super(value);
        },

        _activateDate: function wTimeStamp_activateDate(inputValue) {
            var currentWidget = this;
            var kendoOptions = this.getKendoOptions();
            kendoOptions.change = function wTimeStamp_onChange() {
                if (this.value() !== null) {
                    // only valid date are setted
                    // wrong date are set by blur event
                    var isoDate = currentWidget.convertDateToPseudoIsoString(this.value());
                    // Need to set by widget to use raw date
                    currentWidget.setValue({value: isoDate, displayValue: inputValue.val()});
                }
            };
            inputValue.kendoDateTimePicker(kendoOptions);

            this._controlDate(inputValue);
        },

        convertDateToPseudoIsoString: function wTimeStamp_convertDateToPseudoIsoString(dateObject) {
            if (dateObject && typeof dateObject === "object") {
                return dateObject.getFullYear() + '-' +
                    this.padNumber(dateObject.getMonth() + 1) + '-' +
                    this.padNumber(dateObject.getDate()) + 'T' +
                    this.padNumber(dateObject.getHours()) + ':' +
                    this.padNumber(dateObject.getMinutes()) + ':' +
                    this.padNumber(dateObject.getSeconds());
            }
            return '';
        },

        formatDate: function wTimeStamp_formatDate(value) {
            if (this.options.renderOptions.kendoDateConfiguration.format) {
                return kendo.toString(value, this.options.renderOptions.kendoDateConfiguration.format);
            }
            return kendo.toString(value, "g");
        },

        getType: function wTimeStamp_getType() {
            return "timestamp";
        }

    });

    return $.fn.dcpTimestamp;
}));