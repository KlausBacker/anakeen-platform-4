/*global define, _super, kendoNumericTextBox*/
define([
    'underscore',
    'mustache',
    'kendo/kendo.numerictextbox',
    'widgets/attributes/text/wText',
    "kendo-culture-fr"
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpInt", $.dcp.dcpText, {

        options :     {
            type :          "int",

            renderOptions : {
                kendoNumericConfiguration : {},
                max:null,
                min:null,
                numberFormat :  'n0'
            },
            labels : {
                decreaseLabel : "Decrease value",
                increaseLabel : "Increase value"
            }
        },
        /**
         * The kendoNumericTextBox widget instance
         */
        kendoWidget : null,

        _initDom : function wIntInitDom() {
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);
            if (parseFloat(this.options.attributeValue.displayValue) === parseFloat(this.options.attributeValue.value)) {
                this.options.attributeValue.displayValue = this.formatNumber(this.options.attributeValue.value);
            }

            if (this.getMode() === "read") {
                if (this.options.renderOptions.format) {
                    this.options.attributeValue.formatValue=Mustache.render(this.options.renderOptions.format,
                        this.options.attributeValue);
                }
            }

            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            if (this.kendoWidget) {
                if (this.options.hasAutocomplete) {
                    this.activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateNumber(this.kendoWidget);
                }
            }
        },

        _initChangeEvent : function wIntInitChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue : function wIntSetValue(value) {
            // this._super.(value);
            // Don't call dcpText::setValue()

            value = _.clone(value);

            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = this.formatNumber(value.value);
            } else if (parseFloat(value.displayValue) === parseFloat(value.value)) {
                value.displayValue = this.formatNumber(value.value);
            }

            $.dcp.dcpAttribute.prototype.setValue.apply(this, [value]);

            if (this.getMode() === "write") {
                var originalValue = this.kendoWidget.data("kendoNumericTextBox").value();
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    this.kendoWidget.data("kendoNumericTextBox").value(value.value);
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.getContentElements().text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        _activateNumber : function wIntActivateNumber(inputValue) {
            return inputValue.kendoNumericTextBox(this.getKendoNumericOptions());
        },

        formatNumber : function wIntFormatNumber(value) {
            try {
                value = kendo.toString(value, this.getKendoNumericOptions().format);
            } catch(e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
                console.error("Unable to format the number "+e);
            }
            return value;
        },

        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoNumericOptions : function wIntGetKendoNumericOptions() {
            var scope = this,
                kendoOptions = {},
                defaultOptions = {
                    decimals : 0,
                    downArrowText : scope.options.labels.decreaseLabel,
                    upArrowText :  scope.options.labels.increaseLabel,
                    format :   scope.options.renderOptions.numberFormat,
                    max :      scope.options.renderOptions.max,
                    min :      scope.options.renderOptions.min,
                    change :   function () {
                        // Need to set by widget to honor decimals option
                        scope.setValue({value : this.value()});
                    }
                };


            if (_.isObject(scope.options.renderOptions.kendoNumericConfiguration)) {
                kendoOptions = scope.options.renderOptions.kendoNumericConfiguration;
            }

            return _.extend(defaultOptions, kendoOptions);
        },

        getType : function wIntGetType() {
            return "int";
        },

        testValue : function wIntTestValue(value) {
            this._super(value);
            if (!_.isNumber(value.value)) {
                throw new Error("The value must be a number for (attrid : " + this.options.id + ")");
            }
        },

        _destroy : function _destroy() {
            if (this.kendoWidget && this.kendoWidget.data("kendoNumericTextBox")) {
                this.kendoWidget.data("kendoNumericTextBox").destroy();
            }
            this._super();
        }

    });

    return $.fn.dcpInt;
});