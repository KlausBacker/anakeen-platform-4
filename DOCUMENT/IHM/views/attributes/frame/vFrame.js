/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/vAttribute',
    'views/attributes/array/vArray'
], function (_, Backbone, Mustache, ViewAttribute, ViewAttributeArray) {
    'use strict';

    return Backbone.View.extend({

        className : "panel panel-default dcpDocument__frame",

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
            var $content;
            this.templateLabel = this.model.getTemplates().attribute.frame.label;
            this.templateContent = this.model.getTemplates().attribute.frame.content;
            var labelElement = $(Mustache.render(this.templateLabel, this.model.toJSON()));
            var contentElement = $(Mustache.render(this.templateContent, this.model.toJSON()));

            this.$el.empty();
            this.$el.append(labelElement);
            this.$el.append(contentElement);

            labelElement.on("click", _.bind(this.toggle, this));
            $content = this.$el.find(".dcpFrame__content");
            var hasOneContent = this.model.get("content").some(function (value) {
                return value.isDisplayable();
            });
            if (!hasOneContent) {
                $content.append(this.model.getOption('showEmptyContent'));
            } else {
                this.model.get("content").each(function (currentAttr) {
                    if (!currentAttr.isDisplayable()) {
                        return;
                    }
                    try {
                        if (currentAttr.get("valueAttribute")) {
                            $content.append((new ViewAttribute({model : currentAttr})).render().$el);
                            return;
                        }
                        if (currentAttr.get("type") === "array") {
                            $content.append((new ViewAttributeArray({model : currentAttr})).render().$el);
                        }
                    } catch (e) {
                        $content.append('<h1 class="bg-danger"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>Unable to render ' + currentAttr.id + '</h1>');
                        window.TraceKit.report(e);
                        console.error(e);
                    }
                });
            }
            this.trigger("renderDone");
            //console.timeEnd("render frame " + this.model.id);
            return this;
        },

        getAttributeModel : function (attributeId) {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        setError : function (event, data) {
            var parentId = this.model.get('parent');
            if (data) {
                this.$el.find(".dcpFrame__label").addClass("has-error");
            } else {
                this.$el.find(".dcpFrame__label").removeClass("has-error");
            }
            if (parentId) {
                var parentModel = this.getAttributeModel(parentId);
                if (parentModel) {
                    parentModel.trigger("errorMessage", event, data);
                }
            }
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        toggle : function () {
            var $contentElement = this.$(".dcpFrame__content");
            if ($contentElement.hasClass("dcpFrame__content--open")) {
                // Hide frame panel
                this.$(".dcp__frame__caret").addClass("fa-caret-right").removeClass("fa-caret-down");
                $contentElement.removeClass("dcpFrame__content--open").addClass("dcpFrame__content--close");
                $contentElement.slideUp();
            } else {
                // Show frame panel
                this.$(".dcp__frame__caret").removeClass("fa-caret-right").addClass("fa-caret-down");
                $contentElement.addClass("dcpFrame__content--open").removeClass("dcpFrame__content--close");
                $contentElement.slideDown();
            }

        }
    });

});