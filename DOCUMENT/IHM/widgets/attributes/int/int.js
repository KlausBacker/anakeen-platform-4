define([
    'underscore',
    'mustache',
    '../attribute',
    'widgets/attributes/text/text'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpInt", $.dcp.dcpText, {

        options: {
            id: "",
            type: "int"
        },
        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            if (this.kendoWidget) {
                if (this.options.hasAutocomplete) {
                    this._activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateNumber(this.kendoWidget);
                }
            }
        },



        _activateNumber: function (inputValue) {
            inputValue.kendoNumericTextBox({
                culture: "fr_FR",
                decimals: 0,
                format:"n0"
            });
        },


        getType: function () {
            return "int";
        }

    });
});