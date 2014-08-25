define([
    'widgets/attributes/int/wInt',
], function () {
    'use strict';

    $.widget("dcp.dcpDouble", $.dcp.dcpInt, {

        options: {
            type: "double",
            numberFormat: 'n'
        },

        _initDom: function wDoubleInitDom() {
            if (this.options.renderOptions.decimalPrecision > 0) {
                // view decimal precision
                this.options.numberFormat = 'n' + this.options.renderOptions.decimalPrecision;
            }
            this._super();
        },
        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoNumericOptions: function wDoubleGetKendoNumericOptions () {
            var options=this._super(); // get from wInt
            options.decimals=this.options.renderOptions.decimalPrecision;
            return options;
        },

        getType: function wDoubleGetType () {
            return "double";
        }

    });

    return $.fn.dcpDouble;
});