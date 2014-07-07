define([
    'underscore',
    'mustache',
    '../attribute',
    'widgets/attributes/int/int'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpDouble", $.dcp.dcpInt, {

        options: {
            id: "",
            type: "double"
        },

        _activateNumber: function (inputValue) {
            inputValue.kendoNumericTextBox({
                culture: "fr-FR",
                decimals: 2,
                format:"n2"
            });
        },


        getType: function () {
            return "double";
        }

    });
});