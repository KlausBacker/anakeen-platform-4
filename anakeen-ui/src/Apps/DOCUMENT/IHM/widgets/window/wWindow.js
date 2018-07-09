define(["underscore", "jquery", "dcpDocument/widgets/widget"], function wWindow(
  _,
  $
) {
  "use strict";

  $.widget("dcp.dcpWindow", {
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
                kendoWindow.element
                  .find("iframe")
                  .on("load", function wWindowOpenSetTitleNow() {
                    try {
                      var $scopeWindow = $(this);
                      var $content = $scopeWindow.contents();

                      kendoWindow.setOptions({
                        title: currentTitle
                      });
                      $content.find("body").addClass("window-dialog");
                    } catch (exp) {}
                  });

                // Verify if need to change title every seconds
                kendoWindow.intervalId = window.setInterval(
                  function wWindowOpenSetTitleIsChanged() {
                    try {
                      $content = kendoWindow.element.find("iframe").contents();
                      var newTitle = $content.find("title").html();
                      var currentIcon = $content
                        .find('link[rel="shortcut icon"]')
                        .attr("href");

                      if (newTitle) {
                        $content.find("body").addClass("window-dialog");
                        if (currentIcon) {
                          newTitle =
                            '<img src="' + currentIcon + '" /> ' + newTitle;
                        }
                        if (newTitle !== currentTitle) {
                          currentTitle = newTitle;
                          $(kendoWindow.element)
                            .closest(".k-window")
                            .find(".k-window-title")
                            .html(newTitle);
                        }
                      }
                    } catch (exp) {}
                  },
                  1000
                );
              });
            } else {
              kendoWindow.setOptions({
                title: $(this)
                  .contents()
                  .find("title")
                  .html()
              });
            }
          } catch (exp) {}
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
      if (
        window.dcp &&
        window.dcp.templates &&
        window.dcp.templates.window &&
        window.dcp.templates.window[templateId]
      ) {
        return window.dcp.templates.window[templateId];
      }
      throw new Error("Unknown window template  " + templateId);
    },
    destroy: function wWindowDestroy() {
      window.clearInterval(this.intervalId);
      if (this.currentWidget && this.currentWidget.data("kendoWindow")) {
        this.currentWidget.data("kendoWindow").destroy();
      }
      this._super();
    },
    open: function wWindowopen() {
      this.currentWidget.data("kendoWindow").open();
    },
    close: function wWindowClose() {
      window.clearInterval(this.intervalId);
      this.currentWidget.data("kendoWindow").close();
    },
    kendoWindow: function wWindowkendoWindow() {
      return this.currentWidget.data("kendoWindow");
    }
  });
});
