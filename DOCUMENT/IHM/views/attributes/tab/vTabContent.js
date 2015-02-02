/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/frame/vFrame',
    'views/document/attributeTemplate'
], function (_, Backbone, Mustache, ViewAttributeFrame, attributeTemplate) {
    'use strict';

    return Backbone.View.extend({

        tagName: "div",

        className: "dcpTab__content",
        customView: false,

        initialize: function vTabContentInitialize(options) {
            if (options.customView) {
                this.customView = options.customView;
            }
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'showTab', this.renderContent);
            this.listenTo(this.model, 'showTab', this.propageShowTab);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
            this.listenTo(this.model, 'haveView', this._identifyView);
            this.initializeContent = options.initializeContent;
            this.initialized = false;
            this.options = options;
        },

        render: function vTabContentRender() {
            var hasOneContent;
            this.$el.empty();
            this.$el.attr("id", this.model.id);
            this.$el.append('<p> Loading : <i class="fa fa-spinner fa-spin"></i></p>');
            this.$el.attr("data-attrid", this.model.id);

            hasOneContent = this.model.get("content").some(function vTabContentIsDisplayable(value) {
                return value.isDisplayable();
            });

            if (!hasOneContent || !this.initializeContent) {
                this.$el.append(this.model.getOption('showEmptyContent'));
            } else {
                this.renderContent();
            }
            this.model.trigger("renderDone", this.model);
            return this;
        },

        renderContent: function vTabContentRenderContent() {
            var $content = this.$el, model = this.model;
            if (this.initialized === false) {
                this.$el.empty();
                console.time("render tab " + this.model.id);
                if (this.customView) {
                    $content.append(this.customView);
                } else {
                    this.model.get("content").each(function vTabContentRenderContent(currentAttr) {
                        var view, customView = null;
                        try {
                            if (!currentAttr.isDisplayable()) {
                                return;
                            }
                            if (currentAttr.get("type") === "frame") {
                                if (currentAttr.getOption("template")) {
                                    // @TODO I don't know why but need require one more time
                                    if (_.isUndefined(attributeTemplate) || !attributeTemplate.customView) {
                                        /*global require*/
                                        attributeTemplate = require('views/document/attributeTemplate');
                                    }
                                    customView = attributeTemplate.customView(currentAttr);
                                }
                                // @TODO I don't know why but need require one more time
                                if (_.isUndefined(ViewAttributeFrame)) {
                                    /*global require*/
                                    ViewAttributeFrame = require('views/attributes/frame/vFrame');
                                }
                                view = new ViewAttributeFrame({model: currentAttr, customView: customView});
                                $content.append(view.render().$el);

                            } else {
                                //noinspection ExceptionCaughtLocallyJS
                                throw new Error("unkown type " + currentAttr.get("type") + " for id " + currentAttr.id + " for tab " + model.id);
                            }
                        } catch (e) {
                            if (window.dcp.logger) {
                                window.dcp.logger(e);
                            } else {
                                console.error(e);
                            }
                        }
                    });
                }
                this.initialized = true;
                console.timeEnd("render tab " + this.model.id);
            }
            $(window.document).trigger("redrawErrorMessages");
        },

        propageShowTab: function vTabContentPropageShowTab() {
            this.model.get("content").propageEvent('showTab');
        },

        updateLabel: function vTabContentUpdateLabel() {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        hide: function vTabContentHide() {
            this.$el.hide();
        },

        show: function vTabContentShow() {
            this.$el.show();
        },

        _identifyView: function vAttribute_identifyView(event) {
            event.haveView = true;
        }
    });

});