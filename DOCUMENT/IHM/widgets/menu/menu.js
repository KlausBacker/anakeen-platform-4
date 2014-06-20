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

            /**
             * Fix menu when no see header
             */
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


            $mainElement.on("click", ".menu__item a", function (event) {
                event.stopPropagation();
                var href=$(this).data('url');
                //noinspection JSHint
                if (href != '') {
                    var target=$(this).attr("target") || '_self';
                    console.log("target",target, $(this) );

                    if (target === "_self") {
                        window.location.href=href;
                    } else if (target === "_dialog") {
                        console.log("open in dailog",target, $(this) );
                        var dw=$("<div/>");
                        $('body').append(dw);
                        dw.dcpWindow({
                            content:href,
                            iframe: true
                        });
                        _.delay(function() {
                            console.log('DELAY', dw.find('iframe'));
                        },1000);
                        _.defer(function() {
                            console.log('DEFER', dw.find('iframe'));
                            dw.find('iframe').on("load", function () {
                            console.log("IFRAME2 LOADED");
                                dw.data("kendoWindow").setOptions({
                                   title:$(this).contents().find( "title").html()
                                });
                           });
                        });
                        console.log('DIRECT',dw.find('iframe'));
                        dw.on("load", "iframe", function () {
                            console.log("IFRAME LOADED");
                        });
                        dw.data("kendoWindow").center();
                    } else  {
                        window.open(href, target);
                    }
                }
            });
            $mainElement.on("click", ".menu--confirm", function (event) {
                event.stopPropagation();
                var confirmText=$(this).data('confirm');
                var $scope=$(this);
                $('body').dcpConfirm({
                    messages : {
                      textMessage:confirmText
                    },
                    confirm : function () {
                        //$scope.removeClass('menu--confirm');
                        $scope.trigger("click");
                    }
                });
            });
        },

        _insertMenuContent: function (menus, $content, currentWidget) {
            var subMenu;
            currentWidget = currentWidget || this;
            _.each(menus, function (currentMenu) {

                var $currentMenu;
                if (currentMenu.visibility === "hidden") {
                    return;
                }
                currentMenu.htmlAttr = [];
                _.each(currentMenu.htmlAttributes, function (attrValue, attrId) {
                    currentMenu.htmlAttr.push({"attrId": attrId, "attrValue": attrValue});
                });


                currentMenu.disabled = (currentMenu.visibility === 'disabled');
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
            if (window.dcp && window.dcp.templates && window.dcp.templates.menu && window.dcp.templates.menu[name]) {
                return window.dcp.templates.menu[name];
            }
            throw new Error("Menu unknown template " + name);
        }

    });
});