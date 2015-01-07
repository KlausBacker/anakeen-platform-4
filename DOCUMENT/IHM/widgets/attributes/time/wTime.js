define([
    'underscore',
    'kendo/kendo.timepicker',
    'widgets/attributes/date/wDate'
], function (_, kendo) {
    'use strict';

    $.widget("dcp.dcpTime", $.dcp.dcpDate, {

        options: {
            type: "time",
            renderOptions: {
                kendoTimeConfiguration: {
                    timeDataFormat: ["HH:mm", "HH:mm:ss"]
                }
            }
        },

        kendoWidgetClass: "kendoTimePicker",

        _activateDate: function (inputValue) {
            var scope = this;
            var kOptions = this.getKendoOptions();

            kOptions.change = function () {
                // only valid date are setted
                // wrong date are set by blur event
                var isoDate = scope.convertDateToPseudoIsoString(this.value());
                // Need to set by widget to use raw date

                scope.setValue({value: isoDate, displayValue: inputValue.val()});
            };
            inputValue.kendoTimePicker(kOptions);
            this._controlDate(inputValue);
            if (this.options.attributeValue && this.options.attributeValue.value) {
                this.setValue(this.options.attributeValue);
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
            return kendo.parseDate(value, this.options.renderOptions.kendoTimeConfiguration.timeDataFormat);
        },


        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoOptions: function wTimegetKendoOptions() {
            var scope = this,
                kendoOptions = {},
                defaultOptions = {
                    min: this.options.minDate
                };

            if (_.isObject(scope.options.renderOptions.kendoTimeConfiguration)) {
                kendoOptions = scope.options.renderOptions.kendoTimeConfiguration;
            }

            return _.extend(defaultOptions, kendoOptions);
        },

        getType: function () {
            return "time";
        }

    });

    return $.fn.dcpTime;
});