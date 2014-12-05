/*global define*/
define([
    'underscore',
    'backbone',
    'views/attributes/frame/vFrame'
], function (_, Backbone, ViewAttributeFrame) {
    'use strict';

    return Backbone.View.extend({

        tagName : "div",

        className : "dcpDocument__tabs--pane",

        initialize : function (options) {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'showTab', this.renderContent);
            this.listenTo(this.model, 'showTab', this.propageShowTab);
            this.initializeContent = options.initializeContent;
            this.initialized = false;
        },

        render : function () {
            this.$el.empty();
            this.$el.attr("id", this.model.id);
            this.$el.append('<p> Loading : <i class="fa fa-spinner fa-spin"></i></p>');

            var hasOneContent = this.model.get("content").some(function (value) {
                return value.isDisplayable();
            });

            if (!hasOneContent || !this.initializeContent) {
                this.$el.append(this.model.getOption('showEmptyContent'));
            } else {
                this.renderContent();
            }
            this.trigger("renderDone");
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
                        window.TraceKit.report(e);
                        console.error(e);
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
        }
    });

});