(function umdRequire(root, factory) {
  "use strict";

  if (typeof define === "function" && define.amd) {
    define(["jquery", "dcpDocument/widgets/attributes/file/wFile"], factory);
  } else {
    //noinspection JSUnresolvedVariable
    factory(window.jQuery, window.Mustache);
  }
})(window, function wImageWidget($) {
  "use strict";

  $.widget("dcp.dcpImage", $.dcp.dcpFile, {
    options: {
      type: "image",
      labels: {
        dropFileHere: "Drop image here",
        placeHolder: "Click to upload an image",
        tooltipLabel: "Choose image",
        downloadLabel: "Download the image"
      },
      renderOptions: {
        thumbnailSize: "100x100"
      }
    },

    _initDom: function wImageInitDom() {
      if (this.getMode() === "read") {
        if (this.options.attributeValue.url) {
          if (!this.options.renderOptions.htmlLink.url) {
            if (this.options.renderOptions.thumbnailSize) {
              var reSize = /sizes\/([^/]+)/;
              this.options.attributeValue.thumbnail =
                this.options.attributeValue.thumbnail.replace(
                  reSize,
                  "sizes/" + this.options.renderOptions.thumbnailSize
                ) + ".png";
            } else if (!this.options.renderOptions.thumbnailSize) {
              this.options.attributeValue.thumbnail = this.options.attributeValue.url;
            }
          }
        }
      }
      if (this.options.attributeValue.thumbnail) {
        this.options.attributeValue.hash = this.options.attributeValue.creationDate.replace(
          /[ :-]/g,
          ""
        );
        this.options.attributeValue.thumbnail +=
          "?c=" + this.options.attributeValue.hash;
      }
      this._super();
    },

    _initEvent: function wFileInitEvent() {
      this._super();
      if (this.getMode() === "read") {
        this._initDisplayEvent();
      }
    },

    _initDisplayEvent: function wImageinitDisplayEvent() {
      var scope = this;
      var htmlLink = this.getLink();
      this.element.off("click");
      this.element.on(
        "click" + this.eventNamespace,
        ".dcpAttribute__content__link",
        function wImageClick(event) {
          if (htmlLink.target === "_dialog") {
            event.preventDefault();
            var bdw = $(
              '<div class="dcpImage-window"><img class="img-responsive" src="' +
                $(this).attr("href") +
                '"/></div>'
            );
            $("body").append(bdw);
            // $(this).attr("href"),
            var dw = bdw.kendoWindow({
              title: scope.options.attributeValue.displayValue,
              width: htmlLink.windowWidth,
              height: htmlLink.windowHeight,
              iframe: false,
              actions: ["Maximize", "Close"]
            });

            dw.data("kendoWindow")
              .center()
              .open();
          }
        }
      );
    },

    /**
     * Condition before upload file
     * @returns {boolean}
     */
    uploadCondition: function wImageUploadCondition(file) {
      if (file.type.substr(0, 5) !== "image") {
        this.setError("Invalid image file");
        return false;
      }
      this.setError(null);
      return true;
    },

    getType: function wImageGetType() {
      return "image";
    }
  });

  return $.fn.dcpImage;
});
