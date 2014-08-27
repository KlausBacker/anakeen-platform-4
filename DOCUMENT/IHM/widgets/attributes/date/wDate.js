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

        options : {
            type : "date",
            minDate : new Date(1700, 0, 1),
            dateDataFormat : ["yyyy-MM-dd"],
            renderOptions : {
                kendoDateConfiguration : {}
            }
        },

        kendoWidgetClass : "kendoDatePicker",

        _initDom : function () {
            this.element.addClass("dcpAttribute__contentWrapper");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-id", this.options.id);
            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            if (this.kendoWidget.length) {
                if (this.options.hasAutocomplete) {
                    this.activateAutocomplete(this.kendoWidget);
                } else {
                    this._activateDate(this.kendoWidget);
                }
            }
        },

        _initChangeEvent : function _initChangeEvent() {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue : function (value) {
            // this._super.(value);
            // Don't call dcpText::setValue()

            var originalValue, originalDate;

            value = _.clone(value);
            if (_.has(value, "value") && !_.has(value, "displayValue")) {
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

        _activateDate : function (inputValue) {
            var scope = this;
            var kOptions=this.getKendoDateOptions();

            if (!scope.options.renderOptions) {
                scope.options.renderOptions = {};
            }
            kOptions.change =      function () {
                if (this.value() !== null) {
                    // only valid date are setted
                    // wrong date are set by blur event
                    var isoDate = scope.convertDateToPseudoIsoString(this.value());
                    // Need to set by widget to use raw date
                    scope.setValue({value : isoDate, displayValue : inputValue.val()});
                }
            };

            inputValue.kendoDatePicker(this.getKendoDateOptions());

            this._controlDate(inputValue);
        },

        _controlDate : function (inputValue) {
            var scope = this;
            inputValue.on('blur.' + this.eventNamespace, function validateDate(event) {
                var dateValue = $(this).val().trim();

                scope.setError(null); // clear Error before
                scope._trigger("changeattrmenuvisibility", event, {
                    id :         "save",
                    visibility : "visible"
                });

                if (dateValue) {
                    if (!scope.parseDate(dateValue)) {
                        scope.setValue({value : inputValue.val()});
                        scope._trigger("changeattrmenuvisibility", event, {
                            id :         "save",
                            visibility : "disabled"
                        });
                        _.defer(function () {
                            scope._getFocusInput().focus();
                        });
                        scope.setError("Invalid date");
                    }
                }
            });
        },

        formatDate : function formatDate(value) {
            return kendo.toString(value, "d");
        },

        parseDate : function(value) {
            return kendo.parseDate(value);
        },

        convertDateToPseudoIsoString : function (oDate) {
            if (oDate && _.isDate(oDate)) {
                return oDate.getFullYear() + '-' + this.padNumber(oDate.getMonth() + 1) + '-' + this.padNumber(oDate.getDate());
            }
            return '';
        },

        padNumber : function pad(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        },


        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoDateOptions : function wDateGetKendoDateOptions() {
            var scope = this,
                kendoOptions = {},
                defaultOptions = {
                    parseFormats : this.options.dateDataFormat,
                    min :          this.options.minDate

                };

            if (_.isObject(scope.options.renderOptions.kendoDateConfiguration)) {
                kendoOptions = scope.options.renderOptions.kendoDateConfiguration;
            }
            return _.extend(defaultOptions, kendoOptions);
        }
    });

    return $.fn.dcpDate;
});