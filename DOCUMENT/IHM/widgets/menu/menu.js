define([
    'underscore',
    'mustache',
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpMenu", {

        destroy : function() {
            this.element.empty();
            this._super();
        },

        _create : function() {
            this._initStructure();
        },

        _initStructure : function() {
            console.time("widget menu");
            var $content, getTemplate = this._getTemplate, doc = this.options.document;
            this.element.addClass("navbar navbar-default navbar-fixed-top");
            this.element.attr("role", "navigation");
            $content = $(Mustache.render(this._getTemplate("menu"), _.extend({uuid : this.uuid}, this.options)));
            _.each(this.options.menus, function(currentMenu) {
                var $currentMenu;
                currentMenu.document = doc;
                if (currentMenu.url) {
                    currentMenu.url = Mustache.render(currentMenu.url, currentMenu);
                }
                $currentMenu = Mustache.render(getTemplate("menu__element"), currentMenu);
                $content.find(".menu__content").append($currentMenu);
            });
            this.element.append($content);
        },

        _getTemplate : function(name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.menu
                && window.dcp.templates.menu[name]) {
                return window.dcp.templates.menu[name];
            }
            throw "Menu unknown template "+name;
        }

    });
});