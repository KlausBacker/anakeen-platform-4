define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpArray", {

        options : {
                  tools : true
        },

        _create : function () {
            this.options.tools = this.options.mode === "write";
            this._initDom();
        },

        _initDom : function () {
            this.element.addClass("panel panel-default");
            this.element.append(Mustache.render(this._getTemplate("label"), this.options));
            this.element.append(Mustache.render(this._getTemplate("content"), this.options));
            this.refreshLines();
        },

        _bindEvent : function() {

        },

        refreshLines : function() {
            var i;
            this.element.find(".dcpArray__body").empty();
            for (i = 0; i < this.options.nbLines; i++) {
                this.addLine(i);
            }
            this._trigger("linesGenerated");
        },

        addLine : function(nbLine) {
            var $content = $(Mustache.render(this._getTemplate("line"), _.extend({nbLine : nbLine}, this.options)));
            this.element.find(".dcpArray__body").append($content);
            this._trigger("lineAdded", {}, {line : nbLine, element : $content});
        },

        _getTemplate : function (name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute
                && window.dcp.templates.attribute.array && window.dcp.templates.attribute.array[name]) {
                return window.dcp.templates.attribute.array[name];
            }
            throw "Unknown array template "+name;
        }
    });
});