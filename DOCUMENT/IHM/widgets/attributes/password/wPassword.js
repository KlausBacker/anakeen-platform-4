/*global define, _super, kendoNumericTextBox*/
define([
    'underscore',
    'mustache',
    'dcpDocument/widgets/attributes/text/wText'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpPassword", $.dcp.dcpText, {

        options: {
            type: "password",

            renderOptions: {
                hideValue: '*****'
            },
            labels: {}
        },


        _initDom: function wasswordInitDom() {
            if (this.getMode() === "read") {
                if (this.options.attributeValue.value) {
                    this.options.attributeValue.displayValue = this.options.renderOptions.hideValue;
                }
            }

            this._super();
            if (this.getMode() === "write") {
                this._getFocusInput().attr("type", "password");
            }
        },

        getType: function wIntGetType() {
            return "password";
        }
    });

    return $.fn.dcpPassword;
});