define([
    'underscore',
    'mustache',
    'widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpAttribute", {

        options : {
            type : "abstract",
            mode : "read"
        },

        _create : function() {
            this._initDom();
            this._initEvent();
        },

        _initDom : function() {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
        },

        _initEvent : function() {

        },

        _getTemplate : function () {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute
                && window.dcp.templates.attribute[this.getType()]) {
                return window.dcp.templates.attribute[this.getType()];
            }
            return "";
        },

        getType : function() {
            return this.options.type;
        },

        getMode : function() {
            if (this.options.mode !== "read" && this.options.mode !== "write") {
                throw "Attribute "+this.options.id+" have unknown mode "+this.options.mode;
            }
            return this.options.mode;
        },

        getValue : function() {
            return this.options.value;
        },

        setValue : function(value, event) {
            this.options.value = value;
            this._trigger("change", event, {
                value : value
            })
        }

    });
});