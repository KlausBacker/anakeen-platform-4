/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/vAttribute',
    'dcpDocument/views/attributes/array/vArray',
    'dcpDocument/views/document/attributeTemplate'
], function (_, Backbone, Mustache, ViewAttribute, ViewAttributeArray, attributeTemplate) {
    'use strict';

    return Backbone.View.extend({

        className: "panel panel-default dcpFrame",
        customView:false,
        displayLabel:true,

        initialize: function vFrame_initialize(options) {

            if (options.displayLabel === false || this.model.getOption("labelPosition")==="none") {
                this.displayLabel=false;
            }
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'errorMessage', this.setError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
            this.listenTo(this.model, 'haveView', this._identifyView);
            if (options.originalView !== true) {
                if (this.model.getOption("template")) {
                    this.customView = attributeTemplate.customView(this.model);
                }
            }
            this.options = options;
        },

        render: function vFrame_render() {
            var $content;
            var labelElement;
            var contentElement = '';
            var customView = null;

            this.templateLabel = this.model.getTemplates().attribute.frame.label;
            labelElement = $(Mustache.render(this.templateLabel, this.model.toJSON()));

            if (this.customView) {
                contentElement = this.customView;
                contentElement.addClass("dcpFrame__content--open");
            } else {
                this.templateContent = this.model.getTemplates().attribute.frame.content;
                contentElement = $(Mustache.render(this.templateContent, this.model.toJSON()));
            }
            this.$el.empty();
            if (this.displayLabel === true) {
                this.$el.append(labelElement);
            }
            this.$el.append(contentElement);
            this.$el.attr("data-attrid", this.model.id);

            labelElement.on("click", _.bind(this.toggle, this));
            $content = this.$el.find(".dcpFrame__content");
            var hasOneContent = this.model.get("content").some(function vFrame_getDisplayable(value) {
                return value.isDisplayable();
            });

            if (!this.customView) {
                if (!hasOneContent) {
                    $content.append(this.model.getOption('showEmptyContent'));
                } else {
                    this.model.get("content").each(function vFrame_AnalyzeContent(currentAttr) {
                        if (!currentAttr.isDisplayable()) {
                            return;
                        }
                        try {
                            customView = null;
                            if (currentAttr.get("isValueAttribute")) {

                                $content.append((new ViewAttribute({
                                    model: currentAttr,
                                    customView: customView
                                })).render().$el);
                                return;
                            }
                            if (currentAttr.get("type") === "array") {
                                $content.append((new ViewAttributeArray({
                                    model: currentAttr
                                })).render().$el);
                            }
                        } catch (e) {
                            $content.append('<h1 class="bg-danger"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>Unable to render ' + currentAttr.id + '</h1>');
                            if (window.dcp.logger) {
                                window.dcp.logger(e);
                            } else {
                                console.error(e);
                            }
                        }
                    });
                }
            }
            this.model.trigger("renderDone", {model : this.model, $el : this.$el});
            //console.timeEnd("render frame " + this.model.id);
            return this;
        },


        getAttributeModel: function vFrame_getAttributeModel(attributeId) {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        setError: function vFrame_setError(event, data) {
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

        updateLabel: function vFrame_updateLabel() {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        toggle: function vFrame_toggle() {
            var $contentElement = this.$(".dcpCustomTemplate");
            if ($contentElement.length === 0) {
                $contentElement = this.$(".dcpFrame__content");
            }
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
        },

        hide: function vFrame_hide() {
            this.$el.hide();
        },

        show: function vFrame_show() {
            this.$el.show();
        },

        _identifyView: function vFrame_identifyView(event) {
            event.haveView = true;
            //Add the pointer to the current jquery element to a list passed by the event
            event.elements = event.elements.add(this.$el);
        }
    });

});