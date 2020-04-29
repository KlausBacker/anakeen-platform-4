import $ from "jquery";
import _ from "underscore";
import "../text/wText";
$.widget("dcp.dcpHtmltext", $.dcp.dcpText, {
  options: {
    type: "htmltext",
    renderOptions: {
      kendoEditor: {}
    }
  },

  _initDom: function wHtmltext_InitDom() {
    const currentWidget = this;
    const bindSuper = () => {
      this._super();
    };
    const bindEvents = () => {
      this._initEvent();
    };
    const bindInitToolbar = () => {
      this._initToolBar();
    };
    try {
      this.popupWindows = {};

      if (this.getMode() === "write") {
        bindSuper();
        import("@progress/kendo-ui/js/kendo.editor").then(() => {
          currentWidget.kendoEditorInstance = currentWidget
            .getContentElements()
            .kendoEditor(currentWidget.options.renderOptions.kendoEditor)
            .data("kendoEditor");
          currentWidget.options.attributeValue.value = currentWidget.kendoEditorInstance.value();
          bindEvents();
          bindInitToolbar();
          currentWidget._trigger("widgetReady");
        });
      } else {
        bindSuper();
      }
    } catch (e) {
      if (window.dcp.logger) {
        window.dcp.logger(e);
      } else {
        console.error(e);
      }
    }
  },

  /**
   * Some hacks around the toolbar to pin it in the dom
   * see : https://medium.com/@unitehenry/hacking-the-kendo-inline-editor-for-tributejs-6dcf5824b20c
   * @private
   */
  _initToolBar: function htmltext_initToolbar() {
    const toolBarElement = this.kendoEditorInstance.toolbar.element.closest("div.k-editor-widget");
    //Show the toolbar
    this.kendoEditorInstance.toolbar.show();
    //Disable auto hide
    $(this.kendoEditorInstance.body).off("focusout.kendoEditor");
    //Disable drag of the toolbar
    toolBarElement.find(".k-editortoolbar-dragHandle").hide();
    // Relative Position Toolbar
    toolBarElement.css("position", "inherit");
    // Attach the element to a relative div
    const toolbar = toolBarElement.detach();
    this.element.find(".dcpAttribute__content__htmltext--toolbar").append(toolbar);
  },

  _initEvent: function _initEvent() {
    var currentWidget = this;
    this._super();

    if (this.getMode() === "write") {
      if (currentWidget.kendoEditorInstance) {
        currentWidget.kendoEditorInstance.bind("change", () => {
          currentWidget.setValue({ value: currentWidget.kendoEditorInstance.value() });
        });
      }
    }

    //If we are not in edit mode, we take care of anchor and redirect it
    if (this.getMode() !== "write") {
      this.element.on(
        "click." + this.eventNamespace,
        'a:not([href^="#action/"]):not([data-action])',
        function wHtmlAnchorClick(event) {
          var internalEvent = { prevent: false },
            anchor = this,
            $anchor = $(this),
            isNotPrevented,
            anchorsConfig,
            anchorsTarget,
            wFeature = "",
            href,
            dcpWindow;

          if (event.stopPropagation) {
            event.stopPropagation();
          }
          event.preventDefault();

          anchorsConfig = _.extend({}, currentWidget.options.renderOptions.anchors);

          isNotPrevented = currentWidget._trigger("anchorClick", internalEvent, {
            $el: currentWidget.element,
            index: currentWidget._getIndex(),
            options: {
              anchor: anchor,
              anchorsConfig: anchorsConfig
            }
          });
          if (isNotPrevented) {
            anchorsTarget = anchorsConfig.target || "_blank";
            href = anchor.href;

            if ($anchor.attr("href") && $anchor.attr("href").substring(0, 1) === "#") {
              href =
                window.location.protocol +
                "//" +
                window.location.hostname +
                (window.location.port ? ":" + window.location.port : "") +
                (window.location.pathname ? window.location.pathname : "/") +
                (window.location.search ? window.location.search : "") +
                $anchor.attr("href");
            }

            switch (anchorsTarget) {
              case "_dialog":
                if (currentWidget.popupWindows[href]) {
                  dcpWindow = currentWidget.popupWindows[href];
                } else {
                  dcpWindow = $("<div/>")
                    .appendTo("body")
                    .dcpWindow({
                      width: anchorsConfig.windowWidth,
                      height: anchorsConfig.windowHeight,
                      modal: anchorsConfig.modal,
                      content: href,
                      iframe: true
                    });

                  currentWidget.popupWindows[href] = dcpWindow;
                  dcpWindow
                    .data("dcpWindow")
                    .kendoWindow()
                    .center();
                }
                dcpWindow.data("dcpWindow").open();
                break;
              case "_self":
                window.location.href = href;
                break;
              default:
                if (anchorsConfig.windowWidth || anchorsConfig.windowHeight) {
                  if (anchorsConfig.windowWidth) {
                    wFeature += "width=" + parseInt(anchorsConfig.windowWidth, 10) + ",";
                  }
                  if (anchorsConfig.windowHeight) {
                    wFeature += "height=" + parseInt(anchorsConfig.windowHeight, 10) + ",";
                  }
                  wFeature += "resizable=yes,scrollbars=yes";
                }
                window.open(href, anchorsTarget, wFeature);
                break;
            }
          }
        }
      );
    }
  },
  /**
   * Define inputs for focus
   * @protected
   */
  _getFocusInput: function wHtmltext_getFocusInput() {
    return this.element;
  },
  /**
   * No use parent change
   */
  _initChangeEvent: function wHtmltext_initChangeEvent() {},

  getWidgetValue: function wHtmltext_getWidgetValue() {
    return this.getContentElements().value();
  },

  /**
   * Change the value of the widget
   * @param value
   */
  setValue: function wHtmltextSetValue(value) {
    value = _.clone(value);
    if (_.has(value, "value") && !_.has(value, "displayValue")) {
      value.displayValue = value.value;
    }
    //We don't take null value so replace it with ""
    if (value.value === null) {
      value.value = "";
    }

    if (this.getMode() === "write") {
      // Flash element only
      var originalValue = this.kendoEditorInstance.value();
      // : explicit lazy equal

      //noinspection JSHint
      if (originalValue.trim() != value.value.trim()) {
        // Modify value only if different

        this.kendoEditorInstance.value(value.value);
      }
    } else if (this.getMode() === "read") {
      this.getContentElements().html(value.displayValue);
    } else {
      throw new Error("Attribute " + this.options.id + " unknown mode " + this.getMode());
    }

    // call wAttribute::setValue()
    $.dcp.dcpAttribute.prototype.setValue.call(this, value);
  },

  getType: function wHtmltext_getType() {
    return "htmltext";
  },

  _destroy: function wHtmlTextDestroy() {
    var currentWidget = this;
    if (this.kendoEditorInstance && this.kendoEditorInstance.destroy) {
      this.kendoEditorInstance.destroy();
      _.defer(function wHtmltext_deferDestroy() {
        currentWidget._destroy();
      });
      return;
    }
    _.each(this.popupWindows, function wHtmltextDestroyPopup(pWindow) {
      pWindow.data("dcpWindow").destroy();
    });
    this._super();
  },

  /**
   * Trigger a ready event when widget is render
   */
  _triggerReady: function wAttributeReady() {
    if (this.getMode() !== "write") {
      this._super();
    }
  }
});
