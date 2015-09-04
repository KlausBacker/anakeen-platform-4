/*global define, _super, kendoColorPicker*/
define([
    'jquery',
    'underscore',
    'mustache',
    'kendo/kendo.colorpicker',
    'dcpDocument/widgets/attributes/text/wText'
], function ($, _, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpColor", $.dcp.dcpText, {

        options :     {
            type :          "color",

            renderOptions : {
                kendoColorConfiguration : {
                    buttons:false
                }
            },
            labels : {
            }
        },
        /**
         * The kendoColorPicker widget instance
         */
        kendoWidget : null,

        _initDom : function wColorInitDom() {
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);
            if (parseFloat(this.options.attributeValue.displayValue) === parseFloat(this.options.attributeValue.value)) {
                this.options.attributeValue.displayValue = this.formatNumber(this.options.attributeValue.value);
            }


            //noinspection JSPotentiallyInvalidConstructorUsage,JSAccessibilityCheck
            $.dcp.dcpAttribute.prototype._initDom.apply(this, []);


            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            if (this.kendoWidget) {
                if (this.options.hasAutocomplete) {
                    this.activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateColor(this.kendoWidget);

                    if (this.kendoWidget.hasClass("form-control")) {
                        this.element.find(".k-colorpicker").addClass("form-control");
                    }
                }
            }
            if (this.element.find(".dcpAttribute__content__buttons button").length === 0) {
                this.element.find(".k-picker-wrap").
                    addClass("dcpAttribute__content__nobutton");
            }
            this.element.find(".dcpAttribute__value--read").css("border-color", this.options.attributeValue.value);


        },

        _initChangeEvent : function wcolInitChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue : function wcolSetValue(value) {
            // this._super.(value);
            // Don't call dcpText::setValue()

            value = _.clone(value);

            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = this.formatNumber(value.value);
            }

            $.dcp.dcpAttribute.prototype.setValue.apply(this, [value]);

            if (this.getMode() === "write") {
                var originalValue = this.kendoWidget.data("kendoColorPicker").value();
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    this.kendoWidget.data("kendoColorPicker").value(value.value);
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.getContentElements().text(value.displayValue);
                this.element.find(".dcpAttribute__value--read").css("border-color", value.value);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        _activateColor : function wcolActivateNumber(inputValue) {
            return inputValue.kendoColorPicker(this.getKendoColorOptions());
        },

        formatNumber : function wcolFormatNumber(value) {
            return kendo.toString(value, this.getKendoColorOptions().format);
        },

        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoColorOptions : function wColorgetKendoColorOptions() {
            var scope = this,
                kendoOptions = {},
                defaultOptions = {
                    change :   function () {
                        // Need to set by widget to honor decimals option
                        scope.setValue({value : this.value()});
                    }
                };

            if (_.isObject(scope.options.renderOptions.kendoColorConfiguration)) {
                kendoOptions = scope.options.renderOptions.kendoColorConfiguration;
            }
            return _.extend(defaultOptions, kendoOptions);
        },

        getType : function wcolGetType() {
            return "color";
        },

        testValue : function wcolTestValue(value) {
            this._super(value);
            if (!_.isNumber(value.value)) {
                throw new Error("The value must be a number for (attrid : " + this.options.id + ")");
            }
        },

        _destroy : function _destroy() {
            if (this.kendoWidget && this.kendoWidget.data("kendoColorPicker")) {
                this.kendoWidget.data("kendoColorPicker").destroy();
            }
            this._super();
        }

    });

    return $.fn.dcpColor;
});