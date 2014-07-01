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
            this._initDom();
            this._initEvent();
        },

        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
        },

        _initEvent: function () {

        },
        _model: function () {
            var documentModel = window.dcp.documents.get(window.dcp.documentData.document.properties.id);
            return documentModel.get('attributes').get(this.options.id);
        },
        _getTemplate: function () {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute && window.dcp.templates.attribute[this.getType()]) {
                return window.dcp.templates.attribute[this.getType()];
            }
            return "";
        },

        _isMultiple : function () {
            return (this.options.options && this.options.options.multiple === "yes");
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