/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone)
{
    'use strict';

    return Backbone.View.extend({

        tagName: "li",

        className: "dcpTab__label dcpLabel",

        initialize: function vTabLabel_initialize()
        {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model.get("content"), 'add', this.render);
            this.listenTo(this.model.get("content"), 'remove', this.render);
            this.listenTo(this.model.get("content"), 'reset', this.render);
            this.listenTo(this.model, 'errorMessage', this.setError);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
        },

        render: function vTabLabel_render()
        {
            //console.time("render tab " + this.model.id);
            var label = this.model.get("label");
            this.$el.empty();
            this.$el.text(label);
            this.$el.attr("data-attrid", this.model.id);
            this.$el.tooltip({
                placement: "top",
                container : "body",
                title: function vDocumentTooltipTitle()
                {
                    return label; // set the element text as content of the tooltip
                }
            });
            //console.timeEnd("render tab " + this.model.id);
            return this;
        },

        setError: function vTabLabel_setError(event, data)
        {
            if (data) {
                this.$el.addClass("has-error");
            } else {
                this.$el.removeClass("has-error");
            }
        },

        updateLabel: function vTabLabel_updateLabel()
        {
            this.$el.text(this.model.get("label"));
        },

        hide: function vTabLabel_hide()
        {
            this.$el.hide();
        },

        show: function vTabLabel_show()
        {
            this.$el.show();
        }
    });

});