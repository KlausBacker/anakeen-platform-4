define([
    'underscore',
    'mustache',
    '../attribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpText", $.dcp.dcpAttribute, {

        options: {
            id: "",
            type: "text"
        },
        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
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