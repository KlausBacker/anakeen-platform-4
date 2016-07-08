/*global define*/

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'underscore',
            'mustache',
            'dcpDocument/widgets/attributes/wAttribute',
            'kendo/kendo.multiselect'
        ], factory);
    } else {
        //noinspection JSUnresolvedVariable
        factory(window.jQuery, window._, window.Mustache);
    }
}(window, function require_wDocid($, _, Mustache)
{
    'use strict';

    //noinspection JSUnusedGlobalSymbols
    $.widget("dcp.dcpDocid", $.dcp.dcpAttribute, {

        options: {
            type: "docid",
            renderOptions: {
                kendoMultiSelectConfiguration: {
                    minLength: 1,
                    itemTemplate: '<span><span class="k-state-default">#= data.title#</span>' +
                    '#if (data.error) {#' +
                    '<span class="k-state-error">#: data.error#</span>' +
                    '#}# </span>'
                },
                kendoComboBoxConfiguration: {
                    template: '<span><span class="k-state-default">#= data.title#</span>' +
                    '#if (data.error) {#' +
                    '<span class="k-state-error">#: data.error#</span>' +
                    '#}# </span>'
                },
                editDisplay: "autoCompletion"
            },
            labels: {
                allSelectedDocument: "No more matching"
            }
        },

        kendoWidget: null,

        _initDom: function wDocidInitDom()
        {
            var scope = this;
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);

            if (this._isMultiple()) {
                this.options.attributeValues = _.toArray(this.options.attributeValue);
                this.options.isMultiple = true;
            }

            if (this.getMode() === "read") {
                if (this.options.renderOptions.format) {
                    if (this._isMultiple()) {
                        _.each(this.options.attributeValues, function wDocidinitDomFormat(singleValue)
                        {
                            singleValue.formatValue = Mustache.render(scope.options.renderOptions.format,
                                singleValue);
                        });
                    } else {
                        this.options.attributeValue.formatValue = Mustache.render(this.options.renderOptions.format,
                            this.options.attributeValue);
                    }
                }
                //noinspection JSPotentiallyInvalidConstructorUsage,JSAccessibilityCheck
                $.dcp.dcpAttribute.prototype._initDom.apply(this, []);

            } else
                if (this.getMode() === "write") {
                    //noinspection JSPotentiallyInvalidConstructorUsage,JSAccessibilityCheck
                    $.dcp.dcpAttribute.prototype._initDom.apply(this, []);
                    this.kendoWidget = this.element.find(".dcpAttribute__value--docid");
                    if (this.options.renderOptions.placeHolder) {
                        this.options.renderOptions.kendoMultiSelectConfiguration.placeholder = this.options.renderOptions.placeHolder;
                    }
                    if (this._isMultiple()) {
                        this._decorateMultipleValue(this.kendoWidget);
                    } else {
                        switch (this.options.renderOptions.editDisplay) {
                            case "singleMultiple" :
                                this._decorateSingleValue(this.kendoWidget);

                                break;
                            case "autoCompletion":
                                this.singleCombobox(this.kendoWidget);
                                break;
                            case "list":
                                this.singleDropdown(this.kendoWidget);
                                break;
                            default:
                                this.singleCombobox(this.kendoWidget);
                        }
                    }
                    this._updateCreateButton();
                }
        },

        _updateCreateButton: function wDocid_updateCreateButton()
        {
            var currentValue = this.options.attributeValue;
            var buttonsConfig = this.options.renderOptions.buttons;

            this.element.find(".dcpAttribute__content__button--create").each(function wDocid_updateCreateButtonEach()
            {
                var $button = $(this);
                var buttonIndex = $button.data("index");
                var buttonConfig = buttonsConfig[buttonIndex];

                        $button.prop("disabled", false);
                if (currentValue.value) {
                    $button.html(buttonConfig.renderHtmlContent + buttonConfig.htmlEditContent);
                    if (! currentValue.initid) {
                        $button.prop("disabled", true);
                    }
                } else {
                    // also when mutiple always create
                    $button.html(buttonConfig.renderHtmlContent + buttonConfig.htmlCreateContent);
                }
            });
        },

        /**
         * Init event when a hyperlink is associated to the attribute
         *
         * @protected
         */
        _initLinkEvent: function wDocidInitLinkEvent()
        {
            this._super();
            var htmlLink = this.getLink();
            var currentWidget = this;
            if (htmlLink) {
                this.element.on("click." + this.eventNamespace, '.dcpAttribute__content__link', function wDocidInitLinkOnClick(event)
                {
                    var $this = $(this);
                    if (htmlLink.target === "_render") {
                        event.preventDefault();
                        currentWidget._trigger("fetchdocument", event, {
                            "index": $this.data("index"),
                            "tableLine": $this.closest(".dcpArray__content__line").data("line")
                        });
                    }
                });
            }
            return this;
        },
        _initButtonsEvent: function _initButtonsEvent()
        {
            var currentWidget = this;
            this._super();

            this.element.on("click" + this.eventNamespace, ".dcpAttribute__content__button--create", function wAttributeButtonClick(event)
            {
                var buttonsConfig = currentWidget.options.renderOptions.buttons;
                var $button = $(this);
                var buttonIndex = $button.data("index");
                var buttonConfig = buttonsConfig[buttonIndex];

                currentWidget._trigger("externalLinkSelected", event, {
                    target: event.target,
                    eventId: "attribute.createDocumentRelation",
                    index: currentWidget._getIndex(),
                    buttonConfig: buttonConfig
                });
            });
            this.element.tooltip({
                selector: ".dcpAttribute__content__buttons button",
                placement: "top",
                trigger: "hover",
                html: true,
                title: function wAttributeGetButtonTitle()
                {
                    var title = $(this).data("title");
                    var attrValue = currentWidget.getValue();
                    return Mustache.render(title || "", attrValue);
                },
                container: this.element
            });

            return this;
        },
        /**
         * Define inputs for focus
         * @protected
         */
        _getFocusInput: function wDocidFocusInput()
        {
            return this.element.find('input');
        },

        /**
         * Get kendo option from normal options and from renderOptions.kendoNumeric
         * @returns {*}
         */
        getKendoOptions: function wDocidGetKendoOptions(inputValue, extraOptions)
        {
            var currentWidget = this;
            var options = {
                autoBind: false,
                dataTextField: "docTitle",
                dataValueField: "docId",
                highlightFirst: true,
                //value: values,
                dataSource: {
                    type: "json",
                    serverFiltering: true,
                    transport: {
                        read: function wDocidSelectRead(options)
                        {
                            currentWidget._hasBeenRequested = true;
                            options.data.index = currentWidget._getIndex();
                            return currentWidget.options.autocompleteRequest.call(null, options);
                        }
                    },
                    schema: {
                        // Add already recorded data to items
                        data: function wDocidSelectSchema(items)
                        {
                            /*
                             var attrValues = currentWidget.getValue();

                             if (attrValues && !_.isArray(attrValues) && attrValues.value) {
                             attrValues = [attrValues];
                             }*/
                            //Add new elements
                            _.each(items, function wDocidDataCompose(currentItem)
                            {
                                if (currentItem.values && currentItem.values[currentWidget.options.id]) {
                                    currentItem.docId = currentItem.values[currentWidget.options.id].value;
                                    currentItem.docTitle = currentItem.values[currentWidget.options.id].displayValue;
                                }
                            });

                            //Convert existing values
                            /*
                             _.each(attrValues, function wDocidDataAlreadySet(currentValue)
                             {
                             if (currentValue && currentValue.value) {
                             items.push({
                             docId: currentValue.value,
                             docTitle: currentValue.displayValue
                             });
                             }
                             });*/

                            //Suppress multiple items
                            return _.uniq(items, false, function wDocidDataUniq(currentItem)
                            {
                                return currentItem.docId;
                            });
                        }
                    }
                },
                select: function kendoDocidSelect(event)
                {
                    if (!event.item || _.isUndefined(event.item.index())) {
                        return;
                    }

                    var valueIndex = currentWidget._getIndex();
                    var dataItem = this.dataSource.at(event.item.index()).toJSON();

                    if (dataItem.error) {
                        event.preventDefault();
                    } else {
                        //The object returned by dataSource.at are internal kendo object so I clean it with toJSON

                        _.defer(function wDocidChangeOnSelect()
                        {
                            // Change others attributes designed by help returns
                            currentWidget._trigger("changeattrsvalue", event, {
                                dataItem: dataItem,
                                valueIndex: valueIndex
                            });
                        });
                    }

                },
                change: function kendoChangeSelect(event)
                {
                    // set in case of delete item
                    var oldValues = currentWidget.options.attributeValue;
                    var displayValue;
                    var newValues = [];
                    var kMultiSelect = this;
                    var widgetValue = this.value();

                    if (_.isArray(widgetValue)) {
                        _.each(widgetValue, function wDocidSelectChange(val)
                        {
                            if (!_.isUndefined(val)) {
                                displayValue = _.where(oldValues, {value: val});
                                if (displayValue.length === 0) {
                                    displayValue = _.where(kMultiSelect.dataSource.data(), {docId: val});
                                    if (displayValue.length > 0) {
                                        displayValue = displayValue[0].docTitle;
                                    } else {
                                        displayValue = "-";
                                    }
                                } else {
                                    displayValue = displayValue[0].displayValue;
                                }

                                newValues.push({value: val, displayValue: displayValue});
                            }
                        });

                        if (!currentWidget._isMultiple()) {
                            if (newValues.length > 0) {
                                newValues = newValues[0];
                            } else {
                                newValues = {value: null, displayValue: ""};
                            }
                        }

                    } else {
                        if (widgetValue) {
                            displayValue = _.where(kMultiSelect.dataSource.data(), {docId: widgetValue});
                            if (displayValue.length > 0) {
                                displayValue = displayValue[0].docTitle;
                                newValues = {value: widgetValue, displayValue: displayValue};
                            } else {
                                newValues = {value: null, displayValue: ""};
                            }
                        } else {
                            newValues = {value: null, displayValue: ""};
                        }
                    }

                    currentWidget.setValue(newValues, event);
                },
                open: function wDocidSelectOpen(event)
                {
                    if (currentWidget._hasBeenRequested !== true) {
                        event.preventDefault();
                        currentWidget.kendoWidgetObject.search("");
                    }
                    this.ul.addClass("dcpAttribute__select--docid");
                },
                filtering: function wDocidSelectOpen()
                {
                    this._isFiltering = true;
                },
                dataBound: function wDocidFilteringNoOne()
                {
                    if (this._isFiltering) {
                        if (this.ul.find("li:not(.k-state-selected)").length === 0 && this.ul.find("li.k-state-selected").length > 0) {
                            // No one more : display
                            var $noOne = $('<li class="k-item"/>')
                                .append('<span class="k-state-default"/>')
                                .append($('<span class="k-state-error dcpAttribute__select--docid-none"/>')
                                    .text(currentWidget.options.labels.allSelectedDocument));
                            this.ul.append($noOne);
                        }
                        this._isFiltering = false;
                    }
                }
            };

            if (extraOptions) {
                options = _.extend(options, extraOptions);
            }
            if (this.options.renderOptions.kendoComboBoxConfiguration) {
                options = _.extend(this.options.renderOptions.kendoComboBoxConfiguration, options);
            }

            return options;
        },

        /**
         * When docid is not multiple, it is a multiselect limited to one element
         * @param inputValue select  element
         */
        _decorateSingleValue: function wDocidDecorateSingleValue(inputValue)
        {

            this.options.attributeValues = [];
            if (this.options.attributeValue) {
                this.options.attributeValues.push(this.options.attributeValue);
            }

            this._decorateMultipleValue(inputValue, {
                    maxSelectedItems: 1
                }
            );

            if (this.options.attributeValue && this.options.attributeValue.value !== null) {
                this.element.find('.dcpAttribute__value--docid--button').attr("disabled", "disabled");
                this.element.find('input.k-input').attr("disabled", "disabled");

            }
        },

        _decorateMultipleValue: function wDocidDecorateMultipleValue(inputValue, extraOptions)
        {
            var options = this.getKendoOptions(inputValue, {
                filter: "contains"
            });
            var currentWidget = this,
                values = _.map(this.options.attributeValues, function wDocidSelectMap(val)
                {
                    var info = {};
                    info.docTitle = val.displayValue;
                    info.docId = val.value;
                    return info;
                });

            if (extraOptions) {
                options = _.extend(options, extraOptions);
            }

            if (this.options.renderOptions.kendoMultiSelectConfiguration) {
                options = _.extend(this.options.renderOptions.kendoMultiSelectConfiguration, options);
            }
            //noinspection JSUnresolvedFunction
            inputValue.kendoMultiSelect(options);
            this.kendoWidgetObject = inputValue.data("kendoMultiSelect");
            this.kendoWidgetObject.dataSource.data(values);

            if (this.options.attributeValues.value !== null) {
                // Init kendo widget with identifier array
                this.kendoWidgetObject.value(_.filter(_.map(values, function wDocidInitValue(item)
                {
                    return item.docId;
                }), function wDocidFilterEmpty(item)
                {
                    return !_.isEmpty(item);
                }));
            }
            this.element.on("click" + this.eventNamespace, '.dcpAttribute__value--docid--button', function wDocidSelectClick(event)
            {
                event.preventDefault();
                currentWidget.kendoWidgetObject.search("");
            });

            this.element.find('.dcpAttribute__value--docid--button[title]').tooltip({
                html: true
            });
        },

        singleDropdown: function wDocidSingleDropdown(inputValue)
        {
            var kendoOptions = this.getKendoOptions(inputValue);

            this.kendoWidgetObject = inputValue.kendoDropDownList(kendoOptions).data("kendoDropDownList");

            this.kendoWidgetObject.list.find(".k-list-optionlabel").addClass("placeholder--clear");
            this.kendoWidgetObject.value(this.options.attributeValue.value);
            this.element.find(".dcpAttribute__value--docid--button").parent().hide();
        },

        singleCombobox: function wDocidSingleCombobox(inputValue)
        {
            var kendoOptions = this.getKendoOptions(inputValue, {
                filter: "startswith" //@TODO use filter option in standard auto complete
            });
            var kendoSelect;

            this.kendoWidgetObject = inputValue.kendoComboBox(kendoOptions).data("kendoComboBox");
            kendoSelect = this.kendoWidgetObject;
            this.kendoWidgetObject.list.find(".k-list-optionlabel").addClass("placeholder--clear");

            if (this.options.attributeValue && this.options.attributeValue.value) {
                kendoSelect.dataSource.add({
                    docId: this.options.attributeValue.value,
                    docTitle: this.options.attributeValue.displayValue
                });
            }

            if (this.options.attributeValue.value) {
                // Init value in kendo only if any : if not call to server is performed
                this.kendoWidgetObject.value(this.options.attributeValue.value);
            }
            this.element.find(".dcpAttribute__value--docid--button").parent().hide();
        },

        /**
         * Return true if attribut has multiple option
         * @returns bool
         */
        hasMultipleOption: function wDocidHasMultipleOption()
        {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        setValue: function wDocidSetValue(value, event)
        {
            var newValues;
            this._super(value, event);
            if (this.getMode() === "write") {
                if (!this.hasMultipleOption() && this.options.renderOptions.editDisplay === "singleMultiple") {
                    if (!_.isArray(value)) {
                        if (value.value !== null) {
                            value = [value];
                        } else {
                            value = [];
                        }
                    } else
                        if (value.length === 1 && value.value === null) {
                            value = [];
                        }
                    if (value.length === 0) {
                        this.element.find('.dcpAttribute__value--docid--button').removeAttr("disabled");
                        this.element.find('input.k-input').removeAttr("disabled");
                    } else {
                        this.element.find('.dcpAttribute__value--docid--button').attr("disabled", "disabled");
                        this.element.find('input.k-input').attr("disabled", "disabled");
                    }
                }

                if (this.hasMultipleOption() || this.options.renderOptions.editDisplay === "singleMultiple") {
                    newValues = _.map(value, function wDocidMapValue(val)
                    {
                        return val.value;
                    });

                } else {
                    newValues = value.value;
                }
                var kendoSelect = this.kendoWidgetObject;
                var originalValues = _.clone(kendoSelect.value());
                // update values in kendo widget

                var dataOri = _.map(_.filter(kendoSelect.dataSource.data(), function wDocIdFilter(item)
                {
                    return !_.isEmpty(item.docId);
                }), function wDocidFilterEmpty(currentElement)
                {
                    return {
                        docId: currentElement.docId,
                        docTitle: currentElement.docTitle
                    };
                });

                if (!_.isArray(value)) {
                    value = [value];
                }
                _.each(value, function wDocidEachData(val)
                {
                    var info = {};

                    if (!_.some(dataOri, function wDocidEachUniq(elt)
                        {
                            return val && elt.docId === val.value;
                        })
                    ) {
                        // add more static data in dataSource
                        if (val.value !== null) {
                            info.docTitle = val.displayValue;
                            info.docId = val.value;
                            kendoSelect.dataSource.add(info);
                        }
                    }
                });

                if (
                    (_.isArray(newValues) && !_.isEqual(_.uniq(newValues), _.uniq(originalValues))) ||
                    (newValues === null && !_.isEmpty(originalValues)) ||
                    (!_.isArray(newValues) && !_.isEqual(newValues.toString(), originalValues.toString()))
                ) {
                    kendoSelect.value(newValues);
                    this.flashElement();
                }
                this._updateCreateButton();

            } else
                if (this.getMode() === "read") {
                    this.redraw();
                } else {
                    throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
                }
        },

        close: function wDocid_close()
        {
            if (this.kendoWidget && this.kendoWidgetObject) {
                this.kendoWidgetObject.close();
            }
        },

        getType: function wDocid_getType()
        {
            return "docid";
        },

        _destroy: function wDocid__destroy()
        {
            if (this.kendoWidget && this.kendoWidgetObject) {
                this.kendoWidgetObject.destroy();
            }
            $(".dcpDocid-create-window").each(function wDocid_destroyWindow()
            {
                var kWindow = $(this).data("dcpWindow");
                if (kWindow) {
                    kWindow.destroy();
                }
            });
            this._super();
        }

    });

    return $.fn.dcpDocid;
}));