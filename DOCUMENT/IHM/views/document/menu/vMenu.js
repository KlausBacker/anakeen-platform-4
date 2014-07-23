/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'widgets/menu/wMenu'
], function (_, Backbone, Mustache, WidgetMenu) {
    'use strict';

    return Backbone.View.extend({

        className : "dcpDocument",

        /**
         * The current model is the document model
         * So menuModel reference the menu model
         */
        menuModel : null,

        initialize : function () {
            this.listenTo(this.model.get("properties"), 'change', this.updateWidget);
            this.listenTo(this.model.get("menus"), 'change', this.updateWidget);
            this.listenTo(this.model.get("attributes"), 'changeMenuVisibility', this.changeVisibility);
            this.listenTo(this.model, 'destroy', this.remove);
            this.menuModel=this.model.get("menus");
        },

        render : function () {
            this.$el.dcpMenu(this.model.toData());
            return this;
        },

        changeVisibility : function changeVisibility(event, data) {
            var menuItem=this.menuModel.get(data.id);
            if (menuItem) {
                menuItem.set("visibility",data.visibility);
            }
        },


        updateWidget : function() {
            this.$el.dcpMenu("destroy");
            return this.render();
        }
    });

});