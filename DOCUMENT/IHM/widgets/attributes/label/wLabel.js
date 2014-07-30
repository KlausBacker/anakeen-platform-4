define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpLabel", {

        _create: function () {
            this._initDom();
        },

        _initDom: function () {
            this.element.append(Mustache.render(this._getTemplate(), this.options));
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
            if (this.options.templates.label) {
                return this.options.templates.label;
            }
            throw new Error("Unknown label template ");
        }
    });
});