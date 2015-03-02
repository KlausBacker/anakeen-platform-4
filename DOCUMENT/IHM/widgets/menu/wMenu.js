define([
    "jquery",
    'underscore',
    'mustache',
    "kendo/kendo.menu",
    "kendo/kendo.window",
    'dcpDocument/widgets/widget'
], function ($, _, Mustache) {
    'use strict';

    $.widget("dcp.dcpMenu", {

        options : {
            eventPrefix : "dcpmenu"
        },

        _create : function wMenuCreate() {
            this._tooltips= [];
            this.popupWindows= [];
            this._initStructure();
        },

        _initStructure : function wMenuInitStructure() {
            var $content, $mainElement, scopeWidget = this;
            //InitDom
            $mainElement = $(Mustache.render(this._getTemplate("menu"), _.extend({uuid : this.uuid}, this.options)));
            $content = $mainElement.find(".menu__content");
            this._insertMenuContent(this.options.menus, $content);
            this.element.append($mainElement);
            //Init kendo widget
            $content.kendoMenu({
                openOnClick :  true,
                closeOnClick : false,
                select :       function wMenuSelect (event) {
                    var menuElement = $(event.item), eventContent, $elementA, href, configMenu, confirmText, confirmOptions,
                        confirmDcpWindow, target, targetOptions, dcpWindow, bodyDiv;

                    if (!menuElement.hasClass("menu__element--item")) {
                        var menuUrl = menuElement.data("menu-url");
                        if (menuUrl) {
                            menuElement.find(".listmenu__content").html('<div class="menu--loading"><i class="fa fa-2x fa-spinner fa-spin"></i> Loading menu.</div>');

                            //get subMenu
                            $.get(menuUrl, function wMenuDone (data) {
                                menuElement.find(".listmenu__content").html('');
                                scopeWidget._insertMenuContent(
                                    data.content,
                                    menuElement.find(".listmenu__content"),
                                    scopeWidget, menuElement);
                                menuElement.kendoMenu({
                                    openOnClick :  true,
                                    closeOnClick : false
                                });
                            }).fail(function wMenuFail (data) {
                                try {
                                    console.error(data);
                                    throw new Error("Sub menu");
                                } catch (e) {
                                    if (window.dcp.logger) {
                                        window.dcp.logger(e);
                                    } else {
                                        console.error(e);
                                    }
                                }
                            });
                        }
                        return;
                    }

                    if (!menuElement.hasClass("menu__element--item")) {
                        return;
                    }
                    $elementA = $(event.item).find('a');
                    href = $elementA.data('url');
                    //noinspection JSHint
                    if (href != '') {
                        //Display confirm message
                        if ($elementA.hasClass("menu--confirm")) {
                            confirmText = Mustache.render($elementA.data('confirm-message'), scopeWidget.options);

                            configMenu = menuElement.data("menuConfiguration");
                            confirmOptions = configMenu.confirmationOptions || {};
                            confirmDcpWindow = $('body').dcpConfirm({
                                title :    Mustache.render(confirmOptions.title, scopeWidget.options),
                                width :    confirmOptions.windowWidth,
                                height :   confirmOptions.windowHeight,
                                messages : {
                                    okMessage :     Mustache.render(confirmOptions.confirmButton, scopeWidget.options),
                                    cancelMessage : Mustache.render(confirmOptions.cancelButton, scopeWidget.options),
                                    htmlMessage :   confirmText,
                                    textMessage :   ''
                                },
                                confirm :  function wMenuConfirm() {
                                    $elementA.removeClass('menu--confirm');
                                    $elementA.trigger("click");
                                    $elementA.addClass('menu--confirm');
                                },
                                templateData : scopeWidget.options
                            });

                            scopeWidget.popupWindows.push(confirmDcpWindow.data('dcpWindow'));

                            confirmDcpWindow.data('dcpWindow').open();
                        } else {
                            //if href is event kind propagate event instead of default behaviour
                            if (href.substring(0, 7) === "#event/") {
                                eventContent = href.substring(7).split(":");
                                scopeWidget._trigger("selected", event, {
                                    eventId : eventContent.shift(),
                                    options : eventContent
                                });
                            } else {
                                target = $elementA.attr("target") || '_self';

                                if (target === "_self") {
                                    window.location.href = href;
                                } else if (target === "_dialog") {
                                    configMenu = menuElement.data("menuConfiguration");
                                    targetOptions = configMenu.targetOptions || {};

                                    bodyDiv = $('<div/>');
                                    $('body').append(bodyDiv);

                                    dcpWindow = bodyDiv.dcpWindow({
                                        title :   Mustache.render(targetOptions.title, window.dcp.documentData),
                                        width :   targetOptions.windowWidth,
                                        height :  targetOptions.windowHeight,
                                        content : href,
                                        iframe :  true
                                    });

                                    scopeWidget.popupWindows.push(dcpWindow.data('dcpWindow'));
                                    dcpWindow.data('dcpWindow').kendoWindow().center();
                                    dcpWindow.data('dcpWindow').open();

                                    _.defer(function () {
                                        dcpWindow.data('dcpWindow').currentWidget.find('iframe').on("load", function () {
                                            dcpWindow.data('dcpWindow').kendoWindow().setOptions({
                                                title : $(this).contents().find("title").html()
                                            });
                                        });
                                    });
                                } else {
                                    window.open(href, target);
                                }
                            }
                        }
                    }
                }
            });

            /**
             * Fix menu when no see header
             */
            $(window).scroll(function wMenuScroll() {
                if ($(window).scrollTop() > $mainElement.position().top) {
                    if (!$mainElement.data("isFixed")) {
                        $mainElement.data("isFixed", "1");
                        $mainElement.parent().addClass("menu--fixed");
                        $(window.document).trigger("redrawErrorMessages");
                    }
                } else {
                    if ($mainElement.data("isFixed")) {
                        $mainElement.data("isFixed", null);
                        $mainElement.parent().removeClass("menu--fixed");
                        $(window.document).trigger("redrawErrorMessages");
                    }
                }
            });
        },

        _insertMenuContent : function wMenuInsertMenuContent(menus, $content, currentWidget, scopeMenu) {
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
                        currentMenu.htmlAttr.push({"attrId" : attrId, "attrValue" : attrValue});
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

                } else {
                    currentMenu.document = currentWidget.options.document;
                    if (currentMenu.url) {
                        currentMenu.url = Mustache.render(currentMenu.url, currentMenu);
                    }
                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(currentMenu.type), currentMenu));
                }
                if (currentMenu.tooltipLabel) {
                    currentWidget._tooltips.push($currentMenu.tooltip(
                        {
                            trigger :   "hover",
                            placement : currentMenu.tooltipPlacement?currentMenu.tooltipPlacement:"bottom"

                        }));

                }
                $currentMenu.data("menuConfiguration", currentMenu);
                $content.append($currentMenu);
            });
        },

        _getTemplate : function wMenuTemplate (name) {
            if (this.options.templates && this.options.templates.menu && this.options.templates.menu[name]) {
                return this.options.templates.menu[name];
            }
            if (window.dcp.templates && window.dcp.templates.menu && window.dcp.templates.menu[name]) {
                return window.dcp.templates.menu[name];
            }
            throw new Error("Menu unknown template " + name);
        },

        _destroy : function wMenuDestroy() {
            var kendoWidget = this.element.find(".menu__content").data("kendoMenu");
            if (kendoWidget) {
                kendoWidget.destroy();
            }

            _.each(this.popupWindows, function (pWindow) {
                pWindow.destroy();
            });

            _.each(this._tooltips, function(currentTooltip) {
                currentTooltip.tooltip("destroy");
            });
            this.element.empty();
            this._super();
        }

    });
});