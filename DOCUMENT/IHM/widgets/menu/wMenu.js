define([
    "jquery",
    'underscore',
    'mustache',
    'dcpDocument/documentCatalog',
    "kendo/kendo.menu",
    "kendo/kendo.window",
    'dcpDocument/widgets/widget'
], function ($, _, Mustache, i18n)
{
    'use strict';

    $.widget("dcp.dcpMenu", {

        options: {
            eventPrefix: "dcpmenu"
        },

        kendoMenuWidget: null,
        _create: function wMenuCreate()
        {
            this._tooltips = [];
            this.popupWindows = [];
            this._initStructure();
        },

        _initStructure: function wMenuInitStructure()
        {
            var $content, $mainElement, scopeWidget = this;
            //InitDom
            $mainElement = $(Mustache.render(this._getTemplate("menu"), _.extend({uuid: this.uuid}, this.options)));
            $content = $mainElement.find(".menu__content");
            this._insertMenuContent(this.options.menus, $content);
            this.element.append($mainElement);
            //Init kendo widget
            $content.kendoMenu({
                openOnClick: true,
                closeOnClick: false,
                select: function wMenuSelect(event)
                {
                    var menuElement = $(event.item), eventContent, $elementA, href, configMenu, confirmText, confirmOptions,
                        confirmDcpWindow, target, targetOptions, dcpWindow, bodyDiv;


                    // Use specific select only for terminal items
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
                                title: Mustache.render(confirmOptions.title, scopeWidget.options),
                                width: confirmOptions.windowWidth,
                                height: confirmOptions.windowHeight,
                                messages: {
                                    okMessage: Mustache.render(confirmOptions.confirmButton, scopeWidget.options),
                                    cancelMessage: Mustache.render(confirmOptions.cancelButton, scopeWidget.options),
                                    htmlMessage: confirmText,
                                    textMessage: ''
                                },
                                confirm: function wMenuConfirm()
                                {
                                    $elementA.removeClass('menu--confirm');
                                    $elementA.trigger("click");
                                    $elementA.addClass('menu--confirm');
                                },
                                templateData: scopeWidget.options
                            });

                            scopeWidget.popupWindows.push(confirmDcpWindow.data('dcpWindow'));

                            confirmDcpWindow.data('dcpWindow').open();
                        } else {
                            //if href is event kind propagate event instead of default behaviour
                            if (href.substring(0, 8) === "#action/") {
                                eventContent = href.substring(8).split(":");
                                scopeWidget._trigger("selected", event, {
                                    eventId: eventContent.shift(),
                                    options: eventContent
                                });
                            } else {
                                target = $elementA.attr("target") || '_self';

                                if (target === "_self") {
                                    window.location.href = href;
                                } else
                                    if (target === "_dialog") {
                                        configMenu = menuElement.data("menuConfiguration");
                                        targetOptions = configMenu.targetOptions || {};

                                        bodyDiv = $('<div/>');
                                        $('body').append(bodyDiv);
                                        dcpWindow = bodyDiv.dcpWindow({
                                            title: Mustache.render(targetOptions.title, window.dcp.documentData),
                                            width: targetOptions.windowWidth,
                                            height: targetOptions.windowHeight,
                                            modal: targetOptions.modal,
                                            content: href,
                                            iframe: true
                                        });

                                        scopeWidget.popupWindows.push(dcpWindow.data('dcpWindow'));
                                        dcpWindow.data('dcpWindow').kendoWindow().center();
                                        dcpWindow.data('dcpWindow').open();


                                    } else {
                                        window.open(href, target);
                                    }
                            }
                        }
                    }
                },
                deactivate: function wMenuDeactivate(event)
                {
                    var menuElement = $(event.item);

                    // Use for reopen for Dynamic menu
                    if (menuElement.data("menu-openAgain")) {
                        menuElement.data("menu-openAgain", false);
                        menuElement.data("menu-noQuery", true);
                        $content.data("kendoMenu").open(menuElement);
                    }
                },
                open: function wMenuOpen(event)
                {

                    var menuElement = $(event.item);

                    // Due to iOs artefact, an resize event is send, so need to inhibated during opening menu
                    scopeWidget.element.data("menu-opening", true);
                    menuElement.data("bodyWidth", $('body').width());

                    if (!menuElement.hasClass("menu__element--item")) {
                        var menuUrl = menuElement.data("menu-url");
                        if (menuUrl) {
                            // Open Dynamic menu : request server to get menu contents
                            if (!menuElement.data("menu-noQuery")) {
                                var loading = menuElement.find(".menu__loading");

                                if (loading.length > 0) {
                                    // record initial loading item
                                    menuElement.data("menu-loading", loading);
                                }

                                // Display loading first
                                if (loading.length === 0 && menuElement.data("menu-loading")) {
                                    menuElement.find(".listmenu__content").html('').append(menuElement.data("menu-loading"));
                                }


                                // Get subMenu
                                $.get(menuUrl, function wMenuDone(data)
                                {
                                    menuElement.find(".listmenu__content").html('');

                                    scopeWidget._insertMenuContent(
                                        data.content,
                                        menuElement.find(".listmenu__content"),
                                        scopeWidget, menuElement);
                                    menuElement.kendoMenu({
                                        openOnClick: true,
                                        closeOnClick: false
                                    });


                                    if (parseInt(menuElement.find(".k-animation-container").css("left")) !== 0 &&
                                        parseInt(menuElement.find(".k-animation-container").css("right")) !== 0
                                    ) {
                                        // Need to close and reopen to adjust position menu because content has changed
                                        menuElement.data("menu-openAgain", true);
                                        $content.data("kendoMenu").close(menuElement);
                                    }


                                }).fail(function wMenuFail(data)
                                {
                                    try {
                                        var errorMessage=data.responseText;
                                        menuElement.find(".listmenu__content").html($('<div/>').text(errorMessage).addClass("menu--error"));
                                    } catch (e) {
                                        if (window.dcp.logger) {
                                            window.dcp.logger(e);
                                        } else {
                                            console.error(e);
                                        }
                                    }
                                });
                            }
                            menuElement.data("menu-noQuery", false);
                        }
                    }
                },
                activate: function wMenuActivate(event)
                {
                    // Correct Kendo position list when scrollbar is displayed
                    var $menuElement = $(event.item);
                    var $container = $menuElement.find(".k-animation-container");

                    var bodyWidth = $menuElement.data("bodyWidth");
                    var menuWidth = $menuElement.outerWidth();
                    var menuLeft = $menuElement.offset().left;
                    var listWidth = $container.outerWidth();
                    var listLeft = $container.offset().left;


                    // The first condition is for iOS because no scroll window exists
                    if (($("body").width() > bodyWidth  ) || (window.document.documentElement.scrollHeight > window.document.documentElement.clientHeight)) {

                        // If the list menu is out of the body box, need to move it to the right
                        if ((listLeft + listWidth) > bodyWidth) {
                            $container.css("left", "auto").css("right", (menuLeft - bodyWidth + menuWidth  ) + "px");
                        }

                    }

                    _.delay(function ()
                    {
                        // Due to iOs artefact, an resize event is send, so need to inhibated during opening menu
                        scopeWidget.element.data("menu-opening", false);
                    }, 2000);
                }
            });

            /**
             * Fix menu when no see header
             */
            $(window).off("scroll.ddui"); // reset
            $(window).on("scroll.ddui", function wMenuScroll()
            {
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
            /**
             * Responsive Menu
             */
            this.kendoMenuWidget = $content.data("kendoMenu");
            this.kendoMenuWidget.append([
                {
                    text: i18n.___("Other", "UImenu")+'<span class="menu--count" />',
                    cssClass: "menu__element  menu_element--hamburger ",
                    encoded: false,   // Allows use of HTML for item text
                    items: []         // List items
                }]);
            $(window).on("resize.dcpMenu", _.bind(this.inhibitBarMenu, this));
            $(window).on("resize.dcpMenu", _.debounce(_.bind(this.updateResponsiveMenu, this), 100, false));
            _.delay(_.bind(this.updateResponsiveMenu, this), 100);


        },

        inhibitBarMenu: function ()
        {
            var widgetMenu = this;
            if (!widgetMenu.element.data("menu-opening") && this.element.css("overflow") !== "hidden") {
                this.element.css("max-height", this.element.find(".menu__content li").height() + 2).css("overflow", "hidden");
                this.element.find("li.k-state-border-down").each(function ()
                {
                    widgetMenu.kendoMenuWidget.close($(this));
                });
            }
        },

        /**
         * Get scrollbar width by adding a element
         * @returns {number|*}
         */
        getScrollBarWidth: function wMenugetScrollBarWidth()
        {
            if (!this.scrollBarWidth) {
                var inner = document.createElement('p');
                inner.style.width = "100%";
                inner.style.height = "200px";

                var outer = document.createElement('div');
                outer.style.position = "absolute";
                outer.style.top = "0px";
                outer.style.left = "0px";
                outer.style.visibility = "hidden";
                outer.style.width = "200px";
                outer.style.height = "150px";
                outer.style.overflow = "hidden";
                outer.appendChild(inner);

                document.body.appendChild(outer);
                var w1 = inner.offsetWidth;
                outer.style.overflow = 'scroll';
                var w2 = inner.offsetWidth;
                if (w1 === w2) {
                    w2 = outer.clientWidth;
                }

                document.body.removeChild(outer);
                this.scrollBarWidth = (w1 - w2);
            }

            return this.scrollBarWidth;
        },

        /**
         * Move menu to hamburger which can be displayed in same line menu
         */
        updateResponsiveMenu: function wMenuHideResponsiveMenu()
        {
            var barMenu = this.element;
            var $itemMenu = barMenu.find("ul.k-menu > .menu__element:not(.menu--important,.menu_element--hamburger)");
            var $importantItemMenu = barMenu.find("ul.k-menu > .menu__element.menu--important");
            var newHiddens = [];
            var currentWidth = 0;
            var visibleWidth = 0;
            var freeWidth = 0;
            var barmenuWidth = barMenu.width() - 2;
            var kendoMenu = this.kendoMenuWidget;
            var $hamburger = $(".menu_element--hamburger");
            var hiddenItemsCount;
            var visibleItemCount;
            var $hiddenItems = $($hamburger.find("ul").get(0)).find("> li.k-item");
            var hiddenLeft = $hiddenItems.length;


            if (barMenu.data("menu-opening")) {
                return;
            }
            this.inhibitBarMenu();
            $importantItemMenu.each(function wMenuComputeBarmenuWidth()
            {
                barmenuWidth -= $(this).outerWidth();
            });

            barmenuWidth -= $hamburger.outerWidth();

            // When no scrollbar need to add hypothetic scrollbar width because no event to refresh when scrollbar appear
            if (window.document.documentElement.scrollHeight <= window.document.documentElement.clientHeight) {
                barmenuWidth -= this.getScrollBarWidth(); // Supposed that scrollbar width is max 20px
            }

            visibleItemCount = $itemMenu.length;
            // Detect free menu available width  and record menu items which not contains to bar menu
            $itemMenu.each(function wMenuComputeWidth()
            {
                currentWidth += ($(this).outerWidth());
                if (currentWidth > barmenuWidth) {
                    $(this).data("original-width", $(this).outerWidth());
                    newHiddens.push(this);
                } else {
                    visibleWidth += $(this).outerWidth();
                }
                visibleItemCount--;

            });

            freeWidth = barmenuWidth - visibleWidth;

            if (hiddenLeft === 0 && newHiddens.length === 1) {
                // Special case for the last hidden may visible if hamburger is hide
                if ($(newHiddens[0]).outerWidth() < (freeWidth + $hamburger.outerWidth())) {
                    newHiddens = [];
                }
            }


            // Move each new hidden menu to hamburger
            _.each(newHiddens.reverse(), function wMenuItemToHamburger(item)
            {
                // Prepend new menu to hamburger
                if ($hamburger.find("li.k-item").length === 0) {
                    kendoMenu.append($(item), $hamburger);
                } else {
                    kendoMenu.insertBefore($(item), $($hamburger.find("li.k-item").get(0)));
                }
            });
            // No new hidden menu so ...
            if (newHiddens.length === 0) {
                // May be show hidden menu
                $hiddenItems = $($hamburger.find("ul").get(0)).find("> li.k-item");
                hiddenLeft = $hiddenItems.length;
                $hiddenItems.each(function wMenuItemFromHamburger()
                {
                    if (freeWidth > 0) {
                        if (hiddenLeft === 1) {
                            freeWidth += $hamburger.width();
                        }
                        currentWidth = $(this).data("original-width");

                        // If available width show move at initial place (right of the hamburger)
                        if (currentWidth < freeWidth) {
                            kendoMenu.insertBefore($(this), $hamburger);
                            freeWidth -= $(this).outerWidth();
                        } else {
                            freeWidth = -1; // stop test
                        }
                    }
                });
            }

            // Number of items in hamburger
            hiddenItemsCount = $($hamburger.find("ul").get(0)).find("> li.k-item").length;

            // No view hamburger if empty
            if (hiddenItemsCount === 0) {
                $hamburger.hide();
            }

            // See sub-menu count
            // $hamburger.find(".menu--count").text(hiddenItemsCount);

            // View hamburger if not empty
            if (newHiddens.length > 0) {
                $hamburger.show();
            }

            // Restore css set by other resize callback
            barMenu.css("overflow", "").css("max-height", "");
        },

        _insertMenuContent: function wMenuInsertMenuContent(menus, $content, currentWidget, scopeMenu)
        {
            var subMenu;
            var hasBeforeContent = false;
            currentWidget = currentWidget || this;

            if (scopeMenu) {
                // Add fake before content if at least one element has before content to align all items
                _.each(menus, function (currentMenu)
                {
                    if (currentMenu.iconUrl || currentMenu.beforeContent) {
                        hasBeforeContent = true;
                    }
                });
                if (hasBeforeContent) {
                    _.each(menus, function (currentMenu)
                    {
                        if (!currentMenu.iconUrl && !currentMenu.beforeContent) {
                            if (currentMenu.type !== "separatorMenu") {
                                currentMenu.beforeContent = ' ';
                            }
                        }
                    });
                }
            }

            _.each(menus, function (currentMenu)
            {
                var $currentMenu;
                if (currentMenu.visibility === "hidden") {
                    return;
                }
                currentMenu.htmlAttr = [];
                _.each(currentMenu.htmlAttributes, function (attrValue, attrId)
                {
                    if (attrId === "class") {
                        currentMenu.cssClass = attrValue;
                    } else {
                        currentMenu.htmlAttr.push({"attrId": attrId, "attrValue": attrValue});
                    }
                    if (currentMenu.htmlLabel) {
                        // reRender for variable labels
                        currentMenu.htmlLabel = Mustache.render(currentMenu.htmlLabel, {document: currentWidget.options.document});
                    }
                    if (currentMenu.label) {
                        // reRender for variable labels
                        currentMenu.label = Mustache.render(currentMenu.label, {document: currentWidget.options.document});
                    }
                    if (currentMenu.tooltipLabel) {
                        // reRender for variable labels
                        currentMenu.tooltipLabel = Mustache.render(currentMenu.tooltipLabel, {document: currentWidget.options.document});
                    }
                });

                currentMenu.contentLabel=(currentMenu.htmlLabel || currentMenu.label);
                currentMenu.disabled = (currentMenu.visibility === 'disabled');
                if (currentMenu.type === "listMenu") {
                    subMenu = "listMenu";

                    $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu), currentMenu));
                    currentWidget._insertMenuContent(currentMenu.content, $currentMenu.find(".listmenu__content"), currentWidget, currentMenu);
                } else {
                    if (currentMenu.type === "dynamicMenu") {
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
                }
                if (currentMenu.tooltipLabel) {
                    currentWidget._tooltips.push($currentMenu.tooltip(
                        {
                            trigger: "hover",
                            html: currentMenu.tooltipHtml,
                            placement: currentMenu.tooltipPlacement ? currentMenu.tooltipPlacement : "bottom",
                            container: ".dcpDocument__menu"
                        }));
                }
                if (currentMenu.important) {
                    $currentMenu.addClass("menu--important");
                }
                $currentMenu.data("menuConfiguration", currentMenu);
                $content.append($currentMenu);
            });
        },

        _getTemplate: function wMenuTemplate(name)
        {
            if (this.options.templates && this.options.templates.menu && this.options.templates.menu[name]) {
                return this.options.templates.menu[name];
            }
            if (window.dcp.templates && window.dcp.templates.menu && window.dcp.templates.menu[name]) {
                return window.dcp.templates.menu[name];
            }
            throw new Error("Menu unknown template " + name);
        },

        _destroy: function wMenuDestroy()
        {
            var kendoWidget = this.element.find(".menu__content").data("kendoMenu");
            if (kendoWidget) {
                kendoWidget.destroy();
            }
            $(window).off(".dcpMenu");
            _.each(this.popupWindows, function (pWindow)
            {
                pWindow.destroy();
            });

            _.each(this._tooltips, function (currentTooltip)
            {
                currentTooltip.tooltip("destroy");
            });
            this.element.empty();
            this._super();
        }

    });
});