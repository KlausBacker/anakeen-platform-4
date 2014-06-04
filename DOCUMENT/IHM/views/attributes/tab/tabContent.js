/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/frame/frame'
], function (_, Backbone, Mustache, ViewAttributeFrame) {
    'use strict';

    return Backbone.View.extend({

        tagName : "div",

        className : "tab-pane fade",

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render : function () {
            var $content = this.$el, model = this.model;
            console.time("render tab " + this.model.id);
            this.$el.empty();
            this.$el.attr("id", this.model.id);
            this.model.get("content").each(function (currentAttr) {
                var view;
                if (currentAttr.get("type") === "frame") {
                    view = new ViewAttributeFrame({model : currentAttr});
                    $content.prepend(view.render().$el);
                } else {
                    throw "unkown type "+currentAttr.get("type")+" for id "+currentAttr.id+" for tab "+model.id;
                }
            });
            console.timeEnd("render tab " + this.model.id);
            return this;
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        }
    });

});