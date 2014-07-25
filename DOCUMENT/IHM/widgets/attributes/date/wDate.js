define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpDate", $.dcp.dcpText, {

        options: {
            id: "",
            type: "date"
        },

        kendoWidgetClass: "kendoDatePicker",
        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            if (this.kendoWidget) {
                if (this.options.hasAutocomplete) {
                    this.activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateDate(this.kendoWidget);
                }
            }
        },

        _initChangeEvent: function _initChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue: function (value) {
            // this._super.(value);
            // Don't call dcpText::setValue()
            $.dcp.dcpAttribute.prototype.setValue.apply(this, [value]);

            var originalValue = this.date2string(this.kendoWidget.data(this.kendoWidgetClass).value());

            if (this.getMode() === "write") {
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    if (value.value) {
                        var oDate = new Date(value.value);
                        if (!isNaN(oDate.getTime())) {
                            console.log("set date to", oDate);
                            this.kendoWidget.data(this.kendoWidgetClass).value(oDate);
                        }
                    } else {
                        this.contentElements().val('');
                    }
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.contentElements().text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        _activateDate: function (inputValue) {
            var scope = this;
            if (!scope.options.renderOptions) {
                scope.options.renderOptions = {};
            }
            inputValue.kendoDatePicker({
                parseFormats: ["yyyy-MM-dd"],
                min: new Date(1700, 0, 1),
                change: function () {
                    if (this.value() !== null) {
                        // only valid date are setted
                        // wrong date are set by blur event
                        console.log("date raw", this.value());
                        var isoDate = scope.date2string(this.value());
                        console.log("date", isoDate);
                        // Need to set by widget to use raw date
                        scope.setValue({value: isoDate, displayValue: inputValue.val()});
                    }
                }
            });

            this._controlDate(inputValue);
        },


        _controlDate: function (inputValue) {
            var scope = this;
            inputValue.on('blur', function validateDate(event) {
                console.log(this, $(this).val());
                var dateValue = $(this).val().trim();

                scope.setError(null); // clear Error before
                scope._trigger("changeattrmenuvisibility", event, {
                    id: "save",
                    visibility: "visible"
                });


                if (dateValue) {
                    console.log("verify", dateValue, kendo.culture().name);
                    if (!kendo.parseDate(dateValue)) {
                        console.log("BOUDATE");
                        scope.setValue({value: inputValue.val()});
                        scope._trigger("changeattrmenuvisibility", event, {
                            id: "save",
                            visibility: "disabled"
                        });
                        _.defer(function () {
                            scope._focusInput().focus();
                        });

                        scope.setError("Invalid date");

                    }
                }
            });
        },

        date2string: function (oDate) {
            if (oDate && typeof oDate === "object") {
                return oDate.getFullYear() + '-' + this.padNumber(oDate.getMonth() + 1) + '-' + this.padNumber(oDate.getDate());
            }
            return '';
        },

        padNumber: function pad(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        },

        getType: function () {
            return "date";
        }

    });
});