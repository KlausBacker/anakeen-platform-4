/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/document/menu/menu',
    'views/attributes/frame/frame'
], function (_, Backbone, Mustache, ViewDocumentMenu, ViewAttributeFrame) {
    'use strict';

    return Backbone.View.extend({

        className : "dcpDocument container-fluid",

        initialize : function () {
            this.listenTo(this.model, 'destroy', this.remove);
            this.template = window.dcp.templates.body;
            this.partials = window.dcp.templates.sections;
        },

        render : function () {
            var $content, model = this.model;
            //add document base
            this.$el.empty().append($(Mustache.render(this.template, this.model.toData(), this.partials)));
            //add menu
            new ViewDocumentMenu({model : this.model, el : this.$el.find(".dcpDocument__menu")[0]}).render();
            //add first level attributes
            $content = this.$el.find(".dcpDocument__form");
            this.model.get("attributes").each(function (currentAttr) {
                var view;
                if (currentAttr.get("type") === "frame" && currentAttr.get("parent") === undefined) {
                    view = new ViewAttributeFrame({model : model.get("attributes").get(currentAttr.id)});
                    $content.prepend(view.render().$el);
                }
            });
            return this;
        }
    });

});