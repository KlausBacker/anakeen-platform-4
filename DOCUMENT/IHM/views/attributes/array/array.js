/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/attribute'
], function (_, Backbone, Mustache, ViewAttribute, ViewAttributeArray) {
    'use strict';

    return Backbone.View.extend({

        className : "panel panel-default css-frame frame",

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateLabel = window.dcp.templates.attribute.frame.label;
            this.templateContent = window.dcp.templates.attribute.frame.content;
        },

        render : function () {
            var $content;
            this.$el.empty();
            this.$el.append($(Mustache.render(this.templateLabel, this.model.toJSON())));
            this.$el.append($(Mustache.render(this.templateContent, this.model.toJSON())));
            $content = this.$el.find(".dcpFrame__content");
            this.model.get("content").each(function(attributeModel) {
                if (attributeModel.get("visibility") !== "H" && attributeModel.get("valueAttribute")) {
                    $content.append((new ViewAttribute({model : attributeModel})).render().$el);
                }
            });
            return this;
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        }
    });

});