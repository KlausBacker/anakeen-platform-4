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

        events : {
            "dcpmenuselected" : "propagateSelected"
        },

        /**
         * The current model is the document model
         * So menuModel reference the menu model
         */
        menuModel : null,

        initialize : function vMenuInitialize() {
            this.listenTo(this.model.get("properties"), 'change', this.updateWidget);
            this.listenTo(this.model.get("menus"), 'change', this.updateWidget);
            this.listenTo(this.model.get("attributes"), 'changeMenuVisibility', this.changeVisibility);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);

            this.menuModel = this.model.get("menus");
        },

        render : function vMenuRender() {
            this.$el.dcpMenu(this.model.toData());
            return this;
        },

        propagateSelected : function vMenuPropagateSelected(event, options) {
            this.trigger(options.eventId, {target : event.target, options : options.options});
        },

        changeVisibility : function vMenuchangeVisibility(event, data) {
            var menuItem = this.menuModel.get(data.id);
            if (menuItem) {
                menuItem.set("visibility", data.visibility);
            }
        },

        updateWidget : function vMenuUpdateWidget() {
            this.$el.dcpMenu("destroy");
            return this.render();
        },

        remove : function vMenuRemove() {
            if (this.$el.dcpMenu && this._findWidgetName(this.$el)) {
                this.$el.dcpMenu("destroy");
            }
            return Backbone.View.prototype.remove.call(this);
        },

        _findWidgetName : function vMenu_findWidgetName($element) {
            return _.find(_.keys($element.data()), function (currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        }

    });

});