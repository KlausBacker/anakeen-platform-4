/*global define*/
define([
    'underscore',
    'backbone',
    'views/attributes/frame/vFrame'
], function (_, Backbone, ViewAttributeFrame) {
    'use strict';

    return Backbone.View.extend({

        tagName : "div",

        className : "dcpTab__content",

        initialize : function (options) {
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
            this.initializeContent = options.initializeContent;
            this.initialized = false;
        },

        render : function () {
            this.$el.empty();
            this.$el.attr("id", this.model.id);
            this.$el.append('<p> Loading : <i class="fa fa-spinner fa-spin"></i></p>');
            this.$el.attr("data-attrid", this.model.id);

            var hasOneContent = this.model.get("content").some(function (value) {
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

        renderContent : function () {
            var $content = this.$el, model = this.model;
            if (this.initialized === false) {
                this.$el.empty();
                console.time("render tab " + this.model.id);
                this.model.get("content").each(function (currentAttr) {
                    var view;
                    try {
                        if (!currentAttr.isDisplayable()) {
                            return;
                        }
                        if (currentAttr.get("type") === "frame") {
                            view = new ViewAttributeFrame({model : currentAttr});
                            $content.append(view.render().$el);
                        } else {
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
                this.initialized = true;
                console.timeEnd("render tab " + this.model.id);
            }
            $(window.document).trigger("redrawErrorMessages");
        },

        propageShowTab : function propageShowTab() {
            this.model.get("content").propageEvent('showTab');
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        hide : function hide() {
            this.$el.hide();
        },

        show : function show() {
            this.$el.show();
        }
    });

});