define([
    'underscore',
    'mustache',
    '../attribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpDocid", $.dcp.dcpAttribute, {

        options: {
            id: "",
            type: "docid"
        },

        kendoWidget: null,

        _initDom: function () {

            if (this._isMultiple()) {
                this.options.values = _.map(_.toArray(this.options.value), function (val, index) {
                    val.rawValue = val.value;
                    return val;
                });

                this.options.isMultiple = true;
            }
            if (this.getMode() === "read") {

                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
                this.element.find('a').kendoButton();
            } else if (this.getMode() === "write") {


                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
                this.kendoWidget = this.element.find(".dcpAttribute__content--docid--title");
                if (this._isMultiple()) {
                    this._decorateMultipleValue(this.kendoWidget);
                } else {
                    this._decorateSingleValue(this.kendoWidget);
                }

            }
        },

        _initEvent: function () {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").on("change." + this.eventNamespace, function () {

                    currentWidget.options.value.value = $(this).val();
                    currentWidget.setValue(currentWidget.options.value);
                });
            }
        },

        _decorateSingleValue: function (inputValue) {
            var scope = this;
            var documentModel = window.dcp.documents.get(window.dcp.documentData.document.properties.id);
            var attributeModel = documentModel.get('attributes').get(this.options.id);
            var valueIndex = this.options.index;
            inputValue.kendoAutoComplete({
                dataTextField: "title",
                filter: "contains",
                minLength: 1,
                template: '<span><span class="k-state-default">#= data.title#</span>' +
                    '#if (data.error) {#' +
                    '<span class="k-state-error">#: data.error#</span>' +
                    '#}# </span>',


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
                    var dataItem = this.dataItem(event.item.index());
                    _.each(dataItem.values, function (val, aid) {
                        if (typeof val === "object") {
                            var attrModel = documentModel.get('attributes').get(aid);
                            if (attrModel) {
                                _.defer(function () {
                                    attrModel.setValue({value: val.value, displayValue: val.displayValue}, valueIndex);
                                });
                            }
                        }
                    });
                }
            });
            this.element.find('.dcpAttribute__content--docid--button').on("click", function () {
                inputValue.data("kendoAutoComplete").search(' ');
            });
        },
        _decorateMultipleValue: function (inputValue) {
            var scope = this;
            var documentModel = window.dcp.documents.get(window.dcp.documentData.document.properties.id);
            var attributeModel = documentModel.get('attributes').get(this.options.id);
            var valueIndex = this.options.index;

            inputValue.kendoMultiSelect({
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
                    var dataItem = this.dataItem(event.item.index());
                    event.preventDefault(); // no fire change event
                    _.each(dataItem.values, function (val, aid) {
                        if (typeof val === "object") {
                            var attrModel = documentModel.get('attributes').get(aid);
                            if (attrModel) {
                                _.defer(function () {
                                    if (attrModel.hasMultipleOption) {
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
                    // set in case of delete item
                    var attrModel = documentModel.get('attributes').get(scope.options.id);
                    var oldValues = attrModel.get("value");
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

                    attrModel.setValue(newValues, valueIndex);

                }
            });
            this.element.find('.dcpAttribute__content--docid--button').on("click", function () {
                inputValue.data("kendoMultiSelect").open();
            });
        },
        setValue: function (value) {
            var kendoWidget = this.kendoWidget;
            this._super(value);
            if (this.getMode() === "write") {
                if (this._isMultiple()) {
                    var newValues = _.map(value, function (val, index) {
                        return  val.value;
                    });
                    var newData = _.map(value, function (val, index) {
                        var info = {};
                        info.docTitle = val.displayValue;
                        info.docId = val.value;
                        return info;
                    });
                    // update values in kendo widget
                    this.kendoWidget.data("kendoMultiSelect").dataSource.data(newData);
                    this.kendoWidget.data("kendoMultiSelect").value(newValues);
                    this.kendoWidget.data("kendoMultiSelect").dataSource.data([]);
                } else {
                    this.element.find(".dcpAttribute__content").val(value.value);
                    this.element.find(".dcpAttribute__content--docid--title").val(value.displayValue);
                }
                return;
            }
            if (this.getMode() === "read") {
                this.element.find(".dcpAttribute__content").text(value.displayValue);
                return;
            }
            throw new Error("Attribute " + this.options.id + " unkown mode " + this.getMode());
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