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
             "dcpattributechange .dcpAttribute__contentWrapper" : "updateValue"
        },

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:value', this.refreshValue);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateWrapper = dcp.templates.attribute.simpleWrapper;
        },

        render : function () {
            console.time("render attribute " + this.model.id);
            var data = this.model.toJSON();
            data.viewCid = this.cid;
			data.options=this.model.getOptions();
            this.$el.addClass("dcpAttribute--type--"+this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            this.$el.append($(Mustache.render(this.templateWrapper, data)));
            this.$el.find(".dcpAttribute__label").dcpLabel(data);
            this.$el.find(".dcpAttribute__contentWrapper").dcpText(data);
            console.timeEnd("render attribute " + this.model.id);
            return this;
        },

        refreshLabel : function () {
            this.$el.find(".dcpAttribute__label").dcpLabel("setLabel", this.model.get("label"));
        },

        refreshValue : function (value) {
            this.$el.find(".dcpAttribute__contentWrapper").dcpText("setValue", this.model.get("value"));
        },

        updateValue : function() {
            this.model.setValue(this.$el.find(".dcpAttribute__contentWrapper").dcpText("getValue"));
        }
    });


});