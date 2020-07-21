import $ from "jquery";
import _ from "underscore";
import "../widget";

export default $.widget("dcp.dcpWindow", {
  saveWidthToResize: null,
  intervalId: 0,
  options: {
    animation: {
      open: {
        effects: "fade:in",
        duration: 1000
      },
      close: {
        effects: "fade:out",
        duration: 1000
      }
    },
    actions: ["Maximize", "Close"],
    visible: false,
    height: "300px",
    width: "400px",
    /**
     * Try to add iframe title if no title is set
     */
    open: function wWindowOpen() {
      if (!this.options.title) {
        try {
          var kendoWindow = this;
          var iframeTitle = this.element
            .find("iframe")
            .contents()
            .find("title")
            .html();
          if (typeof iframeTitle === "undefined") {
            _.defer(function wWindowOpenSetTitle() {
              var currentTitle = "";
              var $content = kendoWindow.element.find("iframe").contents();
              kendoWindow.element.find("iframe").on("load", function wWindowOpenSetTitleNow() {
                try {
                  var $scopeWindow = $(this);
                  var $content = $scopeWindow.contents();

                  kendoWindow.setOptions({
                    title: currentTitle
                  });
                  $content.find("body").addClass("window-dialog");
                } catch (exp) {
                  //no test here
                }
              });

              // Verify if need to change title every seconds
              kendoWindow.intervalId = window.setInterval(function wWindowOpenSetTitleIsChanged() {
                try {
                  $content = kendoWindow.element.find("iframe").contents();
                  var newTitle = $content.find("title").html();
                  var currentIcon = $content.find('link[rel="shortcut icon"]').attr("href");

                  if (newTitle) {
                    $content.find("body").addClass("window-dialog");
                    if (currentIcon) {
                      newTitle = '<img src="' + currentIcon + '" /> ' + newTitle;
                    }
                    if (newTitle !== currentTitle) {
                      currentTitle = newTitle;
                      $(kendoWindow.element)
                        .closest(".k-window")
                        .find(".k-window-title")
                        .html(newTitle);
                    }
                  }
                } catch (exp) {
                  //no test here
                }
              }, 1000);
            });
          } else {
            kendoWindow.setOptions({
              title: $(this)
                .contents()
                .find("title")
                .html()
            });
          }
        } catch (exp) {
          //no test here
        }
      }
    },
    close: function wWindowClose() {
      window.clearInterval(this.intervalId);
    },
    destroy: function wWindowDestroy() {
      window.clearInterval(this.intervalId);
    }
  },

  currentWidget: null,
  _create: function wWindowCreate() {
    this.currentWidget = $('<div class="dialog-window"/>');
    this.element.append(this.currentWidget);
    this.element.data("dcpWindow", this);

    this.currentWidget.kendoWindow(this.options);
  },

  _getWindowTemplate: function wWindowCreate_getWindowTemplate(templateId) {
    if (
      this.options.templateData &&
      this.options.templateData.templates &&
      this.options.templateData.templates.window &&
      this.options.templateData.templates.window[templateId]
    ) {
      return this.options.templateData.templates.window[templateId];
    }
    if (window.dcp && window.dcp.templates && window.dcp.templates.window && window.dcp.templates.window[templateId]) {
      return window.dcp.templates.window[templateId];
    }
    throw new Error("Unknown window template  " + templateId);
  },

  /**
   * - Destoy the kendo window
   * - Remove resize.transition event
   */
  destroy: function wWindowDestroy() {
    window.clearInterval(this.intervalId);
    if (this.currentWidget && this.currentWidget.data("kendoWindow")) {
      this.currentWidget.data("kendoWindow").destroy();
    }
    this._super();
    $(window).off("resize.transition");
  },
  activate: function wWindowActivate() {
    this.displayDialogWindow();
  },
  open: function wWindowopen() {
    this.currentWidget.data("kendoWindow").open();
    this.resizeTransitionWindow();
  },

  /**
   * - Close the kendo window
   * - Remove resize.transition event
   */
  close: function wWindowClose() {
    window.clearInterval(this.intervalId);
    this.currentWidget.data("kendoWindow").close();
    $(window).off("resize.transition");
  },
  kendoWindow: function wWindowkendoWindow() {
    return this.currentWidget.data("kendoWindow");
  },

  /**
   * Attach resize.transition event
   */
  resizeTransitionWindow: function wWindow_resizeTransitionWindow() {
    $(window).on("resize.transition", _.bind(this.displayDialogWindow, this));
  },

  /**
   * on event : resize window :
   * - The kendo window is not in the viewport, the kendo window must be refocused
   * - The kendo window is bigger than window we maximize it
   * - The kendo window is smaller than windows
   */
  displayDialogWindow: function wWindow_displayDialogWindow() {
    const kWindow = this.kendoWindow();
    if (kWindow) {
      if (this.checkNeedCenter()) {
        kWindow.center();
      }
      if (this.checkNeedMaximize()) {
        if (this.currentWidget) {
          this.saveWidthToResize = this.currentWidget.closest(".k-window").outerWidth();
          kWindow.maximize();
        }
      }
      if (this.saveWidthToResize && this.saveWidthToResize < $(window).width()) {
        kWindow.restore();
        kWindow.center();
        this.saveWidthToResize = null;
      }
    }
  },

  /**
   * - Check if kendo window is bigger than window
   */
  checkNeedMaximize: function wWindow_checkNeedCenter() {
    if (this.currentWidget) {
      if (this.currentWidget.closest(".k-window").outerWidth() > $(window).width()) {
        return true;
      }
    }
    return false;
  },

  /**
   * - Check all side of the kendo Window
   * - If one side comes out, the kendo window must be refocused
   */
  checkNeedCenter: function wWindow_checkNeedCenter() {
    if (this.currentWidget) {
      const $kWindow = this.currentWidget.closest(".k-window");
      const ptnTop = $kWindow.offset().top;
      const ptnLeft = $kWindow.offset().left;
      const ptnRight = ptnLeft + $kWindow.outerWidth();
      const ptnDown = ptnTop + $kWindow.outerHeight();
      if (ptnTop < 0 || ptnLeft < 0 || ptnRight > $(window).width() || ptnDown > $(window).height()) {
        return true;
      }
    }
    return false;
  }
});
