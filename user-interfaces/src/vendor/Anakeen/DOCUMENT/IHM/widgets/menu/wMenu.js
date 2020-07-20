import $ from "jquery";
import _ from "underscore";
import Mustache from "mustache";
import i18n from "../../i18n/documentCatalog";
import "../widget";

export default $.widget("dcp.dcpMenu", {
  options: {
    eventPrefix: "dcpmenu"
  },
  kendoMenuWidget: null,
  isTooltip: false,
  _create: function wMenuCreate() {
    this.menuUID = _.uniqueId("menuWidget");
    this._tooltips = [];
    this.popupWindows = [];
    if (this.options.menus && this.options.menus.length > 0) {
      this._initStructure();
    }
  },

  _initStructure: function wMenuInitStructure() {
    var $content,
      $mainElement,
      scopeWidget = this;
    //InitDom
    $mainElement = $(Mustache.render(this._getTemplate("menu") || "", _.extend({ uuid: this.uuid }, this.options)));
    $content = $mainElement.find(".menu__content");
    this._insertMenuContent(this.options.menus, $content);
    this.element.append($mainElement);
    //Init kendo widget
    $content.kendoMenu({
      openOnClick: {
        rootMenuItems: true
      },
      closeOnClick: true,

      select: function wMenuSelect(event) {
        var $menuElement = $(event.item),
          eventContent,
          $elementA,
          href,
          configMenu,
          confirmText,
          confirmOptions,
          confirmDcpWindow,
          target,
          targetOptions,
          dcpWindow,
          $bodyDiv,
          wFeature = "";

        if ($menuElement.hasClass("menu__element--callable")) {
          scopeWidget.callMenu($menuElement);
          return;
        }

        // Use specific select only for terminal items
        if (!$menuElement.hasClass("menu__element--item")) {
          return;
        }
        $elementA = $(event.item).find("a");
        href = $elementA.data("url");
        //noinspection JSHint
        if (href != "") {
          //Display confirm message
          if ($elementA.hasClass("menu--confirm")) {
            confirmText = Mustache.render($elementA.data("confirm-message") || "", scopeWidget.options);

            configMenu = $menuElement.data("menuConfiguration");
            confirmOptions = configMenu.confirmationOptions || {};
            confirmDcpWindow = $("body").dcpConfirm({
              title: Mustache.render(confirmOptions.title || "", scopeWidget.options),
              width: confirmOptions.windowWidth,
              height: confirmOptions.windowHeight,
              messages: {
                okMessage: Mustache.render(confirmOptions.confirmButton || "", scopeWidget.options),
                cancelMessage: Mustache.render(confirmOptions.cancelButton || "", scopeWidget.options),
                htmlMessage: confirmText,
                textMessage: ""
              },
              confirm: function wMenuConfirm() {
                $elementA.removeClass("menu--confirm");
                $elementA.trigger("click");
                $elementA.addClass("menu--confirm");
              },
              templateData: scopeWidget.options
            });

            scopeWidget.popupWindows.push(confirmDcpWindow.data("dcpWindow"));

            confirmDcpWindow.data("dcpWindow").open();
          } else {
            scopeWidget.element.data("menu-opening", false);
            //if href is event kind propagate event instead of default behaviour
            if (href.substring(0, 8) === "#action/") {
              event.preventDefault();
              if (event.stopPropagation) {
                event.stopPropagation();
              }
              eventContent = href.substring(8).split(":");
              scopeWidget._trigger("externalLinkSelected", event, {
                target: event.target || event.item,
                eventId: eventContent.shift(),
                options: eventContent
              });
            } else {
              target = $elementA.attr("target") || "_self";

              if (target === "_self") {
                window.location.href = href;
              } else {
                configMenu = $menuElement.data("menuConfiguration");
                targetOptions = configMenu.targetOptions || {};
                if (target === "_dialog") {
                  $bodyDiv = $("<div/>");
                  $("body").append($bodyDiv);
                  dcpWindow = $bodyDiv.dcpWindow({
                    title: Mustache.render(targetOptions.title || "", window.dcp.documentData),
                    width: targetOptions.windowWidth,
                    height: targetOptions.windowHeight,
                    modal: targetOptions.modal,
                    content: href,
                    iframe: true,
                    close: function() {
                      this.destroy();
                    }
                  });

                  scopeWidget.popupWindows.push(dcpWindow.data("dcpWindow"));
                  dcpWindow
                    .data("dcpWindow")
                    .kendoWindow()
                    .center();
                  dcpWindow.data("dcpWindow").open();
                } else {
                  if (targetOptions && (targetOptions.windowWidth || targetOptions.windowHeight)) {
                    if (targetOptions.windowWidth) {
                      wFeature += "width=" + parseInt(targetOptions.windowWidth, 10) + ",";
                    }
                    if (targetOptions.windowHeight) {
                      wFeature += "height=" + parseInt(targetOptions.windowHeight, 10) + ",";
                    }
                    wFeature += "resizable=yes,scrollbars=yes";
                  }
                  window.open(href, target, wFeature);
                }
              }
            }
          }
        }
      },
      deactivate: function wMenuDeactivate(event) {
        var $menuElement = $(event.item);

        // Use for reopen for Dynamic menu
        if ($menuElement.data("menu-openAgain")) {
          $menuElement.data("menu-openAgain", false);
          $menuElement.data("menu-noQuery", true);
          $content.data("kendoMenu").open($menuElement);
        }
      },
      open: function wMenuOpen(event) {
        var $menuElement = $(event.item);
        var $menu = $menuElement.closest("nav.dcpDocument__menu");

        // Due to iOs artefact, an resize event is send, so need to inhibated during opening menu
        scopeWidget.element.data("menu-opening", true);
        $menuElement.data("bodyWidth", $menu.width());

        if (!$menuElement.hasClass("menu__element--item")) {
          var menuUrl = $menuElement.data("menu-url");
          if (menuUrl) {
            // Open Dynamic menu : request server to get menu contents
            if (!$menuElement.data("menu-noQuery")) {
              var loading = $menuElement.find(".menu__loading");

              if (loading.length > 0) {
                // record initial loading item
                $menuElement.data("menu-loading", loading);
              }

              // Display loading first
              if (loading.length === 0 && $menuElement.data("menu-loading")) {
                $menuElement
                  .find(".listmenu__content")
                  .html("")
                  .append($menuElement.data("menu-loading"));
              }

              // Get subMenu
              $.get(menuUrl, function wMenuDone(response) {
                var data = response.data;
                $menuElement.find(".listmenu__content").html("");

                scopeWidget._insertMenuContent(
                  data.content,
                  $menuElement.find(".listmenu__content"),
                  scopeWidget,
                  $menuElement
                );
                $content.kendoMenu({
                  openOnClick: {
                    rootMenuItems: true
                  },
                  closeOnClick: true
                });

                if (
                  parseInt($menuElement.find(".k-animation-container").css("left")) !== 0 &&
                  parseInt($menuElement.find(".k-animation-container").css("right")) !== 0
                ) {
                  // Need to close and reopen to adjust position menu because content has changed
                  $menuElement.data("menu-openAgain", true);
                  $content.data("kendoMenu").close($menuElement);
                }
              }).fail(function wMenuFail(data) {
                try {
                  var errorMessage = data.responseText;
                  $menuElement.find(".listmenu__content").html(
                    $("<div/>")
                      .text(errorMessage)
                      .addClass("menu--error")
                  );
                } catch (e) {
                  if (window.dcp.logger) {
                    window.dcp.logger(e);
                  } else {
                    console.error(e);
                  }
                }
              });
            }
            $menuElement.data("menu-noQuery", false);
          }
        }
      },
      activate: function wMenuActivate(event) {
        // Correct Kendo position list when scrollbar is displayed
        var $menuElement = $(event.item);
        var $menu = $menuElement.closest("nav.dcpDocument__menu");
        var $container = $menuElement.find(".k-animation-container");

        var bodyWidth = $menuElement.data("bodyWidth");
        var menuWidth = $menuElement.outerWidth();
        var menuLeft = $menuElement.offset().left;
        var listWidth = $container.outerWidth();
        var listLeft = $container.offset().left;
        // The first condition is for iOS because no scroll window exists
        if (
          $menu.width() > bodyWidth ||
          window.document.documentElement.scrollHeight > window.document.documentElement.clientHeight
        ) {
          // If the list menu is out of the body box, need to move it to the right
          if (listLeft + listWidth > bodyWidth) {
            $container.css("left", "auto").css("right", menuLeft - bodyWidth + menuWidth + "px");
          }
        }
        var $menuElementList = $(".menu__content[data-role='menu']")[0].children;
        if ($menuElementList[$menuElementList.length - 2] === $menuElement[0]) {
          if (bodyWidth - listWidth < menuLeft) {
            $container.css("right", 0);
            $container.css("left", "auto");
          }
        }
        $container.css("width", "auto");
        $container.find("ul").css("position", "relative");
        _.delay(function wMenuOpenDelay() {
          // Due to iOs artefact, an resize event is send, so need to inhibated during opening menu
          scopeWidget.element.data("menu-opening", false);
        }, 2000);
      }
    });
    $content.data("kendoMenu").bind("close", function(e) {
      if (window.isTooltip) {
        e.preventDefault();
      }
    });
    $content.on("mouseover", e => {
      window.isTooltip =
        $(e.relatedTarget).hasClass("tooltip-inner") ||
        $(e.relatedTarget).hasClass("tooltip fade") ||
        $(e.relatedTarget).hasClass("menu__content");
    });
    this.element
      .find(".menu--left")
      .last()
      .addClass("menu--lastLeft");
    /**
     * Fix menu when no see header
     */
    const wrapper = this.element.closest("[data-controller]");
    wrapper.off("scroll.dcpMenu" + this.menuUID); // reset
    if (this.element.prop("nodeName").toUpperCase() === "NAV") {
      wrapper.on("scroll.dcpMenu" + this.menuUID, function wMenuScroll() {
        if (wrapper.scrollTop() > $mainElement.position().top) {
          if (!$mainElement.data("isFixed")) {
            $mainElement.data("isFixed", "1");
            $mainElement.parent().addClass("menu--fixed");
            scopeWidget._trigger("redrawErrorMessages");
          }
        } else {
          if ($mainElement.data("isFixed")) {
            $mainElement.data("isFixed", null);
            $mainElement.parent().removeClass("menu--fixed");
            scopeWidget._trigger("redrawErrorMessages");
          }
        }
      });
    }
    /**
     * Responsive Menu
     */
    this.kendoMenuWidget = $content.data("kendoMenu");
    this.kendoMenuWidget.append([
      {
        text: i18n.___("Other", "UImenu") + '<span class="menu--count" />',
        cssClass: "menu__element  menu_element--hamburger ",
        encoded: false, // Allows use of HTML for item text
        items: [] // List items
      }
    ]);
  },
  callMenu: function callMenu($menuItem) {
    var scopeWidget = this;
    var $elementA = $menuItem.find("a");

    $.ajax({
      dataType: "json",
      url: $elementA.data("url"),
      method: $elementA.data("method")
    })
      .then(function(data) {
        _.each(data.messages, function(msg) {
          scopeWidget._trigger("showMessage", event, {
            title: msg.contentText,
            type: msg.type
          });
        });

        if (data.data.needReload === true) {
          _.delay(
            function() {
              scopeWidget._trigger("reload", event, {});
            },
            // wait 1 second to see message before reload
            data.messages ? 1000 : 0
          );
        }
      })
      .catch(function(info) {
        scopeWidget._trigger("showMessage", event, {
          type: "error",
          message: info.responseJSON.error || info.responseJSON.exceptionMessage
        });
      });
  },

  /**
   * Resizes the menu
   */
  resize: function wMenuResize() {
    _.debounce(_.bind(this.inhibitBarMenu, this), 100);
    _.debounce(_.bind(this.updateResponsiveMenu, this), 100, false);
  },

  inhibitBarMenu: function wMenuInhibitBarMenu() {
    var widgetMenu = this;
    if (!widgetMenu.element.data("menu-opening") && this.element.css("overflow") !== "hidden") {
      this.element.find("li.k-state-border-down").each(function wMenuInhibitBarMenuClose() {
        widgetMenu.kendoMenuWidget.close($(this));
      });
    }
  },

  /**
   * Get scrollbar width by adding a element
   * @returns {number|*}
   */
  getScrollBarWidth: function wMenugetScrollBarWidth() {
    if (!this.scrollBarWidth) {
      var inner = document.createElement("p");
      inner.style.width = "100%";
      inner.style.height = "200px";

      var outer = document.createElement("div");
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
      outer.style.overflow = "scroll";
      var w2 = inner.offsetWidth;
      if (w1 === w2) {
        w2 = outer.clientWidth;
      }

      document.body.removeChild(outer);
      this.scrollBarWidth = w1 - w2;
    }

    return this.scrollBarWidth;
  },

  /**
   * Move menu to hamburger which can be displayed in same line menu
   */
  updateResponsiveMenu: function wMenuHideResponsiveMenu() {
    var barMenu = this.element;
    var $itemMenu = barMenu.find("ul.k-menu > .menu__element:not(.menu--important,.menu_element--hamburger)");
    var $importantItemMenu = barMenu.find("ul.k-menu > .menu__element.menu--important");
    var newHiddens = [];
    var currentWidth = 0;
    var visibleWidth = 0;
    var freeWidth = 0;
    var barmenuWidth = barMenu.width() - 2;
    var kendoMenu = this.kendoMenuWidget;
    var $hamburger = barMenu.find(".menu_element--hamburger");
    var hiddenItemsCount;
    var $hiddenItems = $($hamburger.find("ul").get(0)).find("> li.k-item");
    var hiddenLeft = $hiddenItems.length;

    if (barMenu.data("menu-opening")) {
      // Cannot redraw menu while menu is open because kendo failure occurs in touch device
      return;
    }
    this.inhibitBarMenu();
    $importantItemMenu.each(function wMenuComputeBarmenuWidth() {
      barmenuWidth -= $(this).outerWidth();
    });

    barmenuWidth -= $hamburger.outerWidth();

    if (barmenuWidth <= 0) {
      return;
    }

    // When no scrollbar need to add hypothetic scrollbar width because no event to refresh when scrollbar appear
    if (window.document.documentElement.scrollHeight <= window.document.documentElement.clientHeight) {
      barmenuWidth -= this.getScrollBarWidth(); // Supposed that scrollbar width is max 20px
    }

    // Detect free menu available width  and record menu items which not contains to bar menu
    $itemMenu.each(function wMenuComputeWidth() {
      currentWidth += $(this).outerWidth();
      if (currentWidth > barmenuWidth) {
        $(this).data("original-width", $(this).outerWidth());
        newHiddens.push(this);
      } else {
        visibleWidth += $(this).outerWidth();
      }
    });

    freeWidth = barmenuWidth - visibleWidth;

    if (hiddenLeft === 0 && newHiddens.length === 1) {
      // Special case for the last hidden may visible if hamburger is hide
      if ($(newHiddens[0]).outerWidth() < freeWidth + $hamburger.outerWidth()) {
        newHiddens = [];
      }
    }

    // Move each new hidden menu to hamburger
    _.each(newHiddens.reverse(), function wMenuItemToHamburger(item) {
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
      $hiddenItems.each(function wMenuItemFromHamburger() {
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

  _insertMenuContent: function wMenuInsertMenuContent(menus, $content, currentWidget, scopeMenu) {
    var subMenu;
    var hasBeforeContent = false;
    currentWidget = currentWidget || this;

    if (scopeMenu) {
      // Add fake before content if at least one element has before content to align all items
      _.each(menus, function wMenuInsertMenuContentfake(currentMenu) {
        if (currentMenu.iconUrl || currentMenu.beforeContent) {
          hasBeforeContent = true;
        }
      });
      if (hasBeforeContent) {
        _.each(menus, function wMenuInsertMenuContentBeforeContent(currentMenu) {
          if (!currentMenu.iconUrl && !currentMenu.beforeContent) {
            if (currentMenu.type !== "separatorMenu") {
              currentMenu.beforeContent = " ";
            }
          }
        });
      }
    }

    _.each(menus, function wMenuInsertMenuContentSet(currentMenu) {
      var $currentMenu;
      if (currentMenu.visibility === "hidden") {
        return;
      }
      currentMenu.htmlAttr = [];
      _.each(currentMenu.htmlAttributes, function wMenuInsertMenuContentSetHtml(attrValue, attrId) {
        if (attrId === "class") {
          currentMenu.cssClass = attrValue;
        } else {
          currentMenu.htmlAttr.push({
            attrId: attrId,
            attrValue: attrValue
          });
        }
        if (currentMenu.htmlLabel) {
          // reRender for variable labels
          currentMenu.htmlLabel = Mustache.render(currentMenu.htmlLabel || "", {
            document: currentWidget.options.document
          });
        }
        if (currentMenu.label) {
          // reRender for variable labels
          currentMenu.label = Mustache.render(currentMenu.label || "", {
            document: currentWidget.options.document
          });
        }
        if (currentMenu.tooltipLabel) {
          // reRender for variable labels
          currentMenu.tooltipLabel = Mustache.render(currentMenu.tooltipLabel || "", {
            document: currentWidget.options.document
          });
        }
      });

      currentMenu.contentLabel = currentMenu.htmlLabel || currentMenu.label;
      currentMenu.disabled = currentMenu.visibility === "disabled";
      if (currentMenu.type === "listMenu") {
        subMenu = "listMenu";

        $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu) || "", currentMenu));
        currentWidget._insertMenuContent(
          currentMenu.content || "",
          $currentMenu.find(".listmenu__content"),
          currentWidget,
          currentMenu
        );
      } else {
        if (currentMenu.type === "dynamicMenu") {
          subMenu = "dynamicMenu";
          if (currentMenu.url) {
            currentMenu.document = currentWidget.options.document;
            currentMenu.url = Mustache.render(currentMenu.url || "", currentMenu);
          }
          $currentMenu = $(Mustache.render(currentWidget._getTemplate(subMenu) || "", currentMenu));
        } else {
          currentMenu.document = currentWidget.options.document;
          if (currentMenu.url) {
            currentMenu.url = Mustache.render(currentMenu.url || "", currentMenu);
          }
          $currentMenu = $(Mustache.render(currentWidget._getTemplate(currentMenu.type) || "", currentMenu));
        }
      }
      if (currentMenu.tooltipLabel) {
        currentWidget._tooltips.push(
          $currentMenu.tooltip({
            trigger: "hover",
            html: currentMenu.tooltipHtml,
            placement: currentMenu.tooltipPlacement ? currentMenu.tooltipPlacement : "bottom",
            container: currentWidget.element
          })
        );
      }
      if (currentMenu.important) {
        $currentMenu.addClass("menu--important");
      }
      $currentMenu.data("menuConfiguration", currentMenu);
      $content.append($currentMenu);
    });
  },

  _getTemplate: function wMenuTemplate(name) {
    if (this.options.templates && this.options.templates.menu && this.options.templates.menu[name]) {
      return this.options.templates.menu[name];
    }
    if (window.dcp.templates && window.dcp.templates.menu && window.dcp.templates.menu[name]) {
      return window.dcp.templates.menu[name];
    }
    throw new Error("Menu unknown template " + name);
  },

  _destroy: function wMenuDestroy() {
    var kendoWidget = this.element.find(".menu__content").data("kendoMenu");
    if (kendoWidget) {
      kendoWidget.destroy();
    }
    $(window).off(".dcpMenu" + this.menuUID);
    this.element.closest("[data-controller]").off(".dcpMenu" + this.menuUID);
    _.each(this.popupWindows, function wMenuDestroyPopup(pWindow) {
      pWindow.destroy();
    });

    _.each(this._tooltips, function wMenuDestroyTooltip(currentTooltip) {
      currentTooltip.tooltip("dispose");
    });
    this.element.empty();
    this._super();
  }
});
