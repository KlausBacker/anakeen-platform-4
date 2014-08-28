/*global define, _super*/
define([
    'underscore',
    'mustache',
    'widgets/attributes/wAttribute',
    'kendo/kendo.multiselect',
    'kendo/kendo.dropdownlist'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpEnum", $.dcp.dcpAttribute, {

        options: {
            type: "enum",
            editFormat: "list", // possible values are ["list', 'vertical', 'horizontal', 'bool']'
            canUseEmpty: true,
            canChooseOther: false,
            canAddNewItem: false,
            sourceValues: [], // {key:value, ...}
            labels: {
                chooseMessage: 'Select' // Message to display when canUseEmpty is true and no value selected
            },
            renderOptions: {
                kendoDropDownConfiguration: {},
                kendoMultiSelectConfiguration: {}
            }
        },
        _initDom: function wEnumInitDom() {
            if (this.getMode() === "read") {
                if (this._isMultiple()) {
                    this.options.values = _.toArray(this.options.value);
                    this.options.isMultiple = true;
                } else {
                    this.options.values = [this.options.value];
                    this.options.isMultiple = false;
                }
this._super();
            }

            if (this.getMode() === "write") {

                this._initMainElemeentClass();
                if (this._isMultiple()) {

                    this.multipleSelect();
                } else {
                    this.singleDropdown();
                }
            }
        },
        getSingleEnumData: function wEnumGetSingleEnumData() {
            var source = [];
            var scope = this;
            var selectedIndex = -1;
            var item;
            _.each(this.options.sourceValues, function (displayValue, rawValue) {
                if (rawValue !== '' && rawValue !== ' ') {
                    item = {};


                    item.value = rawValue;
                    item.displayValue = displayValue;


                    // : no === because json encode use numeric cast when index is numeric
                    //noinspection JSHint
                    if (rawValue == scope.options.value.value) {
                        selectedIndex = source.length;
                        item.selected = true;
                    } else {
                        item.selected = false;
                    }

                    source.push(item);
                }
            });

            if (selectedIndex === -1 && this.options.value.value !== null) {
                selectedIndex = source.length;
                source.push({value: this.options.value.value, displayValue: this.options.value.displayValue, selected: true});
            }
            return {data: source, index: selectedIndex};
        },

        getMultipleEnumData: function wEnumGetMultipleEnumData() {
            var source = [];
            var scope = this;
            var selectedValues = [];
            var isIn = false;
            var item;
            var values = _.toArray(scope.options.value);
            _.each(this.options.sourceValues, function (displayValue, rawValue) {
                item = {};
                item.value = rawValue;
                item.displayValue = displayValue;

                isIn = _.some(values, function (aValue) {
                    //noinspection JSHint
                    return (aValue.value == rawValue);
                });


                // : no === because json encode use numeric cast when index is numeric
                //noinspection JSHint
                if (isIn) {

                    selectedValues.push(rawValue);
                }

                source.push(item);
            });

            return {data: source, selectedValues: selectedValues};
        },

        singleDropdown: function wEnumSingleDropdown() {
            var kendoOptions = this.getKendoOptions();
            var kddl;

            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");


            kddl = this.kendoWidget.kendoDropDownList(kendoOptions).data("kendoDropDownList");

            if (this.options.canUseEmpty) {
                kddl.ul.find("li:first-child").addClass("placeholder");
            }
        },
        multipleSelect: function wEnumMultipleSelect() {
            var kendoOptions = this.getKendoOptions();
            var source = this.getMultipleEnumData();
            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            this.kendoWidget.kendoMultiSelect(kendoOptions);
        },
        setValue: function (value, event) {
            var kddl;
            this._super(value, event);
            if (this.getMode() === "write") {
                if (this._isMultiple()) {
                    var newValues = _.map(value, function (val) {
                        return  val.value;
                    });
                    kddl = this.kendoWidget.data("kendoMultiSelect");
                    if (!_.isEqual(kddl.value(), newValues)) {
                        this.flashElement();
                        kddl.value(newValues);
                    }
                } else {
                    kddl = this.kendoWidget.data("kendoDropDownList");
                    if (value.value === '' || value.value === null) {
                        kddl.span.addClass("placeholder");
                    } else {
                        kddl.span.removeClass("placeholder");
                    }
                    if (!_.isEqual(kddl.value(), value.value)) {
                        this.flashElement();
                        kddl.value(value.value);
                    }
                }
            }
        },

        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoOptions: function wEnumGetKendoOptions() {
            var scope = this,
                source = null,
                kendoOptions = {},
                defaultOptions = { };

            if (this._isMultiple()) {

                source = this.getMultipleEnumData();

                defaultOptions = {
                    dataTextField: "displayValue",
                    dataValueField: "value",
                    dataSource: source.data,
                    placeholder: this.options.labels.chooseMessage,
                    value: source.selectedValues,

                    change: function (event) {
                        event.preventDefault(); // no fire change event
                        // set in case of delete item
                        var oldValues = scope.getMultipleEnumData().data;
                        var displayValue;
                        var newValues = [];
                        _.each(this.value(), function (val) {
                            displayValue = _.where(oldValues, {value: val});
                            if (displayValue.length > 0) {
                                displayValue = displayValue[0].displayValue;
                            } else {
                                displayValue = "-";
                            }
                            newValues.push({value: val, displayValue: displayValue});
                        });
                        scope.setValue(newValues, event);
                    }
                };
                if (_.isObject(scope.options.renderOptions.kendoMultiSelectConfiguration)) {
                    kendoOptions = scope.options.renderOptions.kendoMultiSelectConfiguration;
                }
            } else {
                source = this.getSingleEnumData();
                defaultOptions = {
                    valuePrimitive: true,
                    optionLabel: (this.options.canUseEmpty) ? (this.options.labels.chooseMessage + ' ') : '',
                    dataTextField: "displayValue",
                    dataValueField: "value",
                    dataSource: source.data,
                    index: source.index,
                    change: function (event) {
                        var newValue = {value: this.value(), displayValue: this.text()};


                        scope.setValue(newValue, event);
                    }
                };
                if (_.isObject(scope.options.renderOptions.kendoDropDownConfiguration)) {
                    kendoOptions = scope.options.renderOptions.kendoDropDownConfiguration;
                }
            }


            return _.extend(defaultOptions, kendoOptions);
        },

        getType: function () {
            return "enum";
        }

    });

    return $.fn.dcpEnum;
});