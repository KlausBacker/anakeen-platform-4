define([
    'underscore',
    'kendo/kendo.timepicker',
    'widgets/attributes/date/wDate'
], function (_, kendo) {
    'use strict';

    $.widget("dcp.dcpTime", $.dcp.dcpDate, {

        options: {
            type: "time",
            timeDataFormat: ["HH:mm", "HH:mm:ss"]
        },

        kendoWidgetClass: "kendoTimePicker",

        _activateDate: function (inputValue) {
            var scope = this;
            if (!scope.options.renderOptions) {
                scope.options.renderOptions = {};
            }
            inputValue.kendoTimePicker({
                parseFormat: this.options.timeDataFormat,
                change: function () {
                    if (this.value() !== null) {
                        // only valid date are setted
                        // wrong date are set by blur event
                        var isoDate = scope.convertDateToPseudoIsoString(this.value());
                        // Need to set by widget to use raw date

                        scope.setValue({value: isoDate, displayValue: inputValue.val()});
                    }
                }
            });
            this._controlDate(inputValue);
            if (this.options.value && this.options.value.value) {
                this.setValue(this.options.value);
            }
        },

        setValue: function (value) {
            var originalValue;

            value = _.clone(value);


            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = this.formatDate(this.parseDate(value.value));
            }

            $.dcp.dcpAttribute.prototype.setValue.call(this, value);

            if (this.getMode() === "write") {
                originalValue = this.convertDateToPseudoIsoString(this.kendoWidget.data(this.kendoWidgetClass).value());
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    if (value.value) {
                        this.kendoWidget.data(this.kendoWidgetClass).value(value.value);
                    } else {
                        this.getContentElements().val('');
                    }
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.getContentElements().text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        getValue: function () {
            var value = this._super();
            if (value.value && _.isDate(value.value)) {
                value.value = this.convertDateToPseudoIsoString(value.value);
            }
            return value;
        },

        convertDateToPseudoIsoString: function (date) {
            console.log("time date", date);
            if (_.isDate(date)) {
                return this.padNumber(date.getHours()) + ':' +
                    this.padNumber(date.getMinutes());
            }
            return '';
        },

        formatDate: function formatDate(value) {
            return kendo.toString(value, "T");
        },

        parseDate: function (value) {
            return kendo.parseDate(value, this.options.timeDataFormat);
        },

        getType: function () {
            return "time";
        }

    });

    return $.fn.dcpTime;
});