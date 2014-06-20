define([
    'underscore',
    'mustache',
    "kendo",
    'widgets/widget'
], function (_, Mustache) {
    'use strict';

    $.widget("dcp.dcpMenu", {

        destroy: function () {
            this.element.empty();
            this._super();
        },

        _create: function () {
            this._initStructure();
        },

        _initStructure: function () {
            console.time("widget menu");
            var $content, $mainElement;
            // this.element.addClass("navbar navbar-default navbar-fixed-top");
            // this.element.attr("role", "navigation");
            $mainElement = $(Mustache.render(this._getTemplate("menu"), _.extend({uuid: this.uuid}, this.options)));
            $content = $mainElement.find(".menu__content");
            this._insertMenuContent(this.options.menus, $content);
            this.element.append($mainElement);
            $content.kendoMenu({openOnClick: false});

            $(window).scroll(function () {
                if ($(window).scrollTop() > $mainElement.position().top) {
                    if (!$mainElement.data("isFixed")) {
                        $mainElement.data("isFixed", "1");
                        $mainElement.parent().addClass("menu--fixed");
                    }
                } else {
                    if ($mainElement.data("isFixed")) {
                        $mainElement.data("isFixed", null);
                        $mainElement.parent().removeClass("menu--fixed");
                    }
                }
            });
        },

        _insertMenuContent: function (menus, $content, currentWidget) {
            var subMenu;
            currentWidget = currentWidget || this;
            _.each(menus, function (currentMenu) {

                var $currentMenu;
                if (currentMenu.visibility == "hidden") {
                    return;
                }
                currentMenu.htmlAttr = [];
                _.each(currentMenu.htmlAttributes, function (attrValue, attrId) {
                    currentMenu.htmlAttr.push({"attrId": attrId, "attrValue": attrValue});
                });

                if (currentMenu.tooltipLabel) {

                }
                currentMenu.disabled = (currentMenu.visibility == 'disabled');
                if (currentMenu.type === "listMenu") {
                    subMenu = "listMenu";

                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu), currentMenu));
                    currentWidget._insertMenuContent(currentMenu.content, $currentMenu.find(".listmenu__content"), currentWidget);
                } else if (currentMenu.type === "dynamicMenu") {
                    subMenu = "dynamicMenu";
                    if (currentMenu.url) {
                        currentMenu.document = currentWidget.options.document;
                        currentMenu.url = Mustache.render(currentMenu.url, currentMenu);
                    }
                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu), currentMenu));

                    $currentMenu.on("click", function () {

                        var menuUrl = $(this).data("menu-url");
                        $.getJSON(menuUrl,function (data) {
                            $currentMenu.find(".listmenu__content").html('');
                            currentWidget._insertMenuContent(
                                data.content,
                                $currentMenu.find(".listmenu__content"),
                                currentWidget);
                            $currentMenu.kendoMenu();
                        }).fail(function (data) {
                            throw new Error("SubMenu");
                        });
                    });
                } else {
                    currentMenu.document = currentWidget.options.document;
                    if (currentMenu.url) {
                        currentMenu.url = Mustache.render(currentMenu.url, currentMenu);
                    }
                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(currentMenu.type), currentMenu));
                }
                if (currentMenu.tooltipLabel) {
                    $currentMenu.kendoTooltip(
                        {
                            autoHide: true,
                            showOnClick: false,
                            callout: true,
                            position: "bottom"
                        });
                }

                $content.append($currentMenu);
            });
        },

        _getTemplate: function (name) {
            if (window.dcp && window.dcp.templates && window.dcp.templates.menu
                && window.dcp.templates.menu[name]) {
                return window.dcp.templates.menu[name];
            }
            throw new Error("Menu unknown template " + name);
        }

    });
});