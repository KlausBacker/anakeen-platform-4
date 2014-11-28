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
            this.element.addClass("dcpAttribute__contentWrapper");
            this.element.attr("data-type", this.getType());
            this.element.attr("data-id", this.options.id);
            if (this._isMultiple()) {
                this.options.values = _.toArray(this.options.value);
                this.options.isMultiple = true;
            }

            if (this.getMode() === "read") {

                var htmlLink = this.getLink();
                if (htmlLink === null) {
                    htmlLink = {};
                    this.options.renderOptions = this.options.renderOptions || {};
                    this.options.renderOptions.htmlLink = htmlLink;
                }
                this.options.renderOptions.htmlLink.renderUrl = Mustache.render(this.options.renderOptions.htmlLink.url, this.options.value);
                this.options.renderOptions.htmlLink.renderTitle = Mustache.render(this.options.renderOptions.htmlLink.title, this.options.value);

                if (this._isMultiple()) {
                    this.options.values = _.map(this.options.value, function (val, index) {
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
                this.kendoWidget = this.element.find(".dcpAttribute__content--docid");
                if (this._isMultiple()) {
                    this._decorateMultipleValue(this.kendoWidget);
                } else {
                    this._decorateSingleValue(this.kendoWidget);
                }
                if (this.options.value && this.options.value.value !== null) {
                    if (!this.hasMultipleOption()) {
                        this.element.find('.dcpAttribute__content--docid--button').attr("disabled", "disabled");
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
            var scope = this;
            if (htmlLink) {
                this.element.find('.dcpAttribute__content__link').on("click." + this.eventNamespace, function (event) {
                    if (htmlLink.target === "_render") {
                        event.preventDefault();
                        if (_.isUndefined($(this).data("index"))) {
                            window.dcp.document.set("initid", scope.options.value.value);
                        } else {
                            window.dcp.document.set("initid", scope.options.value[$(this).data("index")].value);
                        }
                        window.dcp.document.get("viewId", "!defaultConsultation");
                        window.dcp.document.fetch();
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
            this.options.values = [];
            if (this.options.value) {
                this.options.values.push(this.options.value);
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

                    value: _.map(this.options.values, function (val) {
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
                    select: function (event) {
                        var valueIndex = scope._getIndex();
                        var dataItem = this.dataItem(event.item.index());
                        event.preventDefault(); // no fire change event
                        scope._trigger("changeattrsvalue", event, [dataItem, valueIndex]);

                    },
                    change: function (event) {
                        // set in case of delete item
                        var oldValues = scope.options.value;
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
            this.element.find('.dcpAttribute__content--docid--button').on("click", function (event) {
                event.preventDefault();
                inputValue.data("kendoMultiSelect").open();
            });

            this.element.find('.dcpAttribute__content--docid--button[title]').kendoTooltip();
        },
        /**
         * Return true if attribut has multiple option
         * @returns bool
         */
        hasMultipleOption: function wDocidHasMultipleOption() {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        setValue: function (value, event) {

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
                        this.element.find('.dcpAttribute__content--docid--button').removeAttr("disabled");
                        this.element.find('input.k-input').removeAttr("disabled");
                    } else {
                        this.element.find('.dcpAttribute__content--docid--button').attr("disabled", "disabled");
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
                this.element.find(".dcpAttribute__content").text(value.displayValue);
            } else {
                throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
            }
        },

        getType: function () {
            return "docid";
        }

    });

    return $.fn.dcpDocid;
});