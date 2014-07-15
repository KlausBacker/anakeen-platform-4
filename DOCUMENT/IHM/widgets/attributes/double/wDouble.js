define([
    'underscore',
    'mustache',
    '../wAttribute',
    'widgets/attributes/int/wInt'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpDouble", $.dcp.dcpInt, {

        options: {
            id: "",
            type: "double",
            numberFormat: 'n'
        },

        _initDom: function () {
            if (this.options.renderOptions.decimalPrecision > 0) {
                // view decimal precision
                this.options.numberFormat = 'n' + this.options.renderOptions.decimalPrecision;
            }

            this._super();
        },

        _activateNumber: function (inputValue) {
            var scope = this;


            inputValue.kendoNumericTextBox({
                decimals: scope.options.renderOptions.decimalPrecision,
                max: scope.options.renderOptions.max,
                min: scope.options.renderOptions.min,
                format: scope.options.numberFormat,
                change: function () {
                    // Need to set by widget to honor decimals option
                    console.log("change", this);
                    scope._model().setValue({value: this.value()});
                }
            });
        },


        getType: function () {
            return "double";
        }

    });
});