/*global define*/
define([
    'underscore',
    'backbone',
    'mustache'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        tagName : "li",

        className : "dcpTab__label",

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'errorMessage', this.setError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);
        },

        render : function () {
            //console.time("render tab " + this.model.id);
            this.$el.empty();
            this.$el.text(this.model.get("label"));
            this.$el.attr("data-id", this.model.id);
            //console.timeEnd("render tab " + this.model.id);
            return this;
        },

        setError :    function (event, data) {
            if (data) {
                this.$el.addClass("has-error");
            } else {
                this.$el.removeClass("has-error");
            }
        },

        updateLabel : function () {
            this.$el.text(this.model.get("label"));
        }
    });

});