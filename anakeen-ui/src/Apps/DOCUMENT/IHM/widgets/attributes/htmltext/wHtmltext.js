/*global define, _super*/
(function umdRequire(root, factory) {
  "use strict";

  if (typeof define === "function" && define.amd) {
    define([
      "jquery",
      "underscore",
      "dcpDocument/widgets/attributes/text/wText"
    ], factory);
  } else {
    //noinspection JSUnresolvedVariable
    factory(window.jQuery, window._);
  }
})(window, function require_htmltext($, _) {
  "use strict";

  $.widget("dcp.dcpHtmltext", $.dcp.dcpText, {
    options: {
      type: "htmltext",
      renderOptions: {
        anchors: {
          target: "_blank"
        },
        toolbar: "Basic",
        height: "100px",
        toolbarStartupExpanded: true,
        ckEditorConfiguration: {},
        ckEditorAllowAllTags: false
      },
      inline: false,
      locale: "en"
    },

    ckEditorInstance: null,

    _initDom: function wHtmltext_InitDom() {
      var currentWidget = this,
        bind_super = _.bind(this._super, this),
        bindInitEvent = _.bind(this._initEvent, this);
      try {
        this.popupWindows = {};
        if (this.options.renderOptions.ckEditorInline) {
          this.options.inline = true;
        }
        if (this.getMode() === "write") {
          (function wHtmltext_umdRequire(factory) {
            if (typeof define === "function" && define.amd) {
              require.ensure(
                ["documentCkEditor"],
                () => {
                  require("documentCkEditor");
                  factory();
                },
                "documentCkEditor"
              );
            } else {
              //noinspection JSUnresolvedVariable
              factory();
            }
          })(function wHtmltext_initEditDom() {
            var options = _.extend(
              currentWidget.ckOptions(),
              currentWidget.options.renderOptions.ckEditorConfiguration
            );
            bind_super();
            if (currentWidget.options.renderOptions.ckEditorAllowAllTags) {
              // Allow all HTML tags
              options.allowedContent = {
                $1: {
                  // Use the ability to specify elements as an object.
                  elements: window.CKEDITOR.dtd,
                  attributes: true,
                  styles: true,
                  classes: true
                }
              };
              options.disallowedContent = "script; *[on*]";
            }

            currentWidget.ckEditorInstance = currentWidget
              .getContentElements()
              .ckeditor(options).editor;
            currentWidget.options.attributeValue.value = currentWidget.ckEditorInstance.getData();
            bindInitEvent();
          });
        } else {
          bind_super();
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
     * Define option set for ckEditor widget
     * @returns {{language: string, contentsCss: string[], removePlugins: string, toolbarCanCollapse: boolean, entities: boolean, filebrowserImageBrowseUrl: string, filebrowserImageUploadUrl: string, toolbar_Full: *[], toolbar_Default: *[], toolbar_Simple: *[], toolbar_Basic: *[], removeButtons: string}}
     */
    ckOptions: function wHtmlTextCkOptions() {
      var locale = this.options.locale;
      var hrefBase = $("head base").attr("href") || ""; // no use document.baseURI because get complete url if no base href defined
      if (this.options.renderOptions.toolbar) {
        this.options.renderOptions.ckEditorConfiguration.toolbar = this.options.renderOptions.toolbar;
      }
      if (this.options.renderOptions.height) {
        this.options.renderOptions.ckEditorConfiguration.height = this.options.renderOptions.height;
      }
      if (!_.isUndefined(this.options.renderOptions.toolbarStartupExpanded)) {
        this.options.renderOptions.ckEditorConfiguration.toolbarStartupExpanded = this.options.renderOptions.toolbarStartupExpanded;
      }
      return {
        language: locale.substring(0, 2),
        contentsCss: ["css/ank/document/ckeditor.css"],
        removePlugins: "elementspath", // no see HTML path elements
        toolbarCanCollapse: true,
        entities: false, // no use HTML entities
        baseHref: hrefBase,
        title: "",
        filebrowserImageBrowseUrl: hrefBase + "?sole=Y&app=FDL&action=CKIMAGE",
        filebrowserImageUploadUrl: hrefBase + "?sole=Y&app=FDL&action=CKUPLOAD",
        toolbar_Full: [
          {
            name: "document",
            items: [
              "Sourcedialog",
              "-",
              "NewPage",
              "DocProps",
              "Preview",
              "Print",
              "-",
              "Templates"
            ]
          },
          {
            name: "clipboard",
            items: [
              "Cut",
              "Copy",
              "Paste",
              "PasteText",
              "PasteFromWord",
              "-",
              "Undo",
              "Redo"
            ]
          },
          {
            name: "editing",
            items: ["Find", "Replace", "-", "SelectAll", "-"]
          },
          {
            name: "forms",
            items: [
              "Form",
              "Checkbox",
              "Radio",
              "TextField",
              "Textarea",
              "Select",
              "Button",
              "ImageButton",
              "HiddenField"
            ]
          },
          "/",
          {
            name: "basicstyles",
            items: [
              "Bold",
              "Italic",
              "Underline",
              "Strike",
              "Subscript",
              "Superscript",
              "-",
              "RemoveFormat"
            ]
          },
          {
            name: "paragraph",
            items: [
              "NumberedList",
              "BulletedList",
              "-",
              "Outdent",
              "Indent",
              "-",
              "Blockquote",
              "CreateDiv",
              "-",
              "JustifyLeft",
              "JustifyCenter",
              "JustifyRight",
              "JustifyBlock",
              "-",
              "BidiLtr",
              "BidiRtl"
            ]
          },
          { name: "links", items: ["Link", "Unlink"] },
          {
            name: "insert",
            items: [
              "Image",
              "Table",
              "HorizontalRule",
              "Smiley",
              "SpecialChar",
              "PageBreak",
              "Iframe"
            ]
          },
          "/",
          { name: "styles", items: ["Styles", "Format", "Font", "FontSize"] },
          { name: "colors", items: ["TextColor", "BGColor"] },
          { name: "tools", items: ["Maximize", "ShowBlocks", "-", "About"] }
        ],
        toolbar_Default: [
          { name: "document", items: ["Sourcedialog"] },
          {
            name: "clipboard",
            items: [
              "Cut",
              "Copy",
              "Paste",
              "PasteText",
              "PasteFromWord",
              "-",
              "Undo",
              "Redo"
            ]
          },
          { name: "editing", items: ["Find", "Replace", "-", "SelectAll"] },
          {
            name: "basicstyles",
            items: [
              "Bold",
              "Italic",
              "Underline",
              "Strike",
              "Subscript",
              "Superscript",
              "-",
              "RemoveFormat"
            ]
          },
          {
            name: "paragraph",
            items: [
              "NumberedList",
              "BulletedList",
              "-",
              "Outdent",
              "Indent",
              "-",
              "Blockquote",
              "CreateDiv",
              "-",
              "JustifyLeft",
              "JustifyCenter",
              "JustifyRight",
              "JustifyBlock",
              "-",
              "BidiLtr",
              "BidiRtl"
            ]
          },
          { name: "links", items: ["Link", "Unlink"] },
          {
            name: "insert",
            items: [
              "Image",
              "Table",
              "HorizontalRule",
              "SpecialChar",
              "PageBreak",
              "Iframe"
            ]
          },
          { name: "styles", items: ["Styles", "Format", "Font", "FontSize"] },
          { name: "colors", items: ["TextColor", "BGColor"] },
          { name: "tools", items: ["Maximize", "ShowBlocks", "-", "About"] }
        ],
        toolbar_Simple: [
          { name: "document", items: [] },
          {
            name: "basicstyles",
            items: [
              "Bold",
              "Italic",
              "Underline",
              "Strike",
              "-",
              "RemoveFormat"
            ]
          },
          {
            name: "paragraph",
            items: [
              "NumberedList",
              "BulletedList",
              "-",
              "Outdent",
              "Indent",
              "-",
              "-",
              "JustifyLeft",
              "JustifyCenter",
              "JustifyRight",
              "JustifyBlock"
            ]
          },
          { name: "links", items: ["Link", "Unlink"] },
          { name: "insert", items: ["Image", "Table", "SpecialChar"] },
          { name: "styles", items: ["Format", "FontSize"] },
          { name: "colors", items: ["TextColor", "BGColor"] },
          { name: "tools", items: ["Maximize", "Sourcedialog", "-", "About"] }
        ],
        toolbar_Basic: [
          {
            name: "links",
            items: [
              "Bold",
              "Italic",
              "-",
              "NumberedList",
              "BulletedList",
              "-",
              "Link",
              "Unlink",
              "-",
              "Maximize",
              "About"
            ]
          }
        ],
        removeButtons: ""
      };
    },

    _initEvent: function _initEvent() {
      var currentWidget = this;
      this._super();
      if (this.ckEditorInstance) {
        this.ckEditorInstance.on("change", function wHtmltext_change() {
          currentWidget.setValue({ value: this.getData() });
        });

        this.ckEditorInstance.on("focus", function wHtmltext_focus() {
          var ktTarget = currentWidget.element.find(".input-group");
          currentWidget.showInputTooltip(ktTarget);
          currentWidget.element.find(".cke").addClass("k-state-focused");
          currentWidget.element
            .closest(".dcpAttribute__content")
            .addClass("dcpAttribute--focus");
        });

        this.ckEditorInstance.on("blur", function wHtmltext_blur() {
          var ktTarget = currentWidget.element.find(".input-group");
          currentWidget.hideInputTooltip(ktTarget);
          currentWidget.element.find(".cke").removeClass("k-state-focused");
          currentWidget.element
            .closest(".dcpAttribute__content")
            .removeClass("dcpAttribute--focus");
        });

        this.ckEditorInstance.on("instanceReady", function wHtmltext_loaded() {
          currentWidget._trigger("widgetReady");
        });

        this.element.on(
          "postMoved" + this.eventNamespace,
          function wHtmlTextOnPostMoved(event, eventData) {
            if (eventData && eventData.to === currentWidget.options.index) {
              currentWidget.redraw();
            }
          }
        );
      }

      //If we are not in CKEDITOR mode, we take care of anchor and redirect it
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

            anchorsConfig = _.extend(
              {},
              currentWidget.options.renderOptions.anchors
            );

            isNotPrevented = currentWidget._trigger(
              "anchorClick",
              internalEvent,
              {
                $el: currentWidget.element,
                index: currentWidget._getIndex(),
                options: {
                  anchor: anchor,
                  anchorsConfig: anchorsConfig
                }
              }
            );
            if (isNotPrevented) {
              var $base = $("base");
              var isAbsUrl = new RegExp("^(?:[a-z]+:)?//", "i");

              anchorsTarget = anchorsConfig.target || "_blank";
              href = anchor.href;

              if (
                $anchor.attr("href") &&
                $anchor.attr("href").substring(0, 1) === "#"
              ) {
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
                  // For IE : Not honor base href in this case
                  if (!isAbsUrl.test(href)) {
                    window.location.href = $base.attr("href") + href;
                  } else {
                    window.location.href = href;
                  }
                  break;
                default:
                  if (anchorsConfig.windowWidth || anchorsConfig.windowHeight) {
                    if (anchorsConfig.windowWidth) {
                      wFeature +=
                        "width=" +
                        parseInt(anchorsConfig.windowWidth, 10) +
                        ",";
                    }
                    if (anchorsConfig.windowHeight) {
                      wFeature +=
                        "height=" +
                        parseInt(anchorsConfig.windowHeight, 10) +
                        ",";
                    }
                    wFeature += "resizable=yes,scrollbars=yes";
                  }
                  if (!isAbsUrl.test(href)) {
                    href = $base.attr("href") + href;
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
      return this.getContentElements().val();
    },

    /**
     * Change the value of the widget
     * @param value
     */
    setValue: function wHtmltextSetValue(value) {
      value = _.clone(value);
      if (value.value === null) {
        // ckEditor restore original value if set to null
        value.value = "";
      }
      if (_.has(value, "value") && !_.has(value, "displayValue")) {
        value.displayValue = value.value;
      }
      if (this.getMode() === "write") {
        // Flash element only
        var originalValue = this.ckEditorInstance.getData();
        // : explicit lazy equal

        //noinspection JSHint
        if (originalValue.trim() != value.value.trim()) {
          // Modify value only if different
          if (this.options.inline) {
            this.getContentElements().html(value.value);
          } else {
            this.getContentElements().val(value.value);
            this.flashElement(this.element.find("iframe"));
          }
        }
      } else if (this.getMode() === "read") {
        this.getContentElements().html(value.displayValue);
      } else {
        throw new Error(
          "Attribute " + this.options.id + " unknown mode " + this.getMode()
        );
      }

      // call wAttribute::setValue()
      $.dcp.dcpAttribute.prototype.setValue.call(this, value);
    },

    getType: function wHtmltext_getType() {
      return "htmltext";
    },

    _destroy: function wHtmlTextDestroy() {
      var currentWidget = this;
      if (this.ckEditorInstance && this.ckEditorInstance.destroy) {
        if (
          this.ckEditorInstance.status === "loaded" ||
          this.ckEditorInstance.status === "ready"
        ) {
          this.ckEditorInstance.destroy();
          _.defer(function wHtmltext_deferDestroy() {
            currentWidget._destroy();
          });
          return;
        } else if (this.ckEditorInstance.status === "unloaded") {
          this.ckEditorInstance.on("loaded", function wHtmltext_loaded() {
            currentWidget._destroy();
          });
          return;
        }
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

  return $.fn.dcpHtmltext;
});
