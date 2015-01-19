/*global define, _super*/
define([
    'underscore',
    'mustache',
    'widgets/attributes/wAttribute',
    'kendo/kendo.multiselect'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpDocid", $.dcp.dcpAttribute, {

        options: {
            type: "docid",
            renderOptions: {
                kendoMultiSelectConfiguration: {}
            }
        },

        kendoWidget: null,

        _initDom: function wDocidInitDom() {
            this.element.addClass("dcpAttribute__content");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-attrid", this.options.id);
            if (this._isMultiple()) {
                this.options.attributeValues = _.toArray(this.options.attributeValue);
                this.options.isMultiple = true;
            }

            if (this.getMode() === "read") {

                var htmlLink = this.getLink();
                if (htmlLink === null) {
                    htmlLink = {};
                    this.options.renderOptions = this.options.renderOptions || {};
                    this.options.renderOptions.htmlLink = htmlLink;
                }
                this.options.renderOptions.htmlLink.renderUrl = Mustache.render(this.options.renderOptions.htmlLink.url, this.options.attributeValue);
                this.options.renderOptions.htmlLink.renderTitle = Mustache.render(this.options.renderOptions.htmlLink.title, this.options.attributeValue);

                if (this._isMultiple()) {
                    this.options.attributeValues = _.map(this.options.attributeValue, function (val, index) {
                        val.rawValue = val.value;
                        val.renderUrl = Mustache.render(htmlLink.url, val);
                        val.renderTitle = Mustache.render(htmlLink.title, val);
                        val.index = index;
                        return val;
                    });

                    this.options.isMultiple = true;
                }

                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            } else if (this.getMode() === "write") {
                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
                this.kendoWidget = this.element.find(".dcpAttribute__value--docid");
                if (this._isMultiple()) {
                    this._decorateMultipleValue(this.kendoWidget);
                } else {
                    this._decorateSingleValue(this.kendoWidget);
                }
                if (this.options.attributeValue && this.options.attributeValue.value !== null) {
                    if (!this.hasMultipleOption()) {
                        this.element.find('.dcpAttribute__value--docid--button').attr("disabled", "disabled");
                        this.element.find('input.k-input').attr("disabled", "disabled");
                    }
                }
            }
        },


        /**
         * Init event when a hyperlink is associated to the attribute
         *
         * @protected
         */
        _initLinkEvent: function wAttributeInitLinkEvent() {
            this._super();
            var htmlLink = this.getLink();
            var currentWidget = this;
            if (htmlLink) {
                this.element.on("click." + this.eventNamespace, '.dcpAttribute__content__link', function (event) {
                    var $this = $(this);
                    if (htmlLink.target === "_render") {
                        event.preventDefault();
                        currentWidget._trigger("changedocument", event, {
                            "index" : $this.data("index"),
                            "tableLine" : $this.closest(".dcpArray__content__line").data("line")
                        });
                    }
                });
            }
            return this;
        },

        /**
         * Define inputs for focus
         * @protected
         */
        _getFocusInput: function wDocidFocusInput() {
            return this.element.find('input');
        },

        /**
         * When docid is not multiple, it is a multiselect limited to one element
         * @param inputValue select  element
         */
        _decorateSingleValue: function wDocidDecorateSingleValue(inputValue) {
            this.options.attributeValues = [];
            if (this.options.attributeValue) {
                this.options.attributeValues.push(this.options.attributeValue);
            }

            this._decorateMultipleValue(inputValue, {
                    maxSelectedItems: 1
                }
            );
        },

        _decorateMultipleValue: function wDocidDecorateMultipleValue(inputValue, extraOptions) {
            var scope = this,
                options = {
                    filter: "contains",
                    minLength: 1,
                    itemTemplate: '<span><span class="k-state-default">#= data.title#</span>' +
                    '#if (data.error) {#' +
                    '<span class="k-state-error">#: data.error#</span>' +
                    '#}# </span>',
                    autoBind: false,
                    dataTextField: "docTitle",
                    dataValueField: "docId",

                    value: _.map(this.options.attributeValues, function (val) {
                        var info = {};
                        info.docTitle = val.displayValue;
                        info.docId = val.value;
                        return info;
                    }),
                    dataSource: {
                        type: "json",
                        serverFiltering: true,
                        transport: {
                            read: scope.options.autocompleteRequest
                        },
                        schema: {
                            // Filter data to delete already recorded ids
                            data: function (items) {
                                var attrValues = scope.getValue();
                                if (!attrValues || !_.isArray(attrValues)) {
                                    return items;
                                }
                                var recordedValues = _.pluck(attrValues, "value");
                                return _.filter(items, function (item) {
                                    if (!item.values) {
                                        return true;
                                    }
                                    return (_.indexOf(recordedValues, item.values[scope.options.id].value) < 0);
                                });
                            }
                        }
                    },
                    select: function kendoDocidSelect(event) {
                        var valueIndex = scope._getIndex();
                        var dataItem = this.dataSource.at(event.item.index());
                        //The object returned by dataSource.at are internal kendo object so I clean it with toJSON
                        if (dataItem.toJSON) {
                            dataItem = dataItem.toJSON();
                        }
                        event.preventDefault(); // no fire change event
                        console.log("select",{ dataItem : dataItem, valueIndex : valueIndex} );
                        scope._trigger("changeattrsvalue", event, { dataItem : dataItem, valueIndex : valueIndex});

                    },
                    change: function kendoChangeSelect(event) {
                        // set in case of delete item
                        var oldValues = scope.options.attributeValue;
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

            if (extraOptions) {
                options = _.extend(options, extraOptions);
            }

            if (this.options.renderOptions.kendoMultiSelectConfiguration) {
                options = _.extend(options, this.options.renderOptions.kendoMultiSelectConfiguration);
            }
            inputValue.kendoMultiSelect(options);
            this.element.on("click"+ this.eventNamespace, '.dcpAttribute__value--docid--button', function (event) {
                event.preventDefault();
                inputValue.data("kendoMultiSelect").open();
            });

            this.element.find('.dcpAttribute__value--docid--button[title]').tooltip({
                html:true
            });
        },
        /**
         * Return true if attribut has multiple option
         * @returns bool
         */
        hasMultipleOption: function wDocidHasMultipleOption() {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        setValue: function wDocidSetValue(value, event) {
            this._super(value, event);
            if (this.getMode() === "write") {
                if (!this.hasMultipleOption()) {
                    if (!_.isArray(value)) {
                        if (value.value !== null) {
                            value = [value];
                        } else {
                            value = [];
                        }
                    } else if (value.length === 1 && value.value === null) {
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
                var newValues = _.map(value, function (val) {
                    return val.value;
                });
                var newData = _.map(value, function (val) {
                    var info = {};
                    info.docTitle = val.displayValue;
                    info.docId = val.value;
                    return info;
                });
                var originalValues = _.clone(this.kendoWidget.data("kendoMultiSelect").value());
                // update values in kendo widget
                this.kendoWidget.data("kendoMultiSelect").dataSource.data(newData);
                this.kendoWidget.data("kendoMultiSelect").value(newValues);
                this.kendoWidget.data("kendoMultiSelect").dataSource.data([]);

                if (!_.isEqual(newValues, originalValues)) {
                    this.flashElement();
                }

            } else if (this.getMode() === "read") {
                this.element.find(".dcpAttribute__value").text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        getType: function getType() {
            return "docid";
        },

        _destroy : function _destroy() {
            if (this.kendoWidget && this.kendoWidget.data("kendoMultiSelect")) {
                this.kendoWidget.data("kendoMultiSelect").destroy();
            }
            this._super();
        }

    });

    return $.fn.dcpDocid;
});