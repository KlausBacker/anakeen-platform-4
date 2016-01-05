define([
    'jquery',
    'dcpDocument/widgets/attributes/int/wInt'
], function require_double($) {
    'use strict';

    $.widget("dcp.dcpDouble", $.dcp.dcpInt, {

        options: {
            type: "double",
            renderOptions : {
                decimalPrecision : null, // unlimited precision
                numberFormat :  '#,#.######################'
            }
        },

        _initDom: function wDoubleInitDom() {
            if (this.options.renderOptions.decimalPrecision !== null && this.options.renderOptions.decimalPrecision >= 0) {
                // view decimal precision
                this.options.renderOptions.numberFormat = '#,#.' ;
                for (var idx=0;idx<this.options.renderOptions.decimalPrecision; idx++) {
                    this.options.renderOptions.numberFormat += '0';
                }
            }

            this._super();
        },
        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoNumericOptions: function wDoubleGetKendoNumericOptions () {
            var options=this._super(); // get from wInt
            //Limits are linked to this bug => https://github.com/telerik/kendo-ui-core/issues/423
            if (options.max === null) {
                options.max = 9.99999e19;
            }
            if (options.min === null) {
                options.min = -9.9999e19;
            }
            if (this.options.renderOptions.decimalPrecision !== null) {
                options.decimals = this.options.renderOptions.decimalPrecision;
            } else {
                options.decimals = 20;
            }
            return options;
        },

        getType: function wDoubleGetType () {
            return "double";
        }

    });

    return $.fn.dcpDouble;
});