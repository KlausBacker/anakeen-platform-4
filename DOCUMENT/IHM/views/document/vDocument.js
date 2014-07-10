/*global define*/



define([
    'underscore',
    'backbone',
    'mustache',
    'views/document/menu/vMenu',
    'views/attributes/frame/vFrame',
    'views/attributes/tab/vTabLabel',
    'views/attributes/tab/vTabContent'
], function (_, Backbone, Mustache, ViewDocumentMenu, ViewAttributeFrame, ViewAttributeTabLabel, ViewAttributeTabContent) {
    'use strict';

    return Backbone.View.extend({

        className: "dcpDocument container-fluid",

        initialize: function () {
            this.listenTo(this.model, 'destroy', this.remove);
            this.template = window.dcp.templates.body;
            this.partials = window.dcp.templates.sections;
        },

        render: function () {
            var $content, model = this.model, $el = this.$el;
            var $loading = $(".dcpLoading");
            console.time("render doc");
            //add document base
            try {
                this.$el.empty().append($(Mustache.render(this.template, this.model.toData(), this.partials)));
            } catch (e) {
                console.log(e);
            }
            $loading.dcpLoading("percent", 10);
            //add menu
            console.time("render menu");
            try {
                new ViewDocumentMenu({model: this.model, el: this.$el.find(".dcpDocument__menu")[0]}).render();
            } catch (e) {
                console.log(e);
            }
            console.timeEnd("render menu");
            $loading.dcpLoading("percent", 20);
            //add first level attributes
            console.time("render attributes");
            $content = this.$el.find(".dcpDocument__frames");
            $loading.dcpLoading("setRest", this.model.get("attributes").length);
            this.model.get("attributes").each(function (currentAttr) {
                var view, viewTabLabel, viewTabContent, tabItems;
                if (!currentAttr.isDisplayable()) {
                    return;
                }

                if (currentAttr.get("type") === "frame" && currentAttr.get("parent") === undefined) {
                    try {
                        view = new ViewAttributeFrame({model: model.get("attributes").get(currentAttr.id)});
                        $content.prepend(view.render().$el);
                    } catch (e) {
                        console.error(e);
                    }
                }
                if (currentAttr.get("type") === "tab" && currentAttr.get("parent") === undefined) {
                    try {
                        console.log("TAB,", $el);
                        viewTabLabel = new ViewAttributeTabLabel({model: model.get("attributes").get(currentAttr.id)});
                        viewTabContent = new ViewAttributeTabContent({model: model.get("attributes").get(currentAttr.id)});
                        $el.find(".dcpDocument__tabs__list").append(viewTabLabel.render().$el);
                        tabItems=$el.find(".dcpDocument__tabs__list").find('li');
                        if (tabItems.length > 1) {
                          tabItems.css("width",Math.floor(100/tabItems.length)+'%').kendoTooltip({
                              position:"top",
                              content: function(e) {
                                  var target = e.target; // the element for which the tooltip is shown
                                  return $(target).text(); // set the element text as content of the tooltip
                              }
                          });
                        }

                        $el.find(".dcpDocument__tabs__content").append(viewTabContent.render().$el);
                        $el.find(".dcpDocument__tabs").show();
                    } catch (e) {
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