import $ from "jquery";
import _ from "underscore";
import "../widget";

export default $.widget("dcp.dcpDialog", {
  /**
   * - Kendo window option
   */
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
  saveWidthToResize: null,

  /**
   * - Set kendo dialog option to create the dialog
   */
  _create: function dcpDialog_create() {
    var currentWidget = this;
    this.element.data("dcpDialog", this);
    if (!this.options.window.close) {
      this.options.window.close = function dcpDialog_onclose() {
        _.defer(_.bind(currentWidget.destroy, currentWidget));
      };
    } else {
      this.options.window.close = _.wrap(this.options.window.close, function dcpDialog_closeWrap(close) {
        var event = arguments[1];
        close.apply(this, _.rest(arguments));
        if (!event.isDefaultPrevented()) {
          _.defer(_.bind(currentWidget.destroy, currentWidget));
        }
      });
    }

    if ($(window).width() <= this.options.maximizeWidth) {
      this.options.window.width = "auto";
      this.options.window.heigth = "auto";
    }
    this.dialogWindow = this.element.kendoWindow(this.options.window).data("kendoWindow");
    this.dialogWindow.bind("activate", () => {
      currentWidget.displayDialogWindow();
      // we need to call again the same function, because when we have an big element, when it is display, it is not center
      _.defer(_.bind(currentWidget.displayDialogWindow, currentWidget));
    });
  },

  /**
   * - Open the kendo dialog
   * - Call function to activate the resize event
   */
  open: function dcpDialog_Open() {
    var kWindow = this.dialogWindow;
    this.resizeTransitionWindow();
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

  /**
   * - Close the kendo dialog
   * - Remove resize.transition event
   */
  close: function dcpDialog_close() {
    var kendoWindow = this.dialogWindow;
    if (kendoWindow) {
      kendoWindow.close();
    }
    $(window).off("resize.transition");
  },

  /**
   * - Destoy the kendo dialog
   * - Remove resize.transition event
   */
  _destroy: function dcpDialog_destroy() {
    if (this.element && this.dialogWindow && this.element.data("kendoWindow")) {
      this.dialogWindow.destroy();
      this.dialogWindow = null;
    } else {
      this._super();
    }
    $(window).off("resize.transition");
  },

  /**
   * Attach resize.transition event
   */
  resizeTransitionWindow: function wDialog_resizeTransitionWindow() {
    $(window).on("resize.transition", _.bind(this.displayDialogWindow, this));
  },

  /**
   * on event : resize window :
   * - The kendo dialog is not in the viewport, the kendo dialog must be refocused
   * - The kendo dialog is bigger than window we maximize it
   * - The kendo dialog is smaller than windows
   */
  displayDialogWindow: function wDialog_displayDialogWindow() {
    if (this.dialogWindow && this.dialogWindow.element) {
      if (this.checkNeedCenter()) {
        this.dialogWindow.center();
      }
      if (this.checkNeedMaximize()) {
        this.saveWidthToResize = this.dialogWindow.element.closest(".k-window").outerWidth();
        this.dialogWindow.maximize();
      }
      if (this.saveWidthToResize && this.saveWidthToResize < $(window).width()) {
        this.dialogWindow.restore();
        this.dialogWindow.center();
        this.saveWidthToResize = null;
      }
    }
  },

  /**
   * - Check if kendo dialog is bigger than window
   */
  checkNeedMaximize: function wDialog_checkNeedMaximize() {
    if (this.dialogWindow && this.dialogWindow.element) {
      if (this.dialogWindow.element.closest(".k-window").outerWidth() > $(window).width()) {
        return true;
      }
    }
    return false;
  },

  /**
   * - Check all side of the kendo dialog
   * - if one side comes out, the kendo dialog must be refocused
   */
  checkNeedCenter: function wDialog_checkNeedCenter() {
    if (this.dialogWindow && this.dialogWindow.element) {
      const $kWindow = this.dialogWindow.element.closest(".k-window");
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
