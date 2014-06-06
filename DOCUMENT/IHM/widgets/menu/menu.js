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
            var $content, $mainElement;
            this.element.addClass("navbar navbar-default navbar-fixed-top");
            this.element.attr("role", "navigation");
            $mainElement = $(Mustache.render(this._getTemplate("menu"), _.extend({uuid : this.uuid}, this.options)));
            $content = $mainElement.find(".menu__content");
            this._insertMenuContent(this.options.menus, $content);
            this.element.append($mainElement);
        },

        _insertMenuContent : function(menus, $content, currentWidget, secondLevel) {
            var subMenu;
            currentWidget = currentWidget || this;
            _.each(menus, function (currentMenu) {
                var $currentMenu;
                if (currentMenu.type === "listMenu") {
                    subMenu = "dropdownmenu";
                    if (secondLevel) {
                        subMenu = "dropdownsubmenu";
                    }
                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu), currentMenu));
                    currentWidget._insertMenuContent(currentMenu.content, $currentMenu.find(".dropdown-menu"), currentWidget, true);
                } else {
                    currentMenu.document = currentWidget.options.document;
                    if (currentMenu.url) {
                        currentMenu.url = Mustache.render(currentMenu.url, currentMenu);
                    }
                    $currentMenu = Mustache.render(currentWidget._getTemplate("element"), currentMenu);
                }
                $content.append($currentMenu);
            });
        },

        _getTemplate : function(name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.menu
                && window.dcp.templates.menu[name]) {
                return window.dcp.templates.menu[name];
            }
            throw new Error("Menu unknown template "+name);
        }

    });
});