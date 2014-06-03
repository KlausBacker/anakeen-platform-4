define([
    'underscore',
    'mustache',
    '../attribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpText", $.dcp.dcpAttribute, {

        options : {
            type : "text"
        },

        _initDom : function () {
            this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
        },

        _initEvent : function() {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").on("change", function() {
                    currentWidget.setValue($(this).val());
                });
            }
        },

        setValue : function(value) {
            this._super(value);
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").val(value);
                return;
            }
            if (this.getMode() === "read") {
                this.element.find(".dcpAttribute__content").text(value);
                return;
            }
            throw "Unkown mode";
        },

        _getTemplate : function (name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute
                && window.dcp.templates.attribute[this.getType()]
                && window.dcp.templates.attribute[this.getType()][name]) {
                return window.dcp.templates.attribute[this.getType()][name];
            }
            throw "Unknown template text "+name;
        },

        getType : function() {
            return "text";
        }

    });
});