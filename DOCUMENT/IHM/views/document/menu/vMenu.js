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

        initialize : function () {
            this.listenTo(this.model.get("properties"), 'change', this.updateWidget);
            this.listenTo(this.model.get("menus"), 'change', this.updateWidget);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render : function () {
            this.$el.dcpMenu(this.model.toData());
            return this;
        },

        updateWidget : function() {
            this.$el.dcpMenu("destroy");
            return this.render();
        }
    });

});