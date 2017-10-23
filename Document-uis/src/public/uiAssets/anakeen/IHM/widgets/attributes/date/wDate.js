/*global define, _super*/

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'underscore',
            'mustache',
            'dcpDocument/widgets/attributes/text/wText',
            'kendo-culture-fr'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window._, window.Mustache);
    }
}(window, function wDate($, _, Mustache)
{
    'use strict';

    $.widget("dcp.dcpDate", $.dcp.dcpText, {

        options: {
            type: "date",
            minDate: new Date(1700, 0, 1),
            renderOptions: {
                kendoDateConfiguration: {
                    parseFormats: ["yyyy-MM-dd"],
                    format: null
                }
            },
            labels: {
                invalidDate: "Invalid Date"
            }
        },

        kendoWidgetClass: "kendoDatePicker",

        _initDom: function wDateInitDom()
        {

            if (this.options.renderOptions.kendoDateConfiguration.format) {
                this.options.attributeValue.displayValue = this.formatDate(this.parseDate(this.options.attributeValue.value));
            }
            if (this.getMode() === "read") {
                if (this.options.renderOptions.format) {
                    this.options.attributeValue.formatValue = Mustache.render(this.options.renderOptions.format || "",
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

            if (this.element.find(".dcpAttribute__content__buttons button").length === 0) {
                this.element.find(".k-picker-wrap").addClass("dcpAttribute__content__nobutton");
            }
        },

        _initChangeEvent: function wDate_initChangeEvent()
        {
            // set by widget if no autocomplete
            if (this.options.hasAutocomplete) {
                this._super();
            }
        },

        setValue: function wDateSetValue(value)
        {
            // this._super.(value);
            // Don't call dcpText::setValue()

            var originalValue, originalDate;
            value = _.clone(value);
            if (_.has(value, "value") && !_.has(value, "displayValue")) {
                value.displayValue = this.formatDate(this.parseDate(value.value));
            } else {
                if (this.options.renderOptions.kendoDateConfiguration.format) {
                    value.displayValue = this.formatDate(this.parseDate(value.value));
                }
            }

            //noinspection JSPotentiallyInvalidConstructorUsage
            $.dcp.dcpAttribute.prototype.setValue.call(this, value);

            if (this.getMode() === "write") {
                originalValue = this.convertDateToPseudoIsoString(this.kendoWidget.data(this.kendoWidgetClass).value());
                // : explicit lazy equal
                //noinspection JSHint, EqualityComparisonWithCoercionJS
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
                    if (originalValue || value.value) {
                        this.flashElement();
                    }
                }
            } else
                if (this.getMode() === "read") {
                    this.getContentElements().text(value.displayValue);
                } else {
                    throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
                }
        },

        _activateDate: function wDateSetValueActivateDate(inputValue)
        {
            var currentWidget = this;
            var kOptions = this.getKendoOptions();

            kOptions.change = function wDateChange()
            {
                if (this.value() !== null) {
                    // only valid date are setted
                    // wrong date are set by blur event
                    var isoDate = currentWidget.convertDateToPseudoIsoString(this.value());
                    // Need to set by widget to use raw date
                    currentWidget.setValue({value: isoDate, displayValue: inputValue.val()});
                }
            };

            inputValue.kendoDatePicker(kOptions);

            // Workaround for date paste : change event is not trigger in this case
            inputValue.on("paste" + this.eventNamespace, function wDatePaste()
            {
                var $input = $(this);
                _.defer(function wDatePasteAfter()
                {
                    // set Value after
                    inputValue.data("kendoDatePicker").value($input.val().trim());
                    inputValue.data("kendoDatePicker").trigger("change");
                });
            });

            this._controlDate(inputValue);
        },

        _controlDate: function wDateControlDate(inputValue)
        {
            var currentWidget = this;
            inputValue.on('blur' + this.eventNamespace, function validateDate(/*event*/)
            {
                var dateValue = $(this).val().trim();

                if (currentWidget.invalidDate) {
                    currentWidget.setError(null); // clear Error before
                    currentWidget.invalidDate = false;
                }

                currentWidget._setVisibilitySavingMenu("visible");

                if (dateValue) {
                    if (!currentWidget.parseDate(dateValue)) {

                        currentWidget._setVisibilitySavingMenu("disabled");
                        _.defer(function wDateFocus()
                        {
                            currentWidget._getFocusInput().focus();
                        });
                        currentWidget.invalidDate = true;
                        currentWidget.setError(currentWidget.options.labels.invalidDate);
                    }
                }
            });
        },

        formatDate: function wDateFormatDate(value)
        {
            if (this.options.renderOptions.kendoDateConfiguration.format) {
                return kendo.toString(value, this.options.renderOptions.kendoDateConfiguration.format);
            }
            return kendo.toString(value, "d");
        },

        parseDate: function wDateParseDate(value)
        {
            var parseFormat = this.options.renderOptions.kendoDateConfiguration.parseFormats;
            var goodDate = kendo.parseDate(value);
            if (goodDate) {
                return goodDate;
            }
            if (this.options.renderOptions.kendoDateConfiguration.format) {
                parseFormat.push(this.options.renderOptions.kendoDateConfiguration.format);
            }
            return kendo.parseDate(value, parseFormat);
        },

        convertDateToPseudoIsoString: function wDateconvertDateToPseudoIsoString(dateObject)
        {
            if (dateObject && _.isDate(dateObject)) {
                return dateObject.getFullYear() + '-' + this.padNumber(dateObject.getMonth() + 1) + '-' + this.padNumber(dateObject.getDate());
            }
            return '';
        },

        padNumber: function wDatePadNumber(number)
        {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        },

        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoOptions: function wDategetKendoOptions()
        {
            var currentWidget = this,
                kendoOptions = {},
                defaultOptions = {
                    min: this.options.minDate
                };

            if (_.isObject(currentWidget.options.renderOptions.kendoDateConfiguration)) {
                kendoOptions = currentWidget.options.renderOptions.kendoDateConfiguration;
            }

            return _.extend(defaultOptions, kendoOptions);
        },

        close: function wDate_close()
        {
            if (this.kendoWidget.data(this.kendoWidgetClass)) {
                this.kendoWidget.data(this.kendoWidgetClass).close();
            }
            return this._super();
        },

        _destroy: function wDateDestroy()
        {
            if (this.kendoWidget.data(this.kendoWidgetClass)) {
                this.kendoWidget.data(this.kendoWidgetClass).destroy();
            }
            this._super();
        }
    });

    return $.fn.dcpDate;
}));