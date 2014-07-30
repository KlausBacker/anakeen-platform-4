define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpInt", $.dcp.dcpText, {

        options: {
            id: "",
            type: "int",
            numberFormat: 'n0',
            renderOptions: {
                kendoNumericConfiguration: {}
            }
        },
        /**
         * The kendoNumericTextBox widget instance
         */
        kendoWidget:null,
        _initDom: function wIntInitDom() {
            if (parseFloat(this.options.value.displayValue) === parseFloat(this.options.value.value)) {
                this.options.value.displayValue = kendo.toString(this.options.value.value, this.options.numberFormat);
            }

            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            if (this.kendoWidget) {
                if (this.options.hasAutocomplete) {
                    this.activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateNumber(this.kendoWidget);
                }
            }
        },

        _initChangeEvent: function wIntInitChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue: function wIntSetValue(value) {
            // this._super.(value);
            // Don't call dcpText::setValue()
            $.dcp.dcpAttribute.prototype.setValue.apply(this, [value]);

            var originalValue = this.kendoWidget.data("kendoNumericTextBox").value();

            if (this.getMode() === "write") {
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    this.kendoWidget.data("kendoNumericTextBox").value(value.value);
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.contentElements().text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        _activateNumber: function wIntActivateNumber(inputValue) {
            inputValue.kendoNumericTextBox(this.getKendoNumericOptions());
        },

        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoNumericOptions: function wIntGetKendoNumericOptions() {
            var scope = this;
            var knOption = {};
            var defaultOptions = {
                decimals: 0,
                format: scope.options.numberFormat,
                max: scope.options.renderOptions.max,
                min: scope.options.renderOptions.min,
                change: function () {
                    // Need to set by widget to honor decimals option
                    scope.setValue({value: this.value()});
                }
            };

            if (typeof scope.options.renderOptions.kendoNumericConfiguration === 'object') {
                knOption = scope.options.renderOptions.kendoNumericConfiguration;
            }
            return _.extend(defaultOptions, knOption);
        },

        getType: function wIntGetType() {
            return "int";
        }

    });
});