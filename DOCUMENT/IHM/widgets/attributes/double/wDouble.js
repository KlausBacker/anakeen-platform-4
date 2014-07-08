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