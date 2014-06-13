/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/document/menu/menu',
    'views/attributes/frame/frame',
    'views/attributes/tab/tabLabel',
    'views/attributes/tab/tabContent'
], function (_, Backbone, Mustache, ViewDocumentMenu, ViewAttributeFrame, ViewAttributeTabLabel, ViewAttributeTabContent) {
    'use strict';

    return Backbone.View.extend({

        className : "dcpDocument container-fluid",

        initialize : function () {
            this.listenTo(this.model, 'destroy', this.remove);
            this.template = window.dcp.templates.body;
            this.partials = window.dcp.templates.sections;
        },

        render : function () {
            var $content, model = this.model, $el = this.$el;
            console.time("render doc");
            //add document base
            try {
                this.$el.empty().append($(Mustache.render(this.template, this.model.toData(), this.partials)));
            } catch (e) {
                console.log(e);
            }
            //add menu
            console.time("render menu");
            try {
                new ViewDocumentMenu({model : this.model, el : this.$el.find(".dcpDocument__menu")[0]}).render();
            } catch(e) {
                console.log(e);
            }
            console.timeEnd("render menu");
            //add first level attributes
            console.time("render attributes");
            $content = this.$el.find(".dcpDocument__frames");
            this.model.get("attributes").each(function (currentAttr) {
                var view, viewTabLabel, viewTabContent;
                if (!currentAttr.isDisplayable()) {
                    return;
                }
                if (currentAttr.get("type") === "frame" && currentAttr.get("parent") === undefined) {
                    try {
                        view = new ViewAttributeFrame({model : model.get("attributes").get(currentAttr.id)});
                        $content.prepend(view.render().$el);
                    } catch(e) {
                        console.error(e);
                    }
                }
                if (currentAttr.get("type") === "tab" && currentAttr.get("parent") === undefined) {
                    try {
                        viewTabLabel = new ViewAttributeTabLabel({model : model.get("attributes").get(currentAttr.id)});
                        viewTabContent = new ViewAttributeTabContent({model : model.get("attributes").get(currentAttr.id)});
                        $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);
                        $el.find(".dcpDocument__tabs__content").append(viewTabContent.render().$el);
                    } catch(e) {
                        console.error(e);
                    }
                }
            });
            $el.find('.dcpDocument__tabs__list a:first').tab('show');
            console.timeEnd("render attributes");
            console.timeEnd("render doc");
            return this;
        }
    });

});