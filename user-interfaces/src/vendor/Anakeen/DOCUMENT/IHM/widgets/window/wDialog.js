define(["jquery", "underscore", "dcpDocument/widgets/widget"], function wDialog(
  $,
  _
) {
  "use strict";

  $.widget("dcp.dcpDialog", {
    options: {
      window: {
        modal: true,
        actions: ["Maximize", "Close"],
        visible: false,
        height: "300px",
        width: "500px",
        title: "-"
      },
      maximizeWidth: 768 // Limit in px to open with maximize
    },

    dialogWindow: null,

    _create: function dcpDialog_create() {
      var currentWidget = this;
      this.element.data("dcpDialog", this);
      if (!this.options.window.close) {
        this.options.window.close = function dcpDialog_onclose() {
          _.defer(_.bind(currentWidget.destroy, currentWidget));
        };
      } else {
        this.options.window.close = _.wrap(
          this.options.window.close,
          function dcpDialog_closeWrap(close) {
            var event = arguments[1];
            close.apply(this, _.rest(arguments));
            if (!event.isDefaultPrevented()) {
              _.defer(_.bind(currentWidget.destroy, currentWidget));
            }
          }
        );
      }

      if ($(window).width() <= this.options.maximizeWidth) {
        this.options.window.width = "auto";
        this.options.window.heigth = "auto";
      }
      this.dialogWindow = this.element
        .kendoWindow(this.options.window)
        .data("kendoWindow");
    },

    open: function dcpDialog_Open() {
      var kWindow = this.dialogWindow;
      if ($(window).width() <= this.options.maximizeWidth) {
        kWindow.setOptions({
          actions: ["Close"],
          animation: false
        });

        kWindow.open();
        _.delay(function wDialogMaximize() {
          kWindow.maximize(); // Need to defer to wait window to be really opened
        }, 100);
      } else {
        kWindow.setOptions({
          actions: this.options.window.actions
        });
        kWindow.center();
        kWindow.open();
      }
    },

    close: function dcpDialog_close() {
      var kendoWindow = this.dialogWindow;
      if (kendoWindow) {
        kendoWindow.close();
      }
    },

    _destroy: function dcpDialog_destroy() {
      if (
        this.element &&
        this.dialogWindow &&
        this.element.data("kendoWindow")
      ) {
        this.dialogWindow.destroy();
        this.dialogWindow = null;
      } else {
        this._super();
      }
    }
  });
});
