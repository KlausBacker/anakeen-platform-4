define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpLabel", {

        _create : function () {
            this._initDom();
        },

        _initDom : function() {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
        },

        setLabel : function(label) {
            this.element.text(label);
        },

        _getTemplate : function () {
            if (window.dcp && window.dcp.templates && window.dcp.templates.attribute && window.dcp.templates.attribute.label) {
                return window.dcp.templates.attribute.label;
            }
            throw new Error("Unknown label template ");
        }
    });
});