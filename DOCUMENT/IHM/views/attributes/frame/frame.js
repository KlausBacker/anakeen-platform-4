/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/attribute',
    'views/attributes/array/array'
], function (_, Backbone, Mustache, ViewAttribute, ViewAttributeArray) {
    'use strict';

    return Backbone.View.extend({

        className: "panel panel-default css-frame frame",

        initialize: function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'destroy', this.remove);
            this.templateLabel = window.dcp.templates.attribute.frame.label;
            this.templateContent = window.dcp.templates.attribute.frame.content;
        },

        render: function () {
            var $content;
            var $loading = $(".dcpLoading");
            var labelElement = $(Mustache.render(this.templateLabel, this.model.toJSON()));
            var contentElement = $(Mustache.render(this.templateContent, this.model.toJSON()));
            console.time("render frame " + this.model.id);
            this.$el.empty();
            this.$el.append(labelElement);
            this.$el.append(contentElement);

            contentElement.collapse('show');
            labelElement.on("click", function () {
                if (contentElement.hasClass("in")) {
                    $(this).find("i").addClass("fa-caret-down").removeClass("fa-caret-up");
                } else {
                    $(this).find("i").removeClass("fa-caret-down").addClass("fa-caret-up");
                }
                contentElement.collapse('toggle');
            });
            $content = this.$el.find(".dcpFrame__content");

            $loading.dcpLoading("addItem");
            var hasOneContent = this.model.get("content").some(function (value) {
                return value.isDisplayable();
            });

            if (!hasOneContent) {
                $content.append(this.model.getOption('showEmptyContent'));
                $loading.dcpLoading("addItem", this.model.get("content").length);
            } else {
                this.model.get("content").each(function (currentAttr) {
                    $loading.dcpLoading("addItem");
                    if (!currentAttr.isDisplayable()) {
                        return;
                    }

                    try {
                        if (currentAttr.get("valueAttribute")) {
                            $content.append((new ViewAttribute({model: currentAttr})).render().$el);
                            return;
                        }
                        if (currentAttr.get("type") === "array") {
                            $content.append((new ViewAttributeArray({model: currentAttr})).render().$el);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            }
            console.timeEnd("render frame " + this.model.id);
            return this;
        },

        updateLabel: function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        }
    });

});