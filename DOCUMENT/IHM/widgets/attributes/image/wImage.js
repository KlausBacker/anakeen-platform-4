define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/file/wFile'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpImage", $.dcp.dcpFile, {

        options: {
            id: "",
            type: "image"
        },


        _initDom: function () {
            if (this.getMode() === "read") {
            var urlSep='?';
            if (this.options.value.url) {
                if (!this.options.renderOptions.htmlLink.url) {

                    if (this.options.renderOptions.thumbnailWidth > 0) {
                        urlSep= (this.options.value.thumbnail.indexOf('?')>=0) ? "&" : "?";
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



        getType: function () {
            return "image";
        }

    });
});