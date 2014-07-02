define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpAttribute", {

        options: {
            eventPrefix: "dcpAttribute",
            id: null,
            type: "abstract",
            mode: "read",
            index: -1
        },

        _create: function () {
            if (this.options.value === null) {
                this.options.value = {};
            }
            if (this.options.helpOutputs) {
                this.options.hasAutocomplete=true;
            }
            this._initDom();
            this._initEvent();
        },

        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
        },

        _initEvent: function () {
            if (this.getMode() === "write") {
                this._initDeleteEvent();
            }
        },

        _initDeleteEvent: function () {
            var currentWidget = this;
            var attrModel = currentWidget._model();
            var docModel = currentWidget._documentModel();
            var attrToClear = attrModel.attributes.helpOutputs;

            if (!attrToClear) {
                attrToClear = [attrModel.id];
            } else {
                attrToClear = _.toArray(attrToClear);
            }
            // Compose delete button title
            var $deleteButton = this.element.find(".dcpAttribute__content--delete--button");
            var titleDelete = $deleteButton.find("button").attr('title');
            var attrLabels = _.map(attrToClear, function (aid) {
                var attr = docModel.get('attributes').get(aid);
                if (attr) {
                    return attr.attributes.label;
                }
                return '';
            });
            titleDelete += attrLabels.join(", ");
            $deleteButton.on("click." + this.eventNamespace,function (event) {

                event.preventDefault();
                _.each(attrToClear, function (aid) {
                    var attr = docModel.get('attributes').get(aid);
                    if (attr) {
                        if (attr.hasMultipleOption()) {
                            attr.setValue([], currentWidget.options.index);
                        } else {
                            attr.setValue({value: null, displayValue: ''}, currentWidget.options.index);
                        }
                    }
                });


                currentWidget.element.find("input").focus();
            }).attr('title', titleDelete).kendoTooltip({
                position: "left"
            });
        },

        _model: function () {

            return this._documentModel().get('attributes').get(this.options.id);
        },

        _documentModel: function () {
            return  window.dcp.documents.get(window.dcp.documentData.document.properties.id);
        },
        _getTemplate: function () {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute && window.dcp.templates.attribute[this.getType()]) {
                return window.dcp.templates.attribute[this.getType()];
            }
            return "";
        },

        _isMultiple: function () {
            return (this.options.options && this.options.options.multiple === "yes");
        },

        flashElement: function () {
            this.element.addClass('dcpAttribute__content--flash');
            var currentElement = this.element;
            _.delay(function () {
                currentElement.removeClass('dcpAttribute__content--flash');
                currentElement.addClass('dcpAttribute__content--endflash');
                _.delay(function () {
                    currentElement.removeClass('dcpAttribute__content--endflash');
                }, 600);
            }, 10);
        },

        getType: function () {
            return this.options.type;
        },

        getMode: function () {
            if (this.options.mode !== "read" && this.options.mode !== "write" && this.options.mode !== "hidden") {
                throw new Error("Attribute " + this.options.id + " have unknown mode " + this.options.mode);
            }
            return this.options.mode;
        },

        getValue: function () {
            return this.options.value;
        },

        setValue: function (value, event) {
            this.options.value = value;
            this._trigger("change", event, {
                id: this.options.id,
                value: value,
                index: this.options.index
            });
        }

    });
});