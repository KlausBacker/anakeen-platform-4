define([
    'underscore',
    'mustache',
    '../wAttribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpDocid", $.dcp.dcpAttribute, {

        options: {
            id: "",
            type: "docid"
        },

        kendoWidget: null,

        _initDom: function () {
            var scope = this;
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
                    if (!this._model().hasMultipleOption()) {
                        this.element.find('.dcpAttribute__content--docid--button').attr("disabled", "disabled");
                        this.element.find('input.k-input').attr("disabled", "disabled");
                    }
                }
            }
        },

        _initEvent: function _initEvent() {

            if (this.getMode() === "read") {
                this._initLinkEvent();
            }
            this._super();
        },
        /**
         * Define inputs for focus
         * @protected
         */
        _focusInput: function () {
            console.log("docid focus", this.element.find('input'));
            return this.element.find('input');
        },
        _decorateSingleValue: function (inputValue) {

            this.options.values = [];
            if (this.options.value) {
                this.options.values.push(this.options.value);
            }

            this._decorateMultipleValue(inputValue, {
                    maxSelectedItems: 1

                }
            );
        },

        _decorateMultipleValue: function (inputValue, extraOptions) {
            var scope = this;
            var documentModel = window.dcp.documents.get(window.dcp.documentData.document.properties.id);


            var options = {
                filter: "contains",
                minLength: 1,
                itemTemplate: '<span><span class="k-state-default">#= data.title#</span>' +
                    '#if (data.error) {#' +
                    '<span class="k-state-error">#: data.error#</span>' +
                    '#}# </span>',


                autoBind: false,
                dataTextField: "docTitle",
                dataValueField: "docId",

                value: _.map(this.options.values, function (val, index) {
                    var info = {};
                    info.docTitle = val.displayValue;
                    info.docId = val.value;
                    return info;
                }),
                dataSource: {
                    type: "json",
                    serverFiltering: true,
                    transport: {
                        read: {
                            type: "POST",
                            url: "?app=DOCUMENT&action=AUTOCOMPLETE&attrid=" + scope.options.id +
                                "&id=" + window.dcp.documentData.document.properties.id +
                                "&fromid=" + window.dcp.documentData.document.properties.fromid,
                            data: {
                                "attributes": documentModel.getValues()
                            }
                        }
                    }
                },
                select: function (event) {
                    var valueIndex = scope._getIndex();
                    var dataItem = this.dataItem(event.item.index());
                    event.preventDefault(); // no fire change event
                    _.each(dataItem.values, function (val, aid) {
                        if (typeof val === "object") {
                            var attrModel = documentModel.get('attributes').get(aid);
                            if (attrModel) {
                                _.defer(function () {

                                    if (attrModel.hasMultipleOption()) {
                                        attrModel.addValue({value: val.value, displayValue: val.displayValue}, valueIndex);
                                    } else {
                                        attrModel.setValue({value: val.value, displayValue: val.displayValue}, valueIndex);
                                    }
                                });
                            }
                        }
                    });
                },
                change: function (event) {
                    var valueIndex = scope._getIndex();
                    // set in case of delete item
                    var attrModel = documentModel.get('attributes').get(scope.options.id);
                    var oldValues = attrModel.get("value");
                    var displayValue;
                    var newValues = [];
                    if (attrModel.inArray()) {
                        oldValues = oldValues[valueIndex];
                    }
                    _.each(this.value(), function (val) {
                        displayValue = _.where(oldValues, {value: val});
                        if (displayValue.length > 0) {
                            displayValue = displayValue[0].displayValue;
                        } else {
                            displayValue = "-";
                        }
                        newValues.push({value: val, displayValue: displayValue});
                    });
                    attrModel.setValue(newValues, valueIndex);

                }
            };

            if (extraOptions) {
                options = _.extend(options, extraOptions);
            }
            inputValue.kendoMultiSelect(options);
            this.element.find('.dcpAttribute__content--docid--button').on("click", function (event) {
                event.preventDefault();
                inputValue.data("kendoMultiSelect").open();
            });

            this.element.find('.dcpAttribute__content--docid--button[title]').kendoTooltip();
        },
        setValue: function (value) {

            this._super(value);
            if (this.getMode() === "write") {
                if (!this._model().hasMultipleOption()) {
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
                var newValues = _.map(value, function (val, index) {
                    return  val.value;
                });
                var newData = _.map(value, function (val, index) {
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

        _getTemplate: function (name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute && window.dcp.templates.attribute[this.getType()] && window.dcp.templates.attribute[this.getType()][name]) {
                return window.dcp.templates.attribute[this.getType()][name];
            }
            throw new Error("Unknown template docid " + name);
        },


        getType: function () {
            return "docid";
        }

    });
});