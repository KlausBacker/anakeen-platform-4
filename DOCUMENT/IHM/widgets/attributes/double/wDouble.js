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
            numberFormat : 'n'
        },

        _initChangeEvent: function _initChangeEvent() {
            // set by widget
        },

        _activateNumber: function (inputValue) {
            var scope=this;
            inputValue.kendoNumericTextBox({
                decimals: 2,
                format:scope.options.numberFormat,
                change : function () {
                    // Need to set by widget to honor decimals option
                    scope._model().setValue({value:this.value()});
                }
            });
        },


        getType: function () {
            return "double";
        }

    });
});