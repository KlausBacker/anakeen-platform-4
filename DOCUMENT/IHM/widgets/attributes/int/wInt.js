define([
    'underscore',
    'mustache',
    '../wAttribute',
    'widgets/attributes/text/wText'
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


       setValue: function (value) {
           // this._super.(value);
           // Don't call dcpText::setValue()
           $.dcp.dcpAttribute.prototype.setValue.apply(this, [value]);

            var originalValue = this.kendoWidget.data("kendoNumericTextBox").value();

            if (this.getMode() === "write") {
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    this.kendoWidget.data("kendoNumericTextBox").value(value.value);
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.contentElements().text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        _activateNumber: function (inputValue) {
            inputValue.kendoNumericTextBox({
                decimals: 0,
                format:"n0"
            });
        },


        getType: function () {
            return "int";
        }

    });
});