/*global define */
(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'underscore',
            'mustache',
            'dcpDocument/widgets/attributes/wAttribute',
            'kendo/kendo.multiselect',
            'kendo/kendo.combobox',
            'kendo/kendo.dropdownlist'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window._, window.Mustache);
    }
}(window, function require_wenum($, _, Mustache, dcpAttribute, kendo)
{
    'use strict';

    var localeCompareSupportsLocales = (function testLocaleCompareSupportsLocales()
    {
        try {
            'foo'.localeCompare('bar', 'i');
        } catch (e) {
            return e.name === 'RangeError';
        }
        return false;
    })();

    $.widget("dcp.dcpEnum", $.dcp.dcpAttribute, {

        options: {
            type: "enum",
            sourceValues: [], // [{key:"the key", label:"the label"}, ...}]
            sourceUri: null, // when enum definition is dynamically get by server request
            labels: {
                invalidEntry: "Invalid Entry",
                invertSelection: "Click to answer {{displayValue}}",
                selectMessage: 'Select',
                unselectMessage: 'UnSelect',
                chooseAnotherChoice: "Choose another choice",
                selectAnotherChoice: "Select choice",
                displayOtherChoice: "** {{value}} **"
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
                useSourceUri: false,
                useOtherChoice: false,
                placeHolder: 'Select', // Message to display when no useFirstChoice is true and no value selected
                orderBy: false
            }
        },
        _initDom: function wEnumInitDom()
        {
            var currentWidget = this;
            if (this.getMode() === "write" &&
                this.options.renderOptions &&
                this.options.renderOptions.orderBy) {
                switch (this.options.renderOptions.orderBy) {
                    case 'key':
                        if (this.options.sourceValues && this.options.sourceValues.sort) {
                            this.options.sourceValues = this.options.sourceValues.sort(function wEnum_initDomSortKey(a, b)
                            {
                                if (a.key === b.key) {
                                    return 0;
                                }
                                return (a.key < b.key) ? -1 : 1;
                            });
                        }
                        break;
                    case 'label':
                        if (this.options.sourceValues && this.options.sourceValues.sort) {
                            this.options.sourceValues = this.options.sourceValues.sort(function wEnum_initDomSortLabel(a, b)
                            {
                                if (localeCompareSupportsLocales) {
                                    return String(a.label).localeCompare(String(b.label), currentWidget.options.locale, {
                                        numeric: true,
                                        caseFirst: false
                                    });
                                } else {
                                    return String(a.label).localeCompare(String(b.label));
                                }
                            });
                        }
                        break;
                }
            }
            if (this._isMultiple()) {
                this.options.isMultiple = true;
                _.each(this.options.attributeValue, function wEnumDisplayOthers(singleValue)
                {
                    if (singleValue) {
                        singleValue.exists = (singleValue.exists !== false);

                        if (singleValue.exists === false) {
                            singleValue.displayValue = Mustache.render(currentWidget.options.labels.displayOtherChoice, singleValue);
                        }
                    }
                });
            } else {
                if (this.options.attributeValue.exists === false) {
                    this.options.attributeValue.displayValue = Mustache.render(this.options.labels.displayOtherChoice, this.options.attributeValue);
                }
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

                if (this.options.renderOptions.useOtherChoice === true) {
                    this.options.otherUuid = _.uniqueId("enum");
                }
                if (this.options.index >= 0) {
                    var enumIndex = this.element.closest("table").data("enumIndex");

                    if (!enumIndex) {
                        enumIndex = {};
                    }
                    if (!enumIndex[this.options.id]) {
                        enumIndex[this.options.id] = 0;
                    }
                    this.options.inArray = true;
                    this.options.enumIndex = enumIndex[this.options.id];
                    enumIndex[this.options.id]++;
                    this.element.closest("table").data("enumIndex", enumIndex);
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
                    this.element.on("click" + this.eventNamespace, '.dcpAttribute__value--enum--button', function wEnumSelectClick(event)
                    {
                        event.preventDefault();
                        currentWidget.kendoWidgetObject.search("");
                    });
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

            this.noButtonDisplay();
        },
        getSingleEnumData: function wEnumGetSingleEnumData()
        {
            var source = [];
            var scope = this;
            var selectedIndex = -1;
            var item;

            if (this.options.renderOptions.useSourceUri) {
                source = [this.options.attributeValue];
                selectedIndex = this.options.attributeValue.value;
            } else {
                _.each(this.options.sourceValues, function wEnum_prepareValue(enumItem)
                {
                    if (enumItem.key !== '' && enumItem.key !== ' ') {
                        item = {};

                        item.value = enumItem.key;
                        item.displayValue = enumItem.label || '';
                        item.exists = enumItem.exists !== false;
                        item.enumUuid = _.uniqueId("enum");

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
                        displayValue: this.options.attributeValue.displayValue || '',
                        selected: true,
                        exists: false,
                        enumUuid: _.uniqueId("enum")
                    });
                }
            }

            return {data: source, index: selectedIndex};
        },

        getMultipleEnumData: function wEnumGetMultipleEnumData()
        {
            var source = [];
            var selectedValues = [];
            var isIn = false;
            var item;
            var values = [];

            if (this.options.attributeValue && this.options.attributeValue.value !== null) {
                values = _.toArray(this.options.attributeValue);
            }

            if (this.options.renderOptions.useSourceUri) {
                source = values;
                selectedValues = _.pluck(values, "value");
            } else {

                _.each(this.options.sourceValues, function wEnum_prepareMultipleValue(enumItem)
                {
                    item = {};
                    item.value = enumItem.key;
                    item.displayValue = enumItem.label || '';
                    item.selected = false;
                    item.exists = enumItem.exists !== false;
                    item.enumUuid = _.uniqueId("enum");
                    isIn = _.some(values, function wEnum_findSelected(aValue)
                    {
                        //noinspection JSHint
                        return (aValue.value == enumItem.key);
                    });

                    // : no === because json encode use numeric cast when index is numeric
                    //noinspection JSHint
                    if (isIn) {
                        item.selected = true;
                    }

                    source.push(item);
                });

                _.each(values, function wEnum_addOtherValues(singleValue)
                {
                    if (singleValue.value !== null && singleValue.value !== '') {
                        if (singleValue.exists === false) {
                            item = {};
                            item.value = singleValue.value;
                            item.displayValue = singleValue.displayValue;
                            item.selected = true;
                            item.exists = false;
                            item.enumUuid = _.uniqueId("enum");
                            source.push(item);
                        }
                        selectedValues.push(singleValue.value);
                    }
                });
            }

            return {
                data: source,
                selectedValues: selectedValues
            };
        },

        retrieveItems: function wEnumretrieveItemse(done)
        {
            var scope = this,
                requestData = {};

            if (this.options.renderOptions.orderBy) {
                requestData.orderBy = this.options.renderOptions.orderBy;
            }

            // Get enums data and defer render
            $.ajax({
                type: "GET",
                url: this.options.sourceUri,
                dataType: "json",
                data: requestData
            }).done(function wEnum_retrieveDone(result)
            {
                scope.options.sourceValues = result.data.enumItems;
                scope.options.renderOptions.useSourceUri = false;
                done(scope);
            }).fail(function wEnum_retrieveFail(response)
            {
                $('body').trigger("notification", {
                    htmlMessage: "Enumerate " + scope.options.id,
                    message: response.statusText,
                    type: "error"
                });
            });
        },

        noButtonDisplay: function wEnumNoDisplayButton()
        {
            if (this.element.find(".dcpAttribute__content__buttons button").length === 0) {
                this.element.find(".dcpAttribute__value--enumbuttons").addClass("dcpAttribute__content__nobutton");
                this.element.find(".dcpAttribute__content__buttons").hide();
            }
        },

        boolButtons: function wEnumBoolButtons()
        {
            var enumData;
            var tplOption = this.options;
            var scope = this;

            if (this.options.renderOptions.useSourceUri) {
                this.retrieveItems(function wEnum_onRetrieveDone(theWidget)
                {
                    theWidget.boolButtons();
                });
                return;
            }

            enumData = this.getSingleEnumData();
            tplOption.enumValues = enumData.data;

            if (tplOption.enumValues.length > 1) {
                tplOption.enumValues[0].off = true;
                tplOption.enumValues[1].on = true;
            }

            this.options.isMultiple = true; // Just to have checkbox

            this.options.renderOptions.useOtherChoice = false; // Always : no use this options
            this.element.append(Mustache.render(this._getTemplate('writeToggle') || "", this.options));
            this.options.isMultiple = false; // restore isMultiple : it never can be multiple

            if (scope.options.labels.invertSelection) {
                this.element.find(".dcpAttribute__value--toggle").each(function wEnum_insertTooltip(kItem)
                {
                    if (tplOption.enumValues[kItem]) {
                        $(this).tooltip({
                            trigger: "hover",
                            container: scope.element,
                            title: Mustache.render(scope.options.labels.invertSelection || "",
                                tplOption.enumValues[(kItem + 1) % 2])
                        });
                    }
                });
            }

            this.noButtonDisplay();

            this.element.on("click" + this.eventNamespace, ".dcpAttribute__value--toggle", function wEnum_booleanClick(event)
            {
                event.preventDefault();
                // Invert selection
                _.some(tplOption.enumValues, function wEnum_comboSetValue(item)
                {
                    if (scope.options.attributeValue.value === null || item.value !== scope.options.attributeValue.value) {
                        scope.setValue(item, event);
                        return true;
                    }
                    return false;
                });
            });

            this.getContentElements().each(function wEnum_addKButton()
            {
                $(this).closest(".dcpAttribute__value--enumlabel").addClass("k-button");

            });
            if (scope.options.attributeValue.value === null) {
                // Set to first enum item if empty
                var firstItem = tplOption.enumValues[0];
                if (firstItem) {
                    scope.setValue({value: firstItem.value, displayValue: firstItem.displayValue});
                }
            }

        },
        /**
         * Identify the input where is the raw value
         * @returns {*}
         */
        getContentElements: function wEnum_getContentElements()
        {
            if (this.options.inArray && (
                this.options.renderOptions.editDisplay === "horizontal" ||
                this.options.renderOptions.editDisplay === "vertical" ||
                this.options.renderOptions.editDisplay === "bool")) {

                return this.element.find('.dcpAttribute__value[name="' + this.options.id + '[' + this.options.enumIndex + ']"]');
            } else {
                return this._super();
            }
        },

        radioButtons: function wEnumRadioButtons()
        {
            var enumData;
            var tplOption = this.options;
            var labels;
            var scope = this;
            var hasNotExists;

            if (this.options.renderOptions.useSourceUri) {
                this.retrieveItems(function wEnum_retrieveDone(theWidget)
                {
                    theWidget.radioButtons();
                });
                return;
            }

            enumData = this.getSingleEnumData();

            tplOption.enumValues = enumData.data;
            hasNotExists = _.some(enumData.data, function wEnum_findNotExistItem(item)
            {
                return (item.exists === false);
            });

            if (hasNotExists === true) {
                // No set twice for radio
                this.options.renderOptions.useOtherChoice = false;
            }
            this.element.append(Mustache.render(this._getTemplate('writeRadio') || "", this.options));
            labels = this.element.find(".dcpAttribute__value--enumlabel");

            this.noButtonDisplay();
            labels.on("change" + this.eventNamespace, "input[type=radio]", function wEnum_onchange(event)
            {
                var newValue = {};
                newValue.value = $(this).val();
                newValue.displayValue = $(this).closest(".dcpAttribute__value--enumlabel").find("label").text().trim();
                scope.setValue(newValue, event);
            });

            this.getContentElements().each(function wEnum_addKButton()
            {
                $(this).closest("label").addClass("k-button");

            });
            if (scope.options.renderOptions.useFirstChoice && scope.options.attributeValue.value === null) {
                // Set to first enum item if empty
                var firstItem = tplOption.enumValues[0];
                if (firstItem) {
                    scope.setValue({value: firstItem.value, displayValue: firstItem.displayValue});
                }
            }

            if (scope.options.labels.selectMessage) {
                this.element.find(".dcpAttribute__value--enumbuttons").tooltip({
                    container: ".dcpDocument",
                    selector: '.dcpAttribute__value--enumlabel--text',
                    trigger: "hover",
                    title: function wEnum_titleTooltip()
                    {
                        if ($(this).closest(".dcpAttribute__value--enumlabel").find("input").prop("checked")) {
                            return null;
                        } else {
                            return Mustache.render(scope.options.labels.selectMessage, {
                                displayValue: $(this).text(),
                                value: $(this).parent().find("input").val()
                            });
                        }
                    }
                });
            }

            this._checkRadioOther();
        },
        checkboxButtons: function wEnumRadioButtons()
        {
            var enumData;
            var tplOption = this.options;
            var scope = this;

            if (this.options.renderOptions.useSourceUri) {
                this.retrieveItems(function wEnum_onDone(theWidget)
                {
                    theWidget.checkboxButtons();
                });
                return;
            }
            enumData = this.getMultipleEnumData();
            tplOption.enumValues = enumData.data;

            this.element.append(Mustache.render(this._getTemplate('writeRadio') || "", this.options));

            this.noButtonDisplay();
            this.element.on("change" + this.eventNamespace, ".dcpAttribute__value--enumlabel input[type=checkbox]", function wEnum_onChange(event)
            {

                var newValue = [];

                scope.getContentElements().each(function wEnum_findChecked()
                {
                    var $this = $(this);
                    if ($this.prop("checked")) {
                        var itemValue = {};
                        itemValue.value = $this.val();
                        itemValue.displayValue = $this.closest('label').text().trim();
                        newValue.push(itemValue);
                    }
                });

                scope.setValue(newValue, event);
            });

            this.getContentElements().each(function wEnum_addKButton()
            {
                $(this).closest("label").addClass("k-button");
            });

            if (this.options.labels.selectMessage) {
                this.element.find(".dcpAttribute__value--enumbuttons").tooltip({
                    container: ".dcpDocument",
                    selector: '.dcpAttribute__value--enumlabel--text',
                    trigger: "hover",
                    title: function wEnum_Cb_titleTooltip()
                    {
                        var $this = $(this);
                        if ($this.closest(".dcpAttribute__value--enumlabel").find("input").prop("checked")) {

                            return Mustache.render(scope.options.labels.unselectMessage, {
                                displayValue: $(this).text(),
                                value: $(this).parent().find("input").val()
                            });
                        } else {
                            return Mustache.render(scope.options.labels.selectMessage, {
                                displayValue: $(this).text(),
                                value: $(this).parent().find("input").val()
                            });
                        }
                    }
                });
            }
            if (this.options.renderOptions.useOtherChoice === true) {
                this._checkBoxOther();
            }
        },

        /**
         * Manage other input for radio
         * @private
         */
        _checkRadioOther: function wEnum__checkRadioOther()
        {
            this.element.find(".dcpAttribute__value--enum--other").on("focus" + this.eventNamespace, function wEnumRadioOtherInputClick()
            {

                $(this).trigger("change");

            }).on("change" + this.eventNamespace, function wEnumRadioOtherInputChange()
            {
                var $label = $(this).closest(".dcpAttribute__value--enumlabel");
                var $input = $label.find(".dcpAttribute__value--edit");
                $input.val($(this).val());
                // Trigger change label input to real set value
                $label.find("input[type=radio]").trigger("change");
            }).on("keyup" + this.eventNamespace, function wEnumRadioOtherInputKeyReturn(event)
            {
                var code = (event.keyCode ? event.keyCode : event.which);
                if (code === 13 || code === 10) {
                    $(this).blur();
                }
            });
        },

        /**
         * Manage other input for checkbox
         * @private
         */
        _checkBoxOther: function wEnum__checkBoxOther()
        {
            this.element.on("focus" + this.eventNamespace, ".dcpAttribute__value--enum--other", function wEnumCheckOtherInputClick()
            {
                $(this).trigger("change");
            }).on("change" + this.eventNamespace, ".dcpAttribute__value--enum--other", function wEnumCheckOtherInputChange()
            {
                var $label = $(this).closest(".dcpAttribute__value--enumlabel");
                var $input = $label.find(".dcpAttribute__value--edit");
                var $hasEmpty;
                var $checkbox = $label.find("input[type=checkbox]");

                $input.val($(this).val());

                $hasEmpty = _.some($(this).closest(".dcpAttribute__value--enumbuttons").find(".dcpAttribute__value--enum--other"), function wEnum_findEmptyOther(item)
                {
                    return $(item).val() === "";
                });

                if (!$hasEmpty) {
                    var $newOne = $label.clone();
                    var uuid = _.uniqueId("enum");
                    $newOne.find("input[type=checkbox]").attr("id", uuid);
                    $newOne.find("label").attr("for", uuid);
                    // add new input if no one free found
                    $label.parent().append($newOne);
                    $newOne.find("input").val("").prop("checked", false);

                    if (!$checkbox.prop("checked")) {
                        $label.find("label").trigger("click");
                    }
                }
                // resend change trigger because this hook is call before the checkbox onchange event
                $checkbox.trigger("change");

            }).on("keyup" + this.eventNamespace, ".dcpAttribute__value--enum--other", function wEnumCheckOtherInputKeyReturn(event)
            {
                var code = (event.keyCode ? event.keyCode : event.which);
                if (code === 13 || code === 10) {
                    $(this).blur(); // Change event will be triggered
                }
            });
        },
        singleDropdown: function wEnumSingleDropdown()
        {
            var kendoOptions = this.getKendoOptions();
            var kddl;
            this.element.append(Mustache.render(this._getTemplate('write') || "", this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            kddl = this.kendoWidget.kendoDropDownList(kendoOptions).data("kendoDropDownList");
            this.kendoWidgetObject = this.kendoWidget.data("kendoDrobDownList");
            kddl.list.find(".k-list-optionlabel").addClass("placeholder--clear");
        },
        multipleSelect: function wEnumMultipleSelect()
        {
            var kendoOptions = this.getKendoOptions();
            this.element.append(Mustache.render(this._getTemplate('write') || "", this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");
            this.kendoWidget.kendoMultiSelect(kendoOptions);
            this.kendoWidgetObject = this.kendoWidget.data("kendoMultiSelect");
        },

        singleCombobox: function wEnumSingleCombobox()
        {
            var kendoOptions = this.getKendoOptions();
            var kddl;
            var currentWidget = this;

            this.element.append(Mustache.render(this._getTemplate('write') || "", this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__value--edit");

            kddl = this.kendoWidget.kendoComboBox(kendoOptions).data("kendoComboBox");

            this.kendoWidgetObject = kddl;
            if (this.options.renderOptions.useSourceUri) {
                if (this.options.attributeValue.value === null) {
                    //kddl.dataSource.data([]);
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
            this.element.on("click" + this.eventNamespace, ".dcpAttribute__content__button--delete", function wEnumDeleteFilter()
            {
                currentWidget.setError(null);
                kddl.dataSource.filter({});
                kddl.value('');
            });

        },

        /**
         *Set new value to widget
         * @param value value {value:...., displayValue} or array of {value:...., displayValue}
         * @param event
         */
        setValue: function wEnumSetValue(value, event)
        {
            var kddl, newValues;
            var currentWidget = this;
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
                            kddl = this.kendoWidget.data("kendoMultiSelect");
                            newValues = _.map(value, function wEnum_findValues(val)
                            {
                                if (!currentWidget.options.renderOptions.useSourceUri) {
                                    if (!kddl.dataSource.get(val.value)) {
                                        kddl.dataSource.add(val);
                                    }
                                }
                                return val.value;
                            });
                            if (!_.isEqual(kddl.value(), newValues)) {
                                this.flashElement();
                                if (this.options.renderOptions.useSourceUri) {
                                    if (newValues.length > 0) {
                                        _.each(value, function wEnumCompleteExists(singleValue)
                                        {
                                            singleValue.exists = (singleValue.exists !== false);
                                        });
                                        kddl.dataSource.data(value);
                                    }
                                }
                                kddl.value(newValues);
                            }
                            break;

                        case "horizontal":
                        case "vertical":
                            this.getContentElements().each(function wEnum_findValues()
                            {
                                var $this = $(this);
                                var inputValue = $this.val();

                                var isIn = _.some(value, function wEnum_isIn(x)
                                {
                                    //noinspection JSHint
                                    return (x.value == inputValue);
                                });
                                if (isIn) {
                                    $this.prop("checked", true);
                                    $this.closest(".dcpAttribute__value--enumlabel").addClass("selected");
                                } else {
                                    $this.prop("checked", false);
                                    $this.closest(".dcpAttribute__value--enumlabel").removeClass("selected");
                                }
                            });

                            this.element.find(".dcpAttribute__value--enumlabel--text").tooltip("hide");
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
                                    } else {
                                        if (!kddl.dataSource.get(value.value)) {
                                            kddl.dataSource.add(value);
                                        }
                                    }
                                    this.setError(null);
                                    kddl.value(value.value);
                                } else {
                                    kddl.value('');
                                }
                            }
                            break;
                        case "list":
                            kddl = this.kendoWidget.data("kendoDropDownList");

                            if (!_.isEqual(kddl.value(), (value.value || ""))) {
                                this.flashElement();
                                if (!kddl.dataSource.get(value.value)) {
                                    kddl.dataSource.add(value);
                                }

                                // kendo need empty string (not null) to clear input
                                kddl.value(value.value || "");
                            }
                            break;
                        case "bool":
                            this.getContentElements().each(function wEnum_parseElements()
                            {
                                var $this = $(this);
                                var isOn;
                                //noinspection JSHint
                                if ($this.data("togglevalue").toString() === value.value) {
                                    if (!$this.hasClass("selected")) {
                                        if ($this.hasClass("on")) {
                                            isOn = true;
                                            $this.removeClass("on").addClass("off");
                                        } else
                                            if ($this.hasClass("off")) {
                                                $this.removeClass("off").addClass("on");
                                                isOn = false;
                                            }
                                        $this.addClass("selected").removeClass("unselected");

                                        _.defer(function wEnumAnimateBool()
                                        {
                                            if (isOn === true) {
                                                $this.addClass("on").removeClass("off");
                                            } else {
                                                $this.addClass("off").removeClass("on");
                                            }
                                        });
                                    }
                                } else {
                                    $this.removeClass("selected").addClass("unselected");
                                }
                            });
                            this.element.find(".dcpAttribute__value--enumlabel").tooltip("hide");

                            break;
                        case "horizontal":
                        case "vertical":
                            this.getContentElements().each(function wEnum_parseElements()
                            {
                                var $this = $(this);
                                //noinspection JSHint
                                if ($this.val() == value.value) {
                                    $this.prop("checked", true);
                                    $this.closest(".dcpAttribute__value--enumlabel").addClass("selected");
                                } else {
                                    $this.prop("checked", false);
                                    $this.closest(".dcpAttribute__value--enumlabel").removeClass("selected");
                                }
                            });

                            this.element.find(".dcpAttribute__value--enumlabel--text").tooltip("hide");
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
        autocompleteRequestEnum: function wEnumAutocompleteRequestEnum(options)
        {
            var requestData = {}, scope = this;

            if (options.data.filter && options.data.filter.filters && options.data.filter.filters.length > 0) {
                requestData.keyword = options.data.filter.filters[0].value;
                requestData.operator = options.data.filter.filters[0].operator;
            }
            if (this.options.renderOptions.orderBy) {
                requestData.orderBy = this.options.renderOptions.orderBy;
            }

            if (!this.options.sourceUri) {
                throw new Error("Enum : sourceUri must be defined if renderOption useSourceUri is set to true");
            }
            $.ajax({
                type: "GET",
                url: this.options.sourceUri,
                data: requestData,
                dataType: "json",
                success: function wEnum_onAutoCompleteSuccess(result)
                {
                    var info = [];
                    _.each(result.data.enumItems, function wEnum_analyzeResult(enumItem)
                    {
                        info.push({
                            value: enumItem.key,
                            displayValue: enumItem.label || '',
                            exists: enumItem.exists !== false
                        });
                    });
                    if (!scope._isMultiple()) {
                        if (scope.options.attributeValue.value !== null) {
                            if (!_.contains(_.pluck(info, "value"), scope.options.attributeValue.value)) {
                                if (scope.options.attributeValue.displayValue === scope.options.attributeValue.value) {
                                    scope.options.attributeValue.displayValue =
                                        Mustache.render(scope.options.labels.displayOtherChoice, scope.options.attributeValue);
                                }
                                info.push(scope.options.attributeValue);
                            }
                        }
                    } else {
                        _.each(scope.options.attributeValue, function wEnumAddOtherInUri(singleValue)
                        {
                            var hasValue = _.some(info, function wEnumVerifyValue(singleInfo)
                            {
                                return singleInfo.value === singleValue.value;
                            });
                            if (!hasValue) {
                                info.push(singleValue);
                            }
                        });
                    }

                    // notify the data source that the request succeeded
                    options.success(info);
                },
                error: function wEnum_onAutoCompleteError(result)
                {
                    // notify the data source that the request failed
                    options.error(result);
                }
            });
        },
        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoOptions: function wEnumGetKendoOptions()
        {
            var scope = this,
                source = null,
                kendoOptions = {},
                defaultOptions = {};

            if (this._isMultiple()) {

                source = this.getMultipleEnumData();

                defaultOptions = {
                    dataTextField: "displayValue",
                    dataValueField: "value",
                    dataSource: (this.options.renderOptions.useSourceUri) ? source.data : new kendo.data.DataSource({
                        data: source.data,
                        schema: {model: {id: "value"}}
                    }),
                    placeholder: this.options.renderOptions.placeHolder,
                    value: source.selectedValues,

                    change: function wEnum_onChange(event)
                    {
                        event.preventDefault(); // no fire change event
                        // set in case of delete item

                        var kdData = _.toArray(scope.kendoWidget.data("kendoMultiSelect").dataItems());
                        var newValues = [];
                        _.each(kdData, function wEnum_pushNewValues(val)
                        {
                            newValues.push({
                                value: val.value,
                                displayValue: val.displayValue,
                                exists: val.exists !== false
                            });
                        });
                        scope.setValue(newValues, event);
                    },
                    open: function wEnum_open(event)
                    {
                        _.bind(scope._kOpen, scope, event, this)();
                    },
                    /**
                     * When other input is in list do not autoclose list to enter a new value
                     * @param event
                     */
                    close: function wEnum_multipleClose(event)
                    {
                        _.bind(scope._kClose, scope, event, this)();
                    }
                };

                if (_.isObject(scope.options.renderOptions.kendoMultiSelectConfiguration)) {
                    kendoOptions = scope.options.renderOptions.kendoMultiSelectConfiguration;
                }
            } else {
                source = this.getSingleEnumData();

                defaultOptions = {
                    /*valuePrimitive: true,*/
                    dataTextField: "displayValue",
                    dataValueField: "value",
                    optionLabel: {
                        displayValue: this.options.renderOptions.placeHolder + ' ',
                        value: '',
                        exists: true
                    },
                    optionLabelTemplate: '<span class="placeholder">#: displayValue #</span>',
                    dataSource: (this.options.renderOptions.useSourceUri) ? source.data : new kendo.data.DataSource({
                        data: source.data,
                        schema: {model: {id: "value"}}
                    }),
                    index: (source.index < 0) ? undefined : source.index,
                    autoBind: false,

                    change: function wEnum_onChange(event)
                    {
                        if (this.value() && this.selectedIndex === -1) {
                            scope.setError(scope.options.labels.invalidEntry);
                            scope._getFocusInput().each(function wEnum_onChange()
                            {
                                this.focus();
                            });
                        } else {
                            scope.setError(null);

                            var newValue = {value: this.value(), displayValue: this.text()};
                            scope.setValue(newValue, event);
                        }
                    },
                    dataBound: function wEnum_dataBound()
                    {
                        if (scope.options.renderOptions.useFirstChoice && scope.options.attributeValue.value === null) {
                            // Set to first enum item if empty
                            var firstItem = this.dataSource.at(0);
                            if (firstItem) {
                                scope.setValue({value: firstItem.value, displayValue: firstItem.displayValue});
                            }
                        }
                    },
                    open: function wEnum_open(event)
                    {
                        _.bind(scope._kOpen, scope, event, this)();
                    },
                    /**
                     * When other input is in list do not autoclose list to enter a new value
                     * @param event
                     */
                    close: function wEnum_close(event)
                    {
                        _.bind(scope._kClose, scope, event, this)();
                    }
                };

                if (this.options.renderOptions.editDisplay === "autoCompletion") {

                    defaultOptions.index = -1;
                    defaultOptions.value = this.options.attributeValue.value;
                    defaultOptions.placeholder = this.options.renderOptions.placeHolder;

                    if (_.isObject(scope.options.renderOptions.kendoComboBoxConfiguration)) {
                        kendoOptions = scope.options.renderOptions.kendoComboBoxConfiguration;
                    }
                } else {

                    if (_.isObject(scope.options.renderOptions.kendoDropDownConfiguration)) {
                        kendoOptions = scope.options.renderOptions.kendoDropDownConfiguration;
                    }
                }
            }

            if (scope.options.renderOptions.useOtherChoice === true) {
                // add "other" input in header list
                defaultOptions.headerTemplate = $('<div class="dcpAttribute__value--enum-other"><div class="input-group dcpAttribute__value--enum-other-content">' +
                    '<input class="form-control" type="text" placeholder="' + scope.options.labels.chooseAnotherChoice + '"/>' +
                    '<span class="input-group-btn"><button class="btn btn-primary dcpAttribute__value--enum-other-select">' + scope.options.labels.selectAnotherChoice + '</button></span> ' +
                    '</div></div>');
            }

            if (this.options.renderOptions.useSourceUri) {
                defaultOptions.dataSource = {
                    data: source.data,
                    index: source.index,
                    type: "json",
                    serverFiltering: true,
                    minLength: 0,
                    transport: {
                        read: _.bind(scope.autocompleteRequestEnum, scope)
                    }
                };
            }
            return _.extend(defaultOptions, kendoOptions);
        },

        _kSelectOther: function wEnumkSelect(event, kWidget, newValue)
        {
            kWidget.dataSource.filter({});
            if (this._isMultiple()) {
                var kdData = _.toArray(kWidget.dataItems());
                var newValues = [];

                _.each(kdData, function wEnum_pushNewValues(val)
                {
                    newValues.push({
                        value: val.value,
                        displayValue: val.displayValue,
                        exists: val.exists
                    });
                });

                newValues.push({
                    value: newValue,
                    exists: false,
                    displayValue: Mustache.render(this.options.labels.displayOtherChoice, {value: newValue})
                });

                this.setValue(newValues, event);
            } else {
                this.setValue({
                    value: newValue,
                    exists: false,
                    displayValue: Mustache.render(this.options.labels.displayOtherChoice, {value: newValue})
                }, event);
            }

            $(".dcpAttribute__value--enum-other input").blur();
            kWidget.close();
        },

        /**
         * When other input is in list do not autoclose list to enter a new value
         * @param event
         * @param kWidget kendo widget
         */
        _kClose: function wEnumkClose(event, kWidget)
        {
            if (this.options.renderOptions.useOtherChoice === true) {
                var $otherInput = kWidget.ul.closest(".k-list-container").find(".dcpAttribute__value--enum-other");
                if ($otherInput.data("dcpEnumOtherFirstClose") !== false ||
                    $otherInput.find("input").is(":focus")
                ) {
                    event.preventDefault();
                    $otherInput.data("dcpEnumOtherFirstClose", false);
                }
            }
        },

        _kOpen: function wEnumkOpen(event, kWidget)
        {
            var scope = this;
            /**
             * Special events for "other" choice input
             */
            if (this.options.renderOptions.useOtherChoice === true) {
                var $container = kWidget.ul.closest(".k-list-container");
                var $otherInput = $container.find(".dcpAttribute__value--enum-other");

                if ($otherInput.length === 1 && $otherInput.data("dcpEnumOtherInitialized") !== true) {
                    $otherInput.data("dcpEnumOtherInitialized", true);
                    $otherInput.find(".dcpAttribute__value--enum-other-select").prop("disabled", true);
                    $container.prepend($otherInput);

                    $otherInput.on("click" + this.eventNamespace, "input",
                        /**
                         * Apply setValue and close list when confirm button is clicked
                         * @param event
                         */
                        function wEnumSetOtherClick(event)
                        {
                            event.preventDefault();
                            $(this).focus();
                            $(this).data("dcpEnumOtherHasFocus", true);
                        });
                    $container.on("click" + this.eventNamespace, "li.k-item",
                        /**
                         * Force close for "normal" choice because autoclose is disabled
                         */
                        function wEnumItemClick()
                        {
                            var $input = $container.find(".dcpAttribute__value--enum-other input");
                            $input.blur();
                            kWidget.close();
                        });

                    $otherInput.on("keyup" + this.eventNamespace, "input",
                        /**
                         * Apply setValue and close list when return key is pressed
                         * @param event
                         */
                        function wEnumSetOtherKeyPress(event)
                        {
                            var code = (event.keyCode ? event.keyCode : event.which);
                            var $input = $container.find(".dcpAttribute__value--enum-other input");
                            var newValue = $input.val();
                            if (code === 13 || code === 10) {
                                if (newValue) {
                                    _.bind(scope._kSelectOther, scope, event, kWidget, newValue)();
                                }
                            } else {
                                kWidget.search(newValue);
                            }
                            $otherInput.find(".dcpAttribute__value--enum-other-select").prop("disabled", !newValue);
                        });

                    $otherInput.on("click" + this.eventNamespace, ".dcpAttribute__value--enum-other-select", function wEnumSetOtherClick(event)
                    {
                        var $input = $container.find(".dcpAttribute__value--enum-other input");
                        var newValue = $input.val();
                        if (newValue) {
                            _.bind(scope._kSelectOther, scope, event, kWidget, newValue)();
                        }
                    });
                }

                $container.find(".dcpAttribute__value--enum-other").data("dcpEnumOtherFirstClose", true);
            }
        },

        close: function wEnum_close()
        {
            if (this.kendoWidget && this.kendoWidget.data("kendoDropDownList")) {
                this.kendoWidget.data("kendoDropDownList").close();
            }
            if (this.kendoWidget && this.kendoWidget.data("kendoComboBox")) {
                this.kendoWidget.data("kendoComboBox").close();
            }
            if (this.kendoWidget && this.kendoWidget.data("kendoMultiSelect")) {
                this.kendoWidget.data("kendoMultiSelect").close();
            }
            return this._super();
        },

        getType: function wEnum_getType()
        {
            return "enum";
        },

        _destroy: function wEnum_destroy()
        {
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
}));
