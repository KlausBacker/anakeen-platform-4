/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'widgets/attributes/label/label',
    'widgets/attributes/text/text',
    'widgets/attributes/docid/docid'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        className: "row dcpAttribute form-group",

        events: {
            "dcpattributechange .dcpAttribute__contentWrapper": "updateValue"
        },

        initialize: function () {
            this.listenTo(this.model, 'change:label', this.refreshLabel);
            this.listenTo(this.model, 'change:value', this.refreshValue);
            this.listenTo(this.model, 'change:errorMessage', this.refreshError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateWrapper = window.dcp.templates.attribute.simpleWrapper;
        },

        render: function () {
            console.time("render attribute " + this.model.id);
            var data = this.model.toJSON();
            data.viewCid = this.cid;
            data.renderOptions = this.model.getOptions();
            this.$el.addClass("dcpAttribute--type--" + this.model.get("type"));
            this.$el.addClass("dcpAttribute--visibility--" + this.model.get("visibility"));
            if (this.model.get("needed")) {
                this.$el.addClass("dcpAttribute--needed");
            }
            this.$el.append($(Mustache.render(this.templateWrapper, data)));
            this.$el.find(".dcpAttribute__label").dcpLabel(data);
            this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), data);

            console.timeEnd("render attribute " + this.model.id);
            return this;
        },

        refreshLabel: function () {
            this.$el.find(".dcpAttribute__label").dcpLabel("setLabel", this.model.get("label"));
        },

        refreshValue: function () {
            this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), "setValue", this.model.get("value"));

        },
        refreshError: function () {
            console.log("WEH",this.model.get("errorMessage") );
            this.$el.find(".dcpAttribute__label").dcpLabel("setError", this.model.get("errorMessage"));
            this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), "setError", this.model.get("errorMessage"));


        },

        updateValue: function () {

            this.model.setValue(this.widgetApply(this.$el.find(".dcpAttribute__contentWrapper"), "getValue"));
        },

        widgetApply: function ($element, method, argument) {
            return this.getWidgetClass().apply($element, [method, argument]);
        },

        getWidgetClass: function () {
            switch (this.model.get("type")) {
                case "text" :
                    return $.fn.dcpText;
                case "account" :
                case "docid" :
                    return $.fn.dcpDocid;
                default:
                    return $.fn.dcpText;
            }
        }

    });


});