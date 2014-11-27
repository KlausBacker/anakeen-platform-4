/*global define*/
define([
    'underscore',
    'backbone',
    'views/attributes/frame/vFrame'
], function (_, Backbone, ViewAttributeFrame) {
    'use strict';

    return Backbone.View.extend({

        tagName: "div",

        className: "tab-pane",

        initialize: function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render: function () {
            var $content = this.$el, model = this.model;
            console.time("render tab " + this.model.id);
            this.$el.empty();
            this.$el.attr("id", this.model.id);

            var hasOneContent = this.model.get("content").some(function (value) {
                return value.isDisplayable();
            });

            if (!hasOneContent) {
                $content.append(this.model.getOption('showEmptyContent'));
            } else {
                this.model.get("content").each(function (currentAttr) {
                    var view;
                    try {
                        if (!currentAttr.isDisplayable()) {
                            $(".dcpLoading").dcpLoading("addItem", currentAttr.attributes.content.length );
                            return;
                        }
                        if (currentAttr.get("type") === "frame") {
                            view = new ViewAttributeFrame({model: currentAttr});
                            $content.append(view.render().$el);
                        } else {
                            throw new Error("unkown type " + currentAttr.get("type") + " for id " + currentAttr.id + " for tab " + model.id);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            }
            console.timeEnd("render tab " + this.model.id);
            this.trigger("renderDone");
            return this;
        },

        updateLabel: function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        }
    });

});