import $ from "jquery";
import _ from "underscore";
import "../text/wText";

$.widget("dcp.dcpHtmltext", $.dcp.dcpText, {
  options: {
    type: "htmltext",
    renderOptions: {
      kendoEditorConfiguration: {},
      anchors: {
        target: "_blank"
      },
      toolbar: "",
      translatedLabels: [],
      height: "100px"
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
        this._initKendoEditorOptions();
        import("@progress/kendo-ui/js/kendo.editor").then(() => {
          currentWidget.kendoEditorInstance = currentWidget
            .getContentElements()
            .kendoEditor(currentWidget.options.renderOptions.kendoEditorConfiguration)
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

  _initKendoEditorOptions: function htmltext__initKendoEditorOptions() {
    const buttons = {
      Basic: [
        "bold",
        "italic",
        // ----------
        "insertUnorderedList",
        "insertOrderedList",
        // ----------
        "createLink",
        "unlink"
      ],
      Simple: [
        "bold",
        "italic",
        "underline",
        "strikethrough",
        "cleanFormatting",
        // ----------
        "insertUnorderedList",
        "insertOrderedList",

        "indent",
        "outdent",

        "justifyLeft",
        "justifyCenter",
        "justifyRight",
        "justifyFull",

        "createLink",
        "unlink",

        "insertImage",
        // table
        "tableWizard",
        "createTable",
        "addRowAbove",
        "addRowBelow",
        "addColumnLeft",
        "addColumnRight",
        "deleteRow",
        "deleteColumn",
        "mergeCellsHorizontally",
        "mergeCellsVertically",
        "splitCellHorizontally",
        "splitCellVertically",
        //format
        {
          name: "formatting",
          items: [
            { text: "Heading 1", value: "h1" },
            { text: "Heading 2", value: "h2" },
            { text: "Heading 3", value: "h3" },
            { text: "Paragraph", value: "p" },
            { text: "Preformatted", value: "pre" }
          ]
        },
        "fontSize",
        {
          name: "foreColor",
          palette: null
        },
        {
          name: "backColor",
          palette: null
        },

        "viewHtml"
      ],
      Full: [
        "bold",
        "italic",
        "underline",
        "strikethrough",
        "justifyLeft",
        "justifyCenter",
        "justifyRight",
        "justifyFull",
        "insertUnorderedList",
        "insertOrderedList",
        "indent",
        "outdent",
        "createLink",
        "unlink",
        "insertImage",
        "subscript",
        "superscript",
        // table
        "tableWizard",
        "createTable",
        "addRowAbove",
        "addRowBelow",
        "addColumnLeft",
        "addColumnRight",
        "deleteRow",
        "deleteColumn",
        "mergeCellsHorizontally",
        "mergeCellsVertically",
        "splitCellHorizontally",
        "splitCellVertically",
        // format
        {
          name: "formatting",
          items: [
            { text: "Heading 1", value: "h1" },
            { text: "Heading 2", value: "h2" },
            { text: "Heading 3", value: "h3" },
            { text: "Paragraph", value: "p" },
            { text: "Preformatted", value: "pre" }
          ]
        },
        "cleanFormatting",
        "copyFormat",
        "applyFormat",
        "fontName",
        "fontSize",

        {
          name: "foreColor",
          palette: null
        },
        {
          name: "backColor",
          palette: null
        },
        "viewHtml"
      ]
    };

    if (this.options.renderOptions.toolbar && !this.options.renderOptions.kendoEditorConfiguration.tools) {
      if (buttons[this.options.renderOptions.toolbar]) {
        this.options.renderOptions.kendoEditorConfiguration.tools = buttons[this.options.renderOptions.toolbar];
      }
    }
    if (this.options.renderOptions.translatedLabels) {
      this.options.renderOptions.kendoEditorConfiguration.messages = this.options.renderOptions.translatedLabels;
      if (this.options.renderOptions.translatedLabels.formattingItems) {
        this.options.renderOptions.kendoEditorConfiguration.tools.forEach(tool => {
          if (tool.name === "formatting") {
            tool.items.forEach(item => {
              if (this.options.renderOptions.translatedLabels.formattingItems[item.value]) {
                item.text = this.options.renderOptions.translatedLabels.formattingItems[item.value];
              }
            });
          }
        });
      }
    }
    this.options.renderOptions.kendoEditorConfiguration.execute = e => {
      if (e.name === "viewhtml") {
        const label = this.options.label;
        window.setTimeout(() => {
          const $window = $(".k-viewhtml-dialog");
          const kWindow = $window.getKendoWindow();
          $window.addClass("htmltext--viewhtml");
          kWindow.setOptions({ title: label, resizable: true, actions: ["Maximize", "Close"] });
        }, 10);
      }
    };
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

    toolBarElement.css({ width: "100%", position: "static", top: "", left: "", opacity: "" });
    if (this.options.renderOptions.height) {
      $(this.kendoEditorInstance.body).css("height", this.options.renderOptions.height);
    }
    // Attach the element to a relative div
    const toolbar = toolBarElement.detach();
    this.element.find(".dcpAttribute__content__htmltext--toolbar").append(toolbar);
  },

  _initEvent: function _initEvent() {
    const currentWidget = this;
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
          let internalEvent = { prevent: false },
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

      if (originalValue.trim() !== value.value.trim()) {
        // Modify value only if different

        this.kendoEditorInstance.value(value.value);
      }
    } else if (this.getMode() === "read") {
      this.getContentElements().html(value.displayValue);
    } else {
      throw new Error("Attribute " + this.options.id + " unknown mode " + this.getMode());
    }

    // call wAttribute::setValue()
    // noinspection JSPotentiallyInvalidConstructorUsage
    $.dcp.dcpAttribute.prototype.setValue.call(this, value);
  },

  getType: function wHtmltext_getType() {
    return "htmltext";
  },

  _destroy: function wHtmlTextDestroy() {
    const currentWidget = this;
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
