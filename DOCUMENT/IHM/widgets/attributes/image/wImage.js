define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpImage", $.dcp.dcpText, {

        options: {
            id: "",
            type: "image"
        },


        _initDom: function () {
            var urlSep='?';
            if (this.options.value.url) {
                if (!this.options.renderOptions.htmlLink.url) {
                    if (this.options.value.url && this.options.renderOptions.downloadInline) {
                        urlSep= (this.options.value.url.indexOf('?')>=0) ? "&" : "?";
                        this.options.value.url += urlSep + 'inline=yes';
                    }
                    this.options.renderOptions.htmlLink.url=this.options.value.url;
                    if (this.options.renderOptions.thumbnailWidth > 0) {
                        urlSep= (this.options.value.thumbnail.indexOf('?')>=0) ? "&" : "?";
                        this.options.value.thumbnail += urlSep
                            + "size=" + parseInt(this.options.renderOptions.thumbnailWidth)
                            + "&width=" + parseInt(this.options.renderOptions.thumbnailWidth)
                        ;
                    } else if (this.options.renderOptions.thumbnailWidth === 0) {
                        this.options.value.thumbnail = this.options.value.url;
                    }
                    if (! this.options.renderOptions.htmlLink.title) {
                        this.options.renderOptions.htmlLink.title=this.options.value.displayValue;
                    }
                    console.log("image option", this.options);
                }
            }
            this._super();
        },
        /**
         * Return the url of common link
         * @returns {*}
         */
        getLink: function getLink() {
            var link = this._super();
            if (!link || !link.url) {
                console.log("Image options", this.options);
                link.url = this.options.value.url;
            }

            return link;
        },


        getType: function () {
            return "image";
        }

    });
});