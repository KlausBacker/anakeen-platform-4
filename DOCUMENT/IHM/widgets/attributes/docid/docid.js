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

        _initDom: function () {
            if (this.getMode() === "read") {
                if (this.options.options && this.options.options.multiple === "yes") {
                    this.options.values = _.compact(this.options.value);
                    console.log("Multiple", this.options);
                }

                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
                this.element.find('a').kendoButton();
            } else if (this.getMode() === "write") {
                var scope = this;
                var documentModel = window.dcp.documents.get(window.dcp.documentData.document.properties.id);

                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
                this.element.find(".dcpAttribute__content--docid--title").kendoAutoComplete({
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
                        event.preventDefault();
                        var dataItem = this.dataItem(event.item.index());
                        console.log("select", event, dataItem, event.item);
                        _.each(dataItem.values, function (val, aid) {
                            if (typeof val === "object") {

                                    var attrModel = documentModel.get('attributes').get(aid);
                                    if (attrModel) {
                                        console.log("set", aid, val);
                                        attrModel.setValue(val);
                                    }

                            }
                        });
                    }
                });
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

        setValue: function (value) {
            this._super(value);
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").val(value.value);
                this.element.find(".dcpAttribute__content--docid--title").val(value.displayValue);
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