define([
    'underscore',
    'mustache',
    '../attribute'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpDocid", $.dcp.dcpAttribute, {

        options : {
            id : "",
            type : "docid"
        },

        _initDom : function () {
            if (this.getMode() === "read") {
                if (this.options.options && this.options.options.multiple === "yes") {
                    this.options.values= _.compact(this.options.value);
                    console.log("Multiple",this.options);
                }

                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
                this.element.find('a').kendoButton();
            } else if (this.getMode() === "write") {
                this.element.append(Mustache.render(this._getTemplate(this.getMode()), this.options));
            }
        },

        _initEvent : function() {
            var currentWidget = this;
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").on("change."+this.eventNamespace, function() {

                    currentWidget.options.value.value = $(this).val();
                    currentWidget.setValue(currentWidget.options.value);
                });
            }
        },

        setValue : function(value) {
            this._super(value);
            if (this.getMode() === "write") {
                this.element.find(".dcpAttribute__content").val(value.value);
                return;
            }
            if (this.getMode() === "read") {
                this.element.find(".dcpAttribute__content").text(value.displayValue);
                return;
            }
        throw new Error("Attribute "+this.options.id+" unkown mode "+this.getMode());
        },

        _getTemplate : function (name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute && window.dcp.templates.attribute[this.getType()] && window.dcp.templates.attribute[this.getType()][name]) {
                return window.dcp.templates.attribute[this.getType()][name];
            }
            throw new Error("Unknown template docid "+name);
        },

        getType : function() {
            return "docid";
        }

    });
});