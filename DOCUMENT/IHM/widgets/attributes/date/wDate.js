/*global define, _super*/
define([
    'underscore',
    'mustache',
    'kendo/kendo.datepicker',
    'widgets/attributes/text/wText',
    "kendo-culture-fr"
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpDate", $.dcp.dcpText, {

        options: {
            type: "date",
            minDate: new Date(1700, 0, 1),
            renderOptions: {
                kendoDateConfiguration: {
                    parseFormats: ["yyyy-MM-dd"],
                    format:null
                }
            },
            labels : {
                invalidDate:"Invalid Date"
            }
        },

        kendoWidgetClass: "kendoDatePicker",

        _initDom: function wDateInitDom() {

            if (this.options.renderOptions.kendoDateConfiguration.format) {
                this.options.attributeValue.displayValue=this.formatDate(this.parseDate(this.options.attributeValue.value));
            }
            if (this.getMode() === "read") {
                if (this.options.renderOptions.format) {
                    this.options.attributeValue.formatValue=Mustache.render(this.options.renderOptions.format,
                        this.options.attributeValue);
                }
            }
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);
            //noinspection JSPotentiallyInvalidConstructorUsage,JSAccessibilityCheck
            $.dcp.dcpAttribute.prototype._initDom.apply(this, []);

            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");


            if (this.kendoWidget.length) {
                if (this.options.hasAutocomplete) {
                    this.activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateDate(this.kendoWidget);
                }
            }
        },

        _initChangeEvent: function wDate_initChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue: function wDateSetValue(value) {
            // this._super.(value);
            // Don't call dcpText::setValue()

            var originalValue, originalDate;
            value = _.clone(value);
            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = this.formatDate(this.parseDate(value.value));
            } else if (this.options.renderOptions.kendoDateConfiguration.format) {
                value.displayValue = this.formatDate(this.parseDate(value.value));
            }

            $.dcp.dcpAttribute.prototype.setValue.call(this, value);

            if (this.getMode() === "write") {
                originalValue = this.convertDateToPseudoIsoString(this.kendoWidget.data(this.kendoWidgetClass).value());
                // : explicit lazy equal
                //noinspection JSHint
                if (originalValue != value.value) {
                    if (value.value) {
                        originalDate = new Date(value.value);
                        if (!isNaN(originalDate.getTime())) {
                            this.kendoWidget.data(this.kendoWidgetClass).value(originalDate);
                        }
                    } else {
                        this.getContentElements().val('');
                    }
                    // Modify value only if different
                    this.flashElement();
                }
            } else if (this.getMode() === "read") {
                this.getContentElements().text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        _activateDate: function wDateSetValueActivateDate(inputValue) {
            var scope = this;
            var kOptions = this.getKendoOptions();


            kOptions.change = function () {
                if (this.value() !== null) {
                    // only valid date are setted
                    // wrong date are set by blur event
                    var isoDate = scope.convertDateToPseudoIsoString(this.value());
                    // Need to set by widget to use raw date
                    scope.setValue({value: isoDate, displayValue: inputValue.val()});
                }
            };

            inputValue.kendoDatePicker(kOptions);

            this._controlDate(inputValue);
        },

        _controlDate: function wDateControlDate(inputValue) {
            var scope = this;
            inputValue.on('blur' + this.eventNamespace, function validateDate(event) {
                var dateValue = $(this).val().trim();

                scope.setError(null); // clear Error before
                scope._trigger("changeattrmenuvisibility", event, {
                    id: "save",
                    visibility: "visible"
                });

                if (dateValue) {
                    if (!scope.parseDate(dateValue)) {
                        scope.setValue({value: inputValue.val()});
                        scope._trigger("changeattrmenuvisibility", event, {
                            id: "save",
                            visibility: "disabled"
                        });
                        _.defer(function () {
                            scope._getFocusInput().focus();
                        });
                        scope.setError(scope.options.labels.invalidDate);
                    }
                }
            });
        },

        formatDate: function wDateFormatDate(value) {
            if (this.options.renderOptions.kendoDateConfiguration.format) {
                return kendo.toString(value, this.options.renderOptions.kendoDateConfiguration.format);
            }
            return kendo.toString(value, "d");
        },

        parseDate: function wDateParseDate(value) {
            return kendo.parseDate(value);
        },

        convertDateToPseudoIsoString: function (oDate) {
            if (oDate && _.isDate(oDate)) {
                return oDate.getFullYear() + '-' + this.padNumber(oDate.getMonth() + 1) + '-' + this.padNumber(oDate.getDate());
            }
            return '';
        },

        padNumber: function wDatePadNumber(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        },


        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoOptions: function wDategetKendoOptions() {
            var scope = this,
                kendoOptions = {},
                defaultOptions = {
                    min: this.options.minDate
                };

            if (_.isObject(scope.options.renderOptions.kendoDateConfiguration)) {
                kendoOptions = scope.options.renderOptions.kendoDateConfiguration;
            }

            return _.extend(defaultOptions, kendoOptions);
        },

        _destroy : function wDateDestroy() {
            //Destroy autocomplete if activated
            if (this.kendoWidget.data(this.kendoWidgetClass)) {
                this.kendoWidget.data(this.kendoWidgetClass).destroy();
            }
            this._super();
        }
    });

    return $.fn.dcpDate;
});