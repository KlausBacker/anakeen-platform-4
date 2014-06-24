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
            $content.kendoMenu({
                openOnClick: true,
                closeOnClick: false
            });

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


            $mainElement.on("click", ".menu__element--item a", function (event) {
                event.stopPropagation();
                var href = $(this).data('url');
                //noinspection JSHint
                if (href != '') {
                    if ($(this).hasClass("menu--confirm")) {
                        return;
                    }
                    var target = $(this).attr("target") || '_self';

                    if (target === "_self") {
                        window.location.href = href;
                    } else if (target === "_dialog") {
                        var configMenu = $(this).parent().data("menuConfiguration");
                        var targetOptions = configMenu.targetOptions || {};

                        var bdw=$('<div/>');
                        $('body').append(bdw);

                        var dw=bdw.dcpWindow({
                            title: Mustache.render(targetOptions.title, window.dcp.documentData),
                            width: targetOptions.windowWidth,
                            height: targetOptions.windowHeight,
                            content: href,
                            iframe: true
                        });




                        dw.data('dcpWindow').kendoWindow().center();
                        dw.data('dcpWindow').open();


                        _.defer(function () {
                            dw.data('dcpWindow').currentWidget.find('iframe').on("load", function () {
                                dw.data('dcpWindow').kendoWindow().setOptions({
                                    title: $(this).contents().find("title").html()
                                });
                            });
                        });

                    } else {
                        window.open(href, target);
                    }
                }
            });
            $mainElement.on("click", ".menu--confirm", function (event) {
                event.stopPropagation();
                var confirmText = $(this).data('confirm-message');
                var $scope = $(this);
                var configMenu = $(this).parent().data("menuConfiguration");
                var confirmOptions = configMenu.confirmationOptions || {};

                var dwConfirm=$('body').dcpConfirm({
                    title: Mustache.render(confirmOptions.title, window.dcp.documentData),
                    width: confirmOptions.windowWidth,
                    height: confirmOptions.windowHeight,
                    messages: {
                        okMessage: Mustache.render(confirmOptions.confirmButton, window.dcp.documentData),
                        cancelMessage: Mustache.render(confirmOptions.cancelButton, window.dcp.documentData),
                        textMessage: confirmText
                    },
                    confirm: function () {
                        $scope.removeClass('menu--confirm');
                        $scope.trigger("click");
                        $scope.addClass('menu--confirm');
                    }
                });
                dwConfirm.data('dcpWindow').open();
            });
        },

        _insertMenuContent: function (menus, $content, currentWidget, scopeMenu) {
            var subMenu;
            var hasBeforeContent = false;
            currentWidget = currentWidget || this;

            if (scopeMenu) {
                // Add fake before content if at least one element has before content to align all items
                _.each(menus, function (currentMenu) {
                    if (currentMenu.iconUrl || currentMenu.beforeContent) {
                        hasBeforeContent = true;
                    }
                });
                if (hasBeforeContent) {
                    _.each(menus, function (currentMenu) {

                        if (!currentMenu.iconUrl && !currentMenu.beforeContent) {
                            if (currentMenu.type !== "separatorMenu") {
                                currentMenu.beforeContent = ' ';
                            }
                        }
                    });
                }
            }

            _.each(menus, function (currentMenu) {

                var $currentMenu;
                if (currentMenu.visibility === "hidden") {
                    return;
                }
                currentMenu.htmlAttr = [];
                _.each(currentMenu.htmlAttributes, function (attrValue, attrId) {
                    if (attrId === "class") {
                        currentMenu.cssClass = attrValue;
                    } else {
                        currentMenu.htmlAttr.push({"attrId": attrId, "attrValue": attrValue});
                    }

                });


                currentMenu.disabled = (currentMenu.visibility === 'disabled');
                if (currentMenu.type === "listMenu") {
                    subMenu = "listMenu";

                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu), currentMenu));
                    currentWidget._insertMenuContent(currentMenu.content, $currentMenu.find(".listmenu__content"), currentWidget, currentMenu);
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
                                currentWidget, currentMenu);
                            $currentMenu.kendoMenu({
                                openOnClick: true,
                                closeOnClick: false
                            });


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
                $currentMenu.data("menuConfiguration", currentMenu);
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