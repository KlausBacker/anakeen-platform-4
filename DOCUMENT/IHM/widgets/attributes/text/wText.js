define([
    'underscore',
    'mustache',
    "kendo-culture-fr",
    '../wAttribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpText", $.dcp.dcpAttribute, {

        options: {
            id: "",
            type: "text"
        },
        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            this.kendoWidget = this.element.find(".dcpAttribute__content--edit");
            if (this.kendoWidget && this.options.hasAutocomplete) {
                this._activateAutocomplete(this.kendoWidget);
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
            this._super();
        },
        /**
         * Just to be apply in normal input help
         * @param inputValue
         * @private
         */
        _activateAutocomplete: function (inputValue) {
            var scope = this;
            var documentModel = window.dcp.documents.get(window.dcp.documentData.document.properties.id);
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
            this.element.find('.dcpAttribute__content--autocomplete--button').on("click", function (event) {
                event.preventDefault();
                inputValue.data("kendoAutoComplete").search(' ');
            });
        },
        setValue: function (value) {
            this._super(value);
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").val(value.value);
                this.flashElement();

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
            throw new Error("Unknown template text " + name);
        },

        getType: function () {
            return "text";
        }

    });
});