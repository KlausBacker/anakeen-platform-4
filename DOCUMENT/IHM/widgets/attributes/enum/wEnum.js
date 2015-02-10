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
                chooseMessage: 'Select', // Message to display when no useFirstChoice is true and no value selected
                invalidEntry: "Invalid Entry",
                invertSelection: "Click to answer {{displayValue}}",
                selectMessage: 'Select',
                unselectMessage: 'UnSelect'
            },
            renderOptions: {
                kendoDropDownConfiguration: {
                    filter: "none",
                    autoBind: true
                },
                kendoComboBoxConfiguration: {
                    filter: "startswith"
                },
                kendoMultiSelectConfiguration: {
                    filter: "startswith"
                },
                editDisplay: "list", // possible values are ["list', 'vertical', 'horizontal', 'autoCompletion']'
                useFirstChoice: false,
                useSourceUri: false
            }
        },
        _initDom: function wEnumInitDom() {
            if (this._isMultiple()) {
                this.options.isMultiple = true;
            }


            if (this.getMode() === "read") {
                if (this._isMultiple()) {
                    this.options.attributeValues = _.toArray(this.options.attributeValue);
                    this.options.isMultiple = true;
                } else {
                    this.options.attributeValues = [this.options.attributeValue];
                    this.options.isMultiple = false;
                }
                this._super();
            }

            if (this.getMode() === "write") {
                if (this.options.options && this.options.options.eformat === "auto") {
                    this.options.renderOptions.useSourceUri = true;
                }
                this._initMainElementClass();
                if (this._isMultiple()) {
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion" :
                        case "list" :
                            this.multipleSelect();
                            break;
                        case "horizontal" :
                        case "vertical" :
                            this.checkboxButtons();
                            break;
                        case "bool":
                            throw new Error("Enum bool display cannot be applied to a multiple attribute : " + this.options.id);
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
                        case "bool" :
                            this.boolButtons();
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

            if (this.options.renderOptions.useSourceUri) {
                source = [this.options.attributeValue];
                selectedIndex = this.options.attributeValue.value;
            } else {

                _.each(this.options.sourceValues, function (enumItem) {
                    if (enumItem.key !== '' && enumItem.key !== ' ') {
                        item = {};


                        item.value = enumItem.key;
                        item.displayValue = enumItem.label;


                        // : no === because json encode use numeric cast when index is numeric
                        //noinspection JSHint
                        if (enumItem.key == scope.options.attributeValue.value) {
                            selectedIndex = source.length;
                            item.selected = true;
                        } else {
                            item.selected = false;
                        }

                        source.push(item);
                    }
                });
                if (selectedIndex === -1 && this.options.attributeValue && !_.isUndefined(this.options.attributeValue.value) && this.options.attributeValue.value !== null) {
                    selectedIndex = source.length;
                    source.push({
                        value: this.options.attributeValue.value,
                        displayValue: this.options.attributeValue.displayValue,
                        selected: true
                    });
                }
            }

            return {data: source, index: selectedIndex};
        },

        getMultipleEnumData: function wEnumGetMultipleEnumData() {
            var source = [];
            var selectedValues = [];
            var isIn = false;
            var item;
            var values = _.toArray(this.options.attributeValue);


            if (this.options.renderOptions.useSourceUri) {
                source = values;
                selectedValues = values;
            } else {
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
            }


            return {data: source, selectedValues: selectedValues};
        },

        retrieveItems: function wEnumretrieveItemse(done) {
            var scope = this;
            // Get enums data and defer render
            $.ajax({
                type: "GET",
                url: this.options.sourceUri,
                dataType: "json"
            }).done(function (result) {
                scope.options.sourceValues = result.data.enumItems;
                scope.options.renderOptions.useSourceUri = false;
                done(scope);
            }).fail(function (response) {
                $('body').trigger("notification", {
                    htmlMessage: "Enumerate " + scope.options.id,
                    message: response.statusText,
                    type: "error"
                });
            });
        },

        boolButtons: function wEnumBoolButtons() {
            var enumData;
            var tplOption = this.options;
            var labels;
            var scope = this;

            if (this.options.renderOptions.useSourceUri) {
                this.retrieveItems(function (theWidget) {
                    theWidget.boolButtons();
                });
                return;
            }

            enumData = this.getSingleEnumData();
            tplOption.enumValues = enumData.data;

            this.options.isMultiple = true; // Just to have checkbox

            this.element.append(Mustache.render(this._getTemplate('writeRadio'), this.options));
            this.options.isMultiple = false; // restore isMultiple : it never can be multiple
            labels = this.element.find("label");


            if (tplOption.enumValues[0].value === this.options.attributeValue.value) {
                this.element.find("input[type=checkbox]").removeAttr("checked");
                this.element.find(".dcpAttribute__value--enumlabel.selected").addClass("unselected").removeClass("selected");
            }

            this.element.find(".dcpAttribute__value--enumlabel").each(function (kItem) {
                if (tplOption.enumValues[kItem]) {
                    $(this).tooltip({
                        title: Mustache.render(scope.options.labels.invertSelection,
                            tplOption.enumValues[(kItem + 1) % 2])
                    });
                }
            });

            labels.on("click" + this.eventNamespace, "input", function (event) {
                event.preventDefault();
                // Invert selection
                _.some(tplOption.enumValues, function (item, kItem) {
                    if (scope.options.attributeValue.value === null || item.value !== scope.options.attributeValue.value) {
                        scope.setValue(item, event);
                        return true;
                    }
                    return false;
                });
            });


            this.getContentElements().each(function () {
                $(this).closest("label").addClass("k-button");

            });
            if (scope.options.attributeValue.value === null) {
                // Set to first enum item if empty
                var firstItem = tplOption.enumValues[0];
                if (firstItem) {
                    scope.setValue({value: firstItem.value, displayValue: firstItem.displayValue});
                }
            }

        },


        radioButtons: function wEnumRadioButtons() {
            var enumData;
            var tplOption = this.options;
            var labels;
            var scope = this;

            if (this.options.renderOptions.useSourceUri) {
                this.retrieveItems(function (theWidget) {
                    theWidget.radioButtons();
                });
                return;
            }

            enumData = this.getSingleEnumData();
            tplOption.enumValues = enumData.data;


            this.element.append(Mustache.render(this._getTemplate('writeRadio'), this.options));
            labels = this.element.find("label");


            labels.on("change" + this.eventNamespace, "input", function (event) {
                var newValue = {};
                newValue.value = $(this).val();
                newValue.displayValue = $(this).closest('label').text().trim();
                scope.setValue(newValue, event);
            });


            this.getContentElements().each(function () {
                $(this).closest("label").addClass("k-button");

            });
            if (scope.options.renderOptions.useFirstChoice && scope.options.attributeValue.value === null) {
                // Set to first enum item if empty
                var firstItem = tplOption.enumValues[0];
                if (firstItem) {
                    scope.setValue({value: firstItem.value, displayValue: firstItem.displayValue});
                }
            }

            this.element.tooltip({
                selector: '.dcpAttribute__value--enumlabel--text',
                title: function (a) {
                    if ($(this).closest("label").find("input").prop("checked")) {
                        return null;
                    } else {
                        return scope.options.labels.selectMessage + ' "' + $(this).text() + '"';
                    }
                }
            });

        },
        checkboxButtons: function wEnumRadioButtons() {
            var enumData;
            var tplOption = this.options;
            var labels;
            var scope = this;

            if (this.options.renderOptions.useSourceUri) {
                this.retrieveItems(function (theWidget) {
                    theWidget.checkboxButtons();
                });
                return;
            }
            enumData = this.getMultipleEnumData();
            tplOption.enumValues = enumData.data;

            this.element.append(Mustache.render(this._getTemplate('writeRadio'), this.options));
            labels = this.element.find("label");

            labels.on("change" + this.eventNamespace, "input", function (event) {

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

            this.element.tooltip({
                selector: '.dcpAttribute__value--enumlabel--text',
                title: function (a) {
                    if ($(this).closest("label").find("input").prop("checked")) {
                        return scope.options.labels.unselectMessage + ' "' + $(this).text() + '"';
                    } else {
                        return scope.options.labels.selectMessage + ' "' + $(this).text() + '"';
                    }
                }
            });

        },

        singleDropdown: function wEnumSingleDropdown() {
            var kendoOptions = this.getKendoOptions();
            var kddl;

            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            kddl = this.kendoWidget.kendoDropDownList(kendoOptions).data("kendoDropDownList");
            if (!this.options.renderOptions.useFirstChoice) {
                kddl.ul.find("li:first-child").addClass("placeholder");
            }
        },
        multipleSelect: function wEnumMultipleSelect() {
            var kendoOptions = this.getKendoOptions();
            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            this.kendoWidget.kendoMultiSelect(kendoOptions);
        },


        singleCombobox: function wEnumSingleCombobox() {
            var kendoOptions = this.getKendoOptions();
            var kddl;

            this.element.append(Mustache.render(this._getTemplate('write'), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");

            kddl = this.kendoWidget.kendoComboBox(kendoOptions).data("kendoComboBox");
            if (this.options.renderOptions.useSourceUri) {
                if (this.options.attributeValue.value === null) {
                    kddl.dataSource.data([]);
                    kddl.value('');
                } else {
                    kddl.dataSource.data([this.options.attributeValue]);
                    kddl.value(this.options.attributeValue.value);
                }
            } else {
                if (this.options.attributeValue.value) {
                    kddl.value(this.options.attributeValue.value);
                }
            }

        },


        /**
         *Set new value to widget
         * @param value value {value:...., displayValue} or array of {value:...., displayValue}
         * @param event
         */
        setValue: function wEnumSetValue(value, event) {
            var kddl, newValues;
            if (this.options.renderOptions.editDisplay === "bool") {
                // This display has only 2 values and cannot be set to null
                if (value.value === null) {
                    if (this.options.enumValues[0]) {
                        value = this.options.enumValues[0];
                    }
                }
            }
            this._super(value, event);
            if (this.getMode() === "write") {
                if (this._isMultiple()) {
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion":
                        case "list":
                            newValues = _.map(value, function (val) {
                                return val.value;
                            });
                            kddl = this.kendoWidget.data("kendoMultiSelect");
                            if (!_.isEqual(kddl.value(), newValues)) {
                                this.flashElement();
                                if (this.options.renderOptions.useSourceUri) {
                                    kddl.dataSource.data(value);
                                    kddl.value(newValues);
                                    kddl.dataSource.data([]); // Need to reset tu use server data
                                } else {
                                    kddl.value(newValues);
                                }
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
                                    $(this).prop("checked", true);
                                    $(this).closest("label").addClass("selected");
                                } else {
                                    $(this).prop("checked", false);
                                    $(this).closest("label").removeClass("selected");
                                }
                            });

                            break;
                        default:
                            throw new Error("Unknow Enum mode : " + this.options.renderOptions.editDisplay);

                    }
                } else {
                    switch (this.options.renderOptions.editDisplay) {
                        case "autoCompletion":
                            kddl = this.kendoWidget.data("kendoComboBox");
                            if (!_.isEqual(kddl.value(), value.value)) {
                                this.flashElement();

                                if (value.value !== null) {
                                    if (this.options.renderOptions.useSourceUri) {
                                        kddl.dataSource.data([value]);
                                    }
                                    kddl.value(value.value);
                                } else {
                                    if (this.options.renderOptions.useSourceUri) {
                                        kddl.dataSource.data([]);
                                    }
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
                        case "bool":
                            this.getContentElements().each(function (kItem) {
                                //noinspection JSHint
                                if ($(this).val() == value.value) {
                                    if (kItem > 0) {
                                        $(this).prop("checked", true);
                                        $(this).closest("label").addClass("selected").removeClass("unselected");
                                    } else {
                                        $(this).prop("checked", false);
                                        $(this).closest("label").addClass("unselected").removeClass("selected");
                                    }
                                } else {
                                    $(this).prop("checked", false);
                                    $(this).closest("label").removeClass("selected").removeClass("unselected");
                                }
                            });

                            break;
                        case "horizontal":
                        case "vertical":
                            this.getContentElements().each(function () {
                                //noinspection JSHint
                                if ($(this).val() == value.value) {
                                    $(this).prop("checked", true);
                                    $(this).closest("label").addClass("selected");
                                } else {
                                    $(this).prop("checked", false);
                                    $(this).closest("label").removeClass("selected");
                                }
                            });

                            break;
                        default:
                            throw new Error("Unknow Enum mode : " + this.options.renderOptions.editDisplay);

                    }
                }
            } else {
                this._super(value, event);
                this.redraw();
            }
        },
        /**
         * method use for transport multiselect widget
         * @param options
         */
        autocompleteRequestEnum: function wEnumAutocompleteRequestEnum(options) {
            var filter = {};

            if (options.data.filter && options.data.filter.filters && options.data.filter.filters.length > 0) {
                filter = {
                    keyword: options.data.filter.filters[0].value,
                    operator: options.data.filter.filters[0].operator
                };
            }

            if (!this.options.sourceUri) {
                throw new Error("Enum : sourceUri must be defined if renderOption useSourceUri is set to true");
            }
            //options.data.keyword=
            $.ajax({
                type: "GET",
                url: this.options.sourceUri,
                data: filter,
                dataType: "json", // "jsonp" is required for cross-domain requests; use "json" for same-domain requests
                success: function (result) {
                    var info = [];
                    _.each(result.data.enumItems, function (enumItem) {
                        info.push({
                            value: enumItem.key,
                            displayValue: enumItem.label
                        });
                    });
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
                defaultOptions = {};

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
                        var newValues = [];
                        _.each(kdData, function (val) {
                            newValues.push({value: val.value, displayValue: val.displayValue});
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
                    /*valuePrimitive: true,*/
                    optionLabel: this.options.labels.chooseMessage + ' ',
                    dataTextField: "displayValue",
                    dataValueField: "value",
                    dataSource: source.data,
                    index: source.index,
                    autoBind: false,
                    change: function (event) {

                        if (this.value() && this.selectedIndex === -1) {
                            scope.setError(scope.options.labels.invalidEntry);
                            scope._getFocusInput().each(function () {
                                this.focus();
                            });
                        } else {
                            scope.setError(null);

                            var newValue = {value: this.value(), displayValue: this.text()};
                            scope.setValue(newValue, event);
                        }
                    },
                    dataBound: function (e) {
                        if (scope.options.renderOptions.useFirstChoice && scope.options.attributeValue.value === null) {
                            // Set to first enum item if empty
                            var firstItem = this.dataSource.at(0);
                            if (firstItem) {
                                scope.setValue({value: firstItem.value, displayValue: firstItem.displayValue});
                            }
                        }
                    }
                };

                if (this.options.renderOptions.editDisplay === "autoCompletion") {

                    defaultOptions.index = -1;
                    defaultOptions.value = this.options.attributeValue.value;
                    defaultOptions.placeholder = this.options.labels.chooseMessage;

                    if (_.isObject(scope.options.renderOptions.kendoComboBoxConfiguration)) {
                        kendoOptions = scope.options.renderOptions.kendoComboBoxConfiguration;
                    }
                } else {
                    if (_.isObject(scope.options.renderOptions.kendoDropDownConfiguration)) {
                        kendoOptions = scope.options.renderOptions.kendoDropDownConfiguration;
                    }
                }
            }

            if (this.options.renderOptions.useSourceUri) {
                defaultOptions.dataSource = {
                    data: source.data,
                    index: source.index,
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
            return _.extend(defaultOptions, kendoOptions);
        },

        getType: function () {
            return "enum";
        },

        _destroy: function _destroy() {
            if (this.kendoWidget && this.kendoWidget.data("kendoDropDownList")) {
                this.kendoWidget.data("kendoDropDownList").destroy();
            }
            if (this.kendoWidget && this.kendoWidget.data("kendoComboBox")) {
                this.kendoWidget.data("kendoComboBox").destroy();
            }
            if (this.kendoWidget && this.kendoWidget.data("kendoMultiSelect")) {
                this.kendoWidget.data("kendoMultiSelect").destroy();
            }
            this._super();
        }

    });

    return $.fn.dcpEnum;
});