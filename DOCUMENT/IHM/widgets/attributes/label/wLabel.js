define([
    'underscore',
    'mustache',
    'dcpDocument/widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpLabel", {

        _create: function () {
            this._initDom();
        },

        _initDom: function () {
            this.element.addClass("dcpAttribute__label control-label dcpLabel");
            this.element.append(Mustache.render(this._getTemplate(), this.options));
            if (this.options.renderOptions && this.options.renderOptions.attributeLabel) {
                this.setLabel(this.options.renderOptions.attributeLabel);
            }
        },

        setLabel: function (label) {
            this.element.text(label);
        },

        setError: function (message) {
            if (message) {
                this.element.addClass("has-error");
            } else {
                this.element.removeClass("has-error");
            }
        },

        _getTemplate: function () {
            if (this.options.templates && this.options.templates.label) {
                return this.options.templates.label;
            }
            if (window.dcp && window.dcp.templates && window.dcp.templates.label) {
                return window.dcp.templates.label;
            }
            throw new Error("Unknown label template ");
        }
    });

    return $.fn.dcpLabel;
});