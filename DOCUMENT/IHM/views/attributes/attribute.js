/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'widgets/attributes/label/label',
    'widgets/attributes/text/text'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        className : "row dcpAttribute form-group",

        events : {
             "change .dcpAttribute__contentWrapper" : "updateValue"
        },

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:value', this.refreshValue);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateWrapper = dcp.templates.attribute.simpleWrapper;
        },

        render : function () {
            var data = this.model.toJSON();
            this.$el.addClass("dcpAttribute--type--"+this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            this.$el.append($(Mustache.render(this.templateWrapper, data)));
            this.$el.find(".dcpAttribute__labelWrapper").dcpLabel(data);
            this.$el.find(".dcpAttribute__contentWrapper").dcpText(data);
            return this;
        },

        refreshLabel : function () {
            this.$el.find(".dcpAttribute__labelWrapper").dcpLabel("setLabel", this.model.get("label"));
        },

        refreshValue : function (value) {
            this.$el.find(".dcpAttribute__contentWrapper").dcpText("setValue", this.model.get("value"));
        },

        updateValue : function() {
            this.model.set("value", this.$el.find(".dcpAttribute__contentWrapper").dcpText("getValue"));
        }
    });


});