import $ from "jquery";
import _ from "underscore";
import "../text/wText";
import i18n from "../../../i18n/documentCatalog";

$.widget("dcp.dcpHtmltext", $.dcp.dcpText, {
  options: {
    type: "htmltext",
    renderOptions: {
      kendoEditorConfiguration: {
        serialization: {
          entities: false
        },
        pasteCleanup: {
          msAllFormatting: true,
          msConvertLists: true,
          msTags: true,
          span: true,
          css: true,
          all: false
        }
      },
      anchors: {
        target: "_blank"
      },
      toolbar: "",
      translatedLabels: [],
      height: "100px"
    }
  },

  _imgTransfertId: 0,

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
          if (!currentWidget.options.attributeValue.value) {
            this.kendoEditorInstance.value("<p></p>");
          }
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
      if (e.name === "insertimage") {
        const RangeUtils = kendo.ui.editor.RangeUtils;
        const range = e.command.getRange();
        const img = RangeUtils.image(range);
        const editor = this.kendoEditorInstance;

        if (!img) {
          // If image selected : keep orignal behavior
          e.preventDefault();

          this.selectImage("image/*", false).then(currentImage => {
            const reader = new FileReader();
            reader.addEventListener(
              "load",
              function() {
                editor.exec("inserthtml", {
                  value: `<img src="${reader.result}">`
                });
              },
              false
            );
            reader.readAsDataURL(currentImage);
          });
        }
      }
    };
    if (!this.options.renderOptions.kendoEditorConfiguration.pasteCleanup.custom) {
      this.options.renderOptions.kendoEditorConfiguration.pasteCleanup.custom = html => {
        if (html.substring(0, 5) === "<img ") {
          try {
            const $html = $(html);
            if ($html.prop("tagName") === "IMG") {
              return this.transfertImage(html);
            }
          } catch (e) {
            // do nothing , it is not html continue paste
          }
        }
        return html;
      };
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
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutationRecord) {
          // Observe style img mutation to synchro with its container
          if (mutationRecord.target.tagName === "IMG") {
            const $container = $(mutationRecord.target).closest(".htmltext-img-container");
            if ($container.length === 1) {
              const targetStyle = mutationRecord.target.style;
              let newStyle = {
                width: $container.get(0).style.width,
                height: $container.get(0).style.height,
                "background-image": $container.get(0).style.backgroundImage
              };
              for (let i = 0; i < targetStyle.length; i++) {
                let cssItem = targetStyle.item(i);
                newStyle[cssItem] = targetStyle.getPropertyValue(cssItem);
              }
              $container.attr("style", "").css(newStyle);
            }
          }
        });
      });
      if (currentWidget.kendoEditorInstance) {
        currentWidget.kendoEditorInstance.bind("change", () => {
          this._removeResizeImageContainers();
          currentWidget.setValue({ value: currentWidget.kendoEditorInstance.value() });
        });

        //---------------------
        // Resize inside images : use resizable css
        $(this.kendoEditorInstance.body).on("click", e => {
          const $img = $(e.target);

          if (e.isTrigger) {
            // Do nothing it is just to select img
            return;
          }
          if ($img.prop("tagName") === "IMG") {
            // insert a div container to add use resizable
            const $imgContainer = $img.parent();

            if (!$imgContainer.hasClass("htmltext-img-container")) {
              const width = $img.width();
              const height = $img.height();
              const $imgcontainer = $("<div/>")
                .addClass("htmltext-img-container")
                .attr("style", $img.attr("style"))
                .width(width)
                .height(height)
                .css("background-image", 'url("' + $img.attr("src") + '")');

              this._removeResizeImageContainers();
              if ($img.css("float") === "right") {
                // Move scroll bar to the left
                $imgcontainer.css("direction", "rtl");
              }

              $img.width("").height("");

              $imgcontainer.insertBefore($img);
              $imgcontainer.append($img);

              // Need disable button because, the insertion is always to the beginning and not and the current position
              this._disableButton("insertImage", true);
              observer.observe($imgcontainer.get(0), { attributes: true, subtree: true, attributeFilter: ["style"] });
            }
          } else {
            observer.disconnect();
            const $innerImg = this._removeResizeImageContainers();
            if ($innerImg) {
              // Original image selection
              $innerImg.trigger("click");
            }
          }
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
   * Disable/Enable a specific button in toolbar
   * @param buttonName
   * @param disabled
   * @private
   */
  _disableButton: function(buttonName, disabled) {
    const editor = this.kendoEditorInstance;
    const toolbar = editor.toolbar;
    const items = editor.toolbar.items();
    let itemName;

    for (let i = 0; i < items.length; i++) {
      itemName = toolbar._toolName(items[i]);
      if (itemName === buttonName) {
        if (disabled === true) {
          $(items[i]).addClass("k-state-disabled");
        } else {
          $(items[i]).removeClass("k-state-disabled");
        }
      }
    }
  },

  /**
   * Remove temporary div used for resize images
   * @returns {*}
   * @private
   */
  _removeResizeImageContainers: function wHtmltext_removeResizeImageContainers() {
    const $containers = $(this.element).find(".htmltext-img-container");

    let $iImg = null;
    $containers.each(function() {
      $iImg = $(this).find("img");
      const width = $(this).width();
      const height = $(this).height();
      // use tag img tag dimension because kendo analyze this attributes to get dimension instead of style
      $iImg.attr("width", Number.parseInt(width, 10));
      $iImg.attr("height", Number.parseInt(height, 10));
      $(this).css("background-image", "");
      $iImg.attr("style", $(this).attr("style"));
      $iImg.insertBefore($(this));
      $(this).remove();
    });

    this._disableButton("insertImage", false);
    return $iImg;
  },

  /**
   * Select file(s).
   * @param {String} contentType The content type of files you wish to select. For instance "image/*" to select all kinds of images.
   * @param {Boolean} multiple Indicates if the user can select multiples file.
   * @returns {Promise<File|File[]>} A promise of a file or array of files in case the multiple parameter is true.
   */
  selectImage: function(contentType, multiple) {
    return new Promise(resolve => {
      let input = document.createElement("input");
      input.type = "file";
      input.multiple = multiple;
      input.accept = contentType;

      input.onchange = () => {
        let files = Array.from(input.files);
        if (multiple) resolve(files);
        else resolve(files[0]);
      };

      input.click();
    });
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

  dataURLtoFile: function dataURLtoFile(dataurl, filename) {
    let arr = dataurl.split(","),
      mime = arr[0].match(/:(.*?);/)[1],
      bstr = atob(arr[1]),
      n = bstr.length,
      ext = mime.split("/")[1],
      u8arr = new Uint8Array(n);

    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }

    return new File([u8arr], filename + "." + ext, { type: mime });
  },

  transfertImage: function(html) {
    const $img = $(html);
    const localid = ++this._imgTransfertId;
    const event = { prevent: false, target: $img };
    const imgFile = this.dataURLtoFile($img.attr("src"), "paste");
    const formData = new FormData();
    const currentWidget = this;

    const img = new Image();
    img.onload = function() {
      // Limit width to content when paste it
      const wWidget = $(currentWidget.element).width();
      const wImg = this.width;
      if (wWidget < wImg) {
        const $imgLocal = $(currentWidget.element).find('img[data-localid="' + localid + '"]');
        $imgLocal.attr("width", wWidget - 30);
      }
    };
    img.src = $img.attr("src");

    $img.attr("data-localid", localid);
    $img.addClass("htmltext-img-transferring", localid);

    const isNotPrevented = currentWidget._trigger("uploadfile", event, {
      $el: currentWidget.element,
      index: currentWidget._getIndex(),
      file: imgFile
    });
    if (!isNotPrevented) {
      return html;
    }

    formData.append("dcpFile", imgFile);
    $.ajax({
      type: "POST",
      url: "/api/v2/temporaryFiles/",
      processData: false,
      contentType: false,
      cache: false,
      data: formData,

      xhr: function wFileXhrAddProgress() {
        const xhrObject = $.ajaxSettings.xhr();
        if (xhrObject.upload) {
          const styleTag = document.head.appendChild(document.createElement("style"));
          styleTag.textContent = 'img.htmltext-img-transferring[data-localid="' + localid + '"] { opacity: 0}';

          xhrObject.upload.addEventListener(
            "progress",
            function wFileProgress(event) {
              let percent = 0;
              let position = event.loaded || event.position;
              let total = event.total;
              const $img = $(currentWidget.element).find('img[data-localid="' + localid + '"]');
              if (event.lengthComputable) {
                percent = Math.ceil((position / total) * 100);
              }
              if (percent >= 100) {
                $img.removeClass("htmltext-img-transferring");
                styleTag.remove();
              } else {
                styleTag.textContent =
                  'img.htmltext-img-transferring[data-localid="' + localid + '"] { opacity: ' + percent / 100 + "}";
              }
            },
            false
          );
        }
        return xhrObject;
      }
    })
      .done(function wFileUploadDone(data) {
        const dataFile = data.data.file;
        const event = { prevent: false };
        const fileValue = {
          value: dataFile.reference,
          size: dataFile.size,
          fileName: dataFile.fileName,
          displayValue: dataFile.fileName,
          creationDate: dataFile.cdate,
          thumbnail: dataFile.thumbnailUrl,
          url: dataFile.downloadUrl,
          icon: dataFile.iconUrl
        };

        const $img = $(currentWidget.element).find('img[data-localid="' + localid + '"]');

        $img.attr("src", fileValue.url);
        $img.removeAttr("data-localid");
        $img.attr("data-tmpvid", dataFile.id);
        currentWidget.setValue({ value: currentWidget.kendoEditorInstance.value() });

        _.defer(function wHtmltext_uploadImageDone() {
          currentWidget._trigger("uploadfiledone", event, {
            $el: currentWidget.element,
            index: currentWidget._getIndex(),
            file: fileValue
          });
        });
      })
      .fail(function wFileUploadFail(data) {
        const $img = $(currentWidget.element).find('img[data-localid="' + localid + '"]');
        currentWidget.uploadingFiles--;
        currentWidget._trigger("uploadfiledone", event, {
          $el: currentWidget.element,
          index: currentWidget._getIndex(),
          file: null
        });
        try {
          const result = JSON.parse(data.responseText);
          _.each(result.messages, function wFileErrorMessages(errorMessage) {
            if (errorMessage.type === "error") {
              currentWidget._trigger("uploadfileerror", event, {
                index: currentWidget._getIndex(),
                message: errorMessage.contentText
              });
            }
          });
        } catch (e) {
          currentWidget._trigger("uploadfileerror", event, {
            index: currentWidget._getIndex(),
            message: data.responseText || i18n.___("Your navigator seems offline, try later", "ddui")
          });
        }
        $img.remove();
      });

    return $img[0].outerHTML;
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

        this.kendoEditorInstance.value(value.value || "<p></p>");
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
