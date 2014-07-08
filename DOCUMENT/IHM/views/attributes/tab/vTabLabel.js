/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/frame/vFrame'
], function (_, Backbone, Mustache, ViewAttributeFrame) {
    'use strict';

    return Backbone.View.extend({

        tagName : "li",

        className : "",

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateLabel = window.dcp.templates.attribute.tab.label;
        },

        render : function () {
            console.time("render tab " + this.model.id);
            this.$el.empty();
            this.$el.append($(Mustache.render(this.templateLabel, this.model.toJSON())));
            console.timeEnd("render tab " + this.model.id);
            return this;
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        }
    });

});