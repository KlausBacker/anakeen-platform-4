/*global define, _super*/
define([
    'underscore',
    'mustache',
    'widgets/attributes/wAttribute',
    'kendo/kendo.multiselect',
    'kendo/kendo.combobox',
    'kendo/kendo.dropdownlist'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpEnum", $.dcp.dcpAttribute, {

        options: {
            type: "enum",
            canChooseOther: false,
            canAddNewItem: false,
            sourceValues: [], // [{key:"the key", label:"the label"}, ...}]
            sourceUri: null, // when enum definition is dynamically get by server request
            labels: {
                chooseMessage: 'Select' // Message to display when no useFirstChoice is true and no value selected
            },
            renderOptions: {
                kendoDropDownConfiguration: {
                    filter: "none"
                },
                kendoComboBoxConfiguration: {
                    filter: "startswith"
                },
                kendoMultiSelectConfiguration: {
                    filter: "startswith"
                },
                editDisplay: "list", // possible values are ["list', 'vertical', 'horizontal', 'autoCompletion']'
                useFirstChoice: false
            }
        },
        _initDom: function wEnumInitDom() {
            if (this._isMultiple()) {
                this.options.isMultiple = true;
            }
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
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion" :
                        case "list" :
                            this.multipleSelect();
                            break;
                        case "horizontal" :
                        case "vertical" :
                            this.checkboxButtons(true);
                            break;
                        default:
                            this.multipleSelect();
                    }
                } else {
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion" :
                            this.singleCombobox();
                            break;
                        case "list" :
                            this.singleDropdown();
                            break;
                        case "horizontal" :
                        case "vertical" :
                            this.radioButtons();
                            break;
                        default:
                            this.singleDropdown();
                    }
                }
            }
        },
        getSingleEnumData: function wEnumGetSingleEnumData() {
            var source = [];
            var scope = this;
            var selectedIndex = -1;
            var item;

            if (this.options.sourceValues.length === 0) {
                source = [this.options.value];
                selectedIndex = this.options.value.value;
            } else {

                _.each(this.options.sourceValues, function (enumItem) {
                    if (enumItem.key !== '' && enumItem.key !== ' ') {
                        item = {};


                        item.value = enumItem.key;
                        item.displayValue = enumItem.label;


                        // : no === because json encode use numeric cast when index is numeric
                        //noinspection JSHint
                        if (enumItem.key == scope.options.value.value) {
                            selectedIndex = source.length;
                            item.selected = true;
                        } else {
                            item.selected = false;
                        }

                        source.push(item);
                    }
                });
                if (selectedIndex === -1 && this.options.value && !_.isUndefined(this.options.value.value) && this.options.value.value !== null) {
                    selectedIndex = source.length;
                    source.push({value: this.options.value.value, displayValue: this.options.value.displayValue, selected: true});
                }
            }
            console.log("single info", this.options.sourceValues, {data: source, index: selectedIndex});

            return {data: source, index: selectedIndex};
        },

        getMultipleEnumData: function wEnumGetMultipleEnumData() {
            var source = [];
            var selectedValues = [];
            var isIn = false;
            var item;
            var values = _.toArray(this.options.value);
            _.each(this.options.sourceValues, function (enumItem) {
                item = {};
                item.value = enumItem.key;
                item.displayValue = enumItem.label;
                item.selected = false;
                isIn = _.some(values, function (aValue) {
                    //noinspection JSHint
                    return (aValue.value == enumItem.key);
                });


                // : no === because json encode use numeric cast when index is numeric
                //noinspection JSHint
                if (isIn) {
                    item.selected = true;
                    selectedValues.push(enumItem.key);
                }

                source.push(item);
            });

            if (this.options.sourceUri) {
                source = values;
                selectedValues = values;
            }

            return {data: source, selectedValues: selectedValues};
        },


        radioButtons: function wEnumRadioButtons() {
            var enumData = this.getSingleEnumData();
            var tplOption = this.options;
            var labels;
            var scope = this;

            tplOption.enumValues = enumData.data;

            this.element.append(Mustache.render(this._getTemplate('writeRadio'), this.options));
            labels = this.element.find("label");

            labels.on("change." + this.eventNamespace, "input", function (event) {
                var newValue = {};
                newValue.value = $(this).val();
                newValue.displayValue = $(this).closest('label').text().trim();
                scope.setValue(newValue, event);
            });

            this.getContentElements().each(function () {
                $(this).closest("label").addClass("k-button");

            });

        },
        checkboxButtons: function wEnumRadioButtons() {
            var enumData = this.getMultipleEnumData();
            var tplOption = this.options;
            var labels;
            var scope = this;

            tplOption.enumValues = enumData.data;

            this.element.append(Mustache.render(this._getTemplate('writeRadio'), this.options));
            labels = this.element.find("label");

            labels.on("change." + this.eventNamespace, "input", function (event) {

                var newValue = [];

                scope.getContentElements().each(function () {
                    if ($(this).prop("checked")) {
                        var itemValue = {};
                        itemValue.value = $(this).val();
                        itemValue.displayValue = $(this).closest('label').text().trim();
                        newValue.push(itemValue);
                    }
                });

                scope.setValue(newValue, event);
            });

            this.getContentElements().each(function () {
                $(this).closest("label").addClass("k-button");

            });

        },

        singleDropdown: function wEnumSingleDropdown() {
            var kendoOptions = this.getKendoOptions();
            var kddl;

            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");


            kddl = this.kendoWidget.kendoDropDownList(kendoOptions).data("kendoDropDownList");

            if (!this.options.renderOptions.useFirstChoice) {
                kddl.ul.find("li:first-child").addClass("placeholder");
            }


        },
        multipleSelect: function wEnumMultipleSelect() {
            var kendoOptions = this.getKendoOptions();
            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            this.kendoWidget.kendoMultiSelect(kendoOptions);
        },


        singleCombobox: function wEnumSingleCombobox() {
            var kendoOptions = this.getKendoOptions();
            var kddl;

            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");


            kddl=this.kendoWidget.kendoComboBox(kendoOptions).data("kendoComboBox");

                                kddl.dataSource.data([this.options.value]);
                                kddl.value(this.options.value.value);

        },


        /**
         *Set new value to widget
         * @param value value {value:...., displayValue} or array of {value:...., displayValue}
         * @param event
         */
        setValue: function wEnumSetValue(value, event) {
            var kddl, newValues;
            this._super(value, event);
            if (this.getMode() === "write") {
                console.log("widgetSetValue");
                if (this._isMultiple()) {
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion":
                            newValues = _.map(value, function (val) {
                                return  val.value;
                            });
                            console.log("set widget to ", newValues);
                            kddl = this.kendoWidget.data("kendoMultiSelect");
                            if (!_.isEqual(kddl.value(), newValues)) {
                                this.flashElement();
                                console.log("set data source to", value);
                                kddl.dataSource.data(value);
                                kddl.value(newValues);
                                kddl.dataSource.data([]); // Need to reset tu use server data
                            }
                            break;
                        case "list":
                            newValues = _.map(value, function (val) {
                                return  val.value;
                            });
                            kddl = this.kendoWidget.data("kendoMultiSelect");
                            if (!_.isEqual(kddl.value(), newValues)) {
                                this.flashElement();
                                kddl.value(newValues);
                            }
                            break;
                        case "horizontal":
                        case "vertical":
                            this.getContentElements().each(function () {
                                var inputValue = $(this).val();

                                var isIn = _.some(value, function (x) {
                                    //noinspection JSHint
                                    return (x.value == inputValue);
                                });
                                if (isIn) {
                                    $(this).attr("checked", "checked");
                                    $(this).closest("label").addClass("selected");
                                } else {
                                    $(this).removeAttr("checked");
                                    $(this).closest("label").removeClass("selected");
                                }
                            });

                            break;
                    }
                } else {
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion":
                            kddl = this.kendoWidget.data("kendoComboBox");
                            if (!_.isEqual(kddl.value(), value.value)) {
                                this.flashElement();

                                console.log("kddl", kddl, value);
                                if (value.value !== null) {
                                kddl.dataSource.data([value]);
                                kddl.value(value.value);
                                } else {
                                    kddl.dataSource.data([]);
                                    kddl.value('');
                                }
                            }
                            break;
                        case "list":
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
                            break;
                        case "horizontal":
                        case "vertical":

                            this.getContentElements().each(function () {
                                //noinspection JSHint
                                if ($(this).val() == value.value) {
                                    $(this).attr("checked", "checked");
                                    $(this).closest("label").addClass("selected");
                                } else {
                                    $(this).removeAttr("checked");
                                    $(this).closest("label").removeClass("selected");
                                }
                            });

                            break;
                    }
                }
            }
        },
        /**
         * method use for transport multiselect widget
         * @param options
         */
        autocompleteRequestEnum: function (options) {
            var filter = {
            };

            if (options.data.filter && options.data.filter.filters && options.data.filter.filters.length > 0) {
                filter = {
                    keyword: options.data.filter.filters[0].value,
                    operator: options.data.filter.filters[0].operator
                };
            }
            //options.data.keyword=
            $.ajax({
                type: "GET",
                url: this.options.sourceUri,
                data: filter,
                dataType: "json", // "jsonp" is required for cross-domain requests; use "json" for same-domain requests
                success: function (result) {
                    console.log("results", result);
                    console.log(result);
                    var info = [];
                    _.each(result.data.enumItems, function (enumItem) {
                        info.push({
                            value: enumItem.key,
                            displayValue: enumItem.label
                        });
                    });
                    console.log(info);
                    // notify the data source that the request succeeded
                    options.success(info);
                },
                error: function (result) {
                    // notify the data source that the request failed
                    options.error(result);
                }
            });
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

                        var kdData = _.toArray(scope.kendoWidget.data("kendoMultiSelect").dataItems());
                        console.log("kdData", kdData);
                        var displayValue;
                        var newValues = [];
                        _.each(kdData, function (val) {
                            newValues.push({value: val.value, displayValue: val.displayValue});
                        });
                        console.log("change to ", newValues);
                        scope.setValue(newValues, event);
                    }
                };

                if (this.options.renderOptions.editDisplay === "autoCompletion") {


                    defaultOptions.autoBind = false;
                    defaultOptions.dataSource = {

                        type: "json",
                        serverFiltering: true,
                        minLength: 0,
                        transport: {
                            //read : _.bind(scope.autocompleteRequestRead, scope)
                            read: _.bind(scope.autocompleteRequestEnum, scope)
                            //read : scope.options.autocompleteRequest
                        }
                    };
                }
                if (_.isObject(scope.options.renderOptions.kendoMultiSelectConfiguration)) {
                    kendoOptions = scope.options.renderOptions.kendoMultiSelectConfiguration;
                }
            } else {
                source = this.getSingleEnumData();

console.log("single source", source);

                defaultOptions = {
                    valuePrimitive: true,
                    optionLabel: (!this.options.renderOptions.useFirstChoice) ? (this.options.labels.chooseMessage + ' ') : '',
                    dataTextField: "displayValue",
                    dataValueField: "value",
                    dataSource: source.data,
                    index: source.index,
                    change: function (event) {
                        var newValue = {value: this.value(), displayValue: this.text()};
                        scope.setValue(newValue, event);
                    }
                };

                if (this.options.renderOptions.editDisplay === "autoCompletion") {


                    defaultOptions.placeholder = this.options.labels.chooseMessage;
                    defaultOptions.autoBind = false;
                    defaultOptions.dataSource = {
data: source.data,
                        type: "json",
                        serverFiltering: true,
                        minLength: 0,
                        transport: {
                            //read : _.bind(scope.autocompleteRequestRead, scope)
                            read: _.bind(scope.autocompleteRequestEnum, scope)
                            //read : scope.options.autocompleteRequest
                        }
                    };
                    if (_.isObject(scope.options.renderOptions.kendoComboBoxConfiguration)) {
                        kendoOptions = scope.options.renderOptions.kendoComboBoxConfiguration;
                    }
                } else {
                    if (_.isObject(scope.options.renderOptions.kendoDropDownConfiguration)) {
                        kendoOptions = scope.options.renderOptions.kendoDropDownConfiguration;
                    }
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