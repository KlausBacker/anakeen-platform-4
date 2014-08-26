define([
    'widgets/attributes/double/wDouble'
], function () {
    'use strict';

    $.widget("dcp.dcpMoney", $.dcp.dcpDouble, {

        options: {
            type: "money",
            renderOptions: {
                currency: '€',
                numberFormat: '#,#.00'
            }
        },


        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoNumericOptions: function wMoneyGetKendoNumericOptions() {
            var options = this._super(); // get from wDouble
            if (this.options.renderOptions.currency) {
                // view decimal precision
                switch (this.options.locale.substr(0, 2)) {
                    case 'en':
                    case 'ga': // Ireland Irish
                        // currency before
                        options.format = this.options.renderOptions.currency.replace('$', '\\$') + options.format;
                        break;
                    default :
                        options.format += ' ' + this.options.renderOptions.currency.replace('$', '\\$');
                }
            }

            return options;
        },

        getType: function wMoneyGetType() {
            return "money";
        }

    });

    return $.fn.dcpMoney;
});