/*global define, console*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/vAttribute',
    'dcpDocument/views/attributes/array/vArray',
    'dcpDocument/views/document/attributeTemplate'
], function require_vFrame($, _, Backbone, Mustache, ViewAttribute, ViewAttributeArray, attributeTemplate)
{
    'use strict';

    return Backbone.View.extend({

        className: "card card-default dcpFrame",
        customView: false,
        displayLabel: true,

        events: {
            "click .dcpFrame--collapsable": "toggle",
            'click a[href^="#action/"], a[data-action], button[data-action]': 'externalLinkSelected'
        },

        initialize: function vFrame_initialize(options)
        {

            if (options.displayLabel === false || this.model.getOption("labelPosition") === "none") {
                this.displayLabel = false;
            }
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'errorMessage', this.setError);
            this.listenTo(this.model, 'change:errorMessage', this.setError);
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

        render: function vFrame_render()
        {
            var $content, labelElement, contentElement = '', customView = null, event = {prevent: false};
            var contentData, helpId, documentModel = this.model.getDocumentModel();

            this.model.trigger("beforeRender", event, {model: this.model, $el: this.$el});
            if (event.prevent) {
                return this;
            }
            contentData = this.model.toData(null, true);
            if (this.model.getOption("attributeLabel")) {
                contentData.label = this.model.getOption("attributeLabel");
            }
            contentData.collapsable = (contentData.renderOptions.collapse !== "none");

            this.templateLabel = this.model.getTemplates().attribute.frame.label;
            labelElement = $(Mustache.render(this.templateLabel || "", contentData));

            if (this.customView) {
                contentElement = this.customView;
                contentElement.addClass("dcpFrame__content dcpFrame__content--open");
            } else {
                this.templateContent = this.model.getTemplates().attribute.frame.content;
                contentElement = $(Mustache.render(this.templateContent || "", contentData));
            }
            this.$el.empty();
            if (this.displayLabel === true) {
                this.$el.append(labelElement);
            }
            this.$el.append(contentElement);
            this.$el.attr("data-attrid", this.model.id);

            $content = this.$el.find(".dcpFrame__content");
            var hasOneContent = this.model.get("content").some(function vFrame_getDisplayable(value)
            {
                return value.isDisplayable();
            });

            if (!this.customView) {
                if (!hasOneContent) {
                    $content.append(this.model.getOption('showEmptyContent'));
                } else {
                    this.model.get("content").each(function vFrame_AnalyzeContent(currentAttr)
                    {
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
                            $content.append('<h1 class="bg-danger"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span>Unable to render ' + currentAttr.id + '</h1>');
                            if (window.dcp.logger) {
                                window.dcp.logger(e);
                            } else {
                                console.error(e);
                            }
                        }
                    });
                }

                attributeTemplate.insertDescription(this);
            }

            if (this.model.getOption("collapse") === "collapse") {
                this.toggle(null, true);
            }
            this.model.trigger("renderDone", {model: this.model, $el: this.$el});
            return this;
        },

        getAttributeModel: function vFrame_getAttributeModel(attributeId)
        {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        setError: function vFrame_setError(event, data)
        {
            if (data) {
                this.$el.find(".dcpFrame__label").addClass("has-error");
            } else {
                this.$el.find(".dcpFrame__label").removeClass("has-error");
            }
        },

        updateLabel: function vFrame_updateLabel()
        {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        toggle: function vFrame_toggle(event, hideNow)
        {
            var $contentElement = this.$(".dcpFrame__content");
            this.$(".dcp__frame__caret").toggleClass("fa-caret-right fa-caret-down");
            $contentElement.toggleClass("dcpFrame__content--open dcpFrame__content--close");
            if (hideNow) {
                $contentElement.hide();
            } else {
                $contentElement.slideToggle(200);
                if ($contentElement.hasClass("dcpFrame__content--open")) {
                    this.model.getDocumentModel().redrawErrorMessages();
                }
            }
        },

        hide: function vFrame_hide()
        {
            this.$el.hide();
        },

        show: function vFrame_show()
        {
            this.$el.show();
        },

        externalLinkSelected: function vAttributeExternalLinkSelected(event)
        {
            var $target = $(event.currentTarget),
                action,
                options,
                eventOptions,
                documentModel,
                internalEvent = {
                    prevent: false
                };

            event.preventDefault();
            if (event.stopPropagation) {
                event.stopPropagation();
            }

            action = $target.data('action') || $target.attr("href");
            options = action.substring(8).split(":");
            eventOptions = {
                target: event.target,
                index: -1,
                eventId: options.shift(),
                options: options
            };
            documentModel = this.model.getDocumentModel();

            this.model.trigger("internalLinkSelected", internalEvent, eventOptions);
            if (event.prevent) {
                return this;
            }

            documentModel.trigger("actionAttributeLink", internalEvent, eventOptions);

            return this;
        },

        _identifyView: function vFrame_identifyView(event)
        {
            event.haveView = true;
            //Add the pointer to the current jquery element to a list passed by the event
            event.elements = event.elements.add(this.$el);
        }
    });

});
