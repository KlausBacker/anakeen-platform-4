define([
    'mustache',
    'dcpDocument/widgets/attributes/file/wFile'
], function (Mustache) {
    'use strict';

    $.widget("dcp.dcpImage", $.dcp.dcpFile, {

        options: {
            type: "image",
            labels: {
                dropFileHere: "Drop image here",
                placeHolder: "Click to upload an image",
                tooltipLabel: "Choose image",
                downloadLabel: "Download the image"
            },
            renderOptions : {
                thumbnailWidth : 100
            }
        },

        _initDom: function () {
            if (this.getMode() === "read") {
                var urlSep = '?';
                if (this.options.attributeValue.url) {
                    if (!this.options.renderOptions.htmlLink.url) {

                        if (this.options.renderOptions.thumbnailWidth > 0) {
                            urlSep = (this.options.attributeValue.thumbnail.indexOf('?') >= 0) ? "&" : "?";
                            this.options.attributeValue.thumbnail += urlSep +
                            "size=" + parseInt(this.options.renderOptions.thumbnailWidth) +
                            "&width=" + parseInt(this.options.renderOptions.thumbnailWidth);
                        } else if (this.options.renderOptions.thumbnailWidth === 0) {
                            this.options.attributeValue.thumbnail = this.options.attributeValue.url;
                        }

                    }
                }
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
            this.element.on("click" + this.eventNamespace, '.dcpAttribute__content__link', function (event) {

                if (htmlLink.target === "_dialog") {
                    event.preventDefault();
                    var renderTitle;
                    var index = $(this).data("index");
                    if (typeof index !== "undefined" && index !== null) {
                        renderTitle = Mustache.render(htmlLink.windowTitle, scope.options.attributeValue[index]);
                    } else {
                        renderTitle = Mustache.render(htmlLink.windowTitle, scope.options.attributeValue);
                    }

                    var bdw = $('<div class="dcpImage-window"><img class="img-responsive" src="' + $(this).attr("href") + '"/></div>');
                    $('body').append(bdw);
                    // $(this).attr("href"),
                    var dw = bdw.kendoWindow({
                        title: scope.options.attributeValue.displayValue,
                        width: htmlLink.windowWidth,
                        height: htmlLink.windowHeight,
                        iframe: false,
                        actions: [
                            "Maximize",
                            "Close"
                        ]
                    });

                    dw.data("kendoWindow").center().open();
                }
            });
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

        getType: function () {
            return "image";
        }

    });

    return $.fn.dcpImage;
});