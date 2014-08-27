define([
    'widgets/attributes/file/wFile'
], function () {
    'use strict';

    $.widget("dcp.dcpImage", $.dcp.dcpFile, {

        options : {
            type : "image",
            labels : {
                dropFileHere : "Drop image here",
                placeHolder : "Click to upload an image",
                tooltipLabel : "Choose image",
                downloadLabel: "Download the image"
            }
        },

        _initDom : function () {
            if (this.getMode() === "read") {
                var urlSep = '?';
                if (this.options.value.url) {
                    if (!this.options.renderOptions.htmlLink.url) {

                        if (this.options.renderOptions.thumbnailWidth > 0) {
                            urlSep = (this.options.value.thumbnail.indexOf('?') >= 0) ? "&" : "?";
                            this.options.value.thumbnail += urlSep +
                                "size=" + parseInt(this.options.renderOptions.thumbnailWidth) +
                                "&width=" + parseInt(this.options.renderOptions.thumbnailWidth);
                        } else if (this.options.renderOptions.thumbnailWidth === 0) {
                            this.options.value.thumbnail = this.options.value.url;
                        }

                    }
                }
            }
            this._super();
        },

        /**
         * Condition before upload file
         * @returns {boolean}
         */
        uploadCondition : function wImageUploadCondition(file) {
            if (file.type.substr(0, 5) !== "image") {
                this.setError("Invalid image file");
                return false;
            }
            this.setError(null);
            return true;
        },

        getType : function () {
            return "image";
        }

    });

    return $.fn.dcpImage;
});