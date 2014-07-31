define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpFile", $.dcp.dcpText, {

        options: {
            id: "",
            type: "file"
        },

        _initDom: function () {
            var urlSep = '?';
            if (this.options.value.url) {
                if (!this.options.renderOptions.htmlLink.url) {
                    if (this.options.value.url && this.options.renderOptions.downloadInline) {
                        urlSep = (this.options.value.url.indexOf('?') >= 0) ? "&" : "?";
                        this.options.value.url += urlSep + 'inline=yes';
                    }
                    this.options.renderOptions.htmlLink.url = this.options.value.url;

                    if (!this.options.renderOptions.htmlLink.title) {
                        this.options.renderOptions.htmlLink.title = this.options.value.displayValue;
                    }
                }
            }
            this._super();
        },

        _initEvent: function wFileInitEvent() {
            if (this.getMode() === "write") {
                this._initUploadEvent();
            }

            this._super();
        },

        _initUploadEvent: function wFileInitUploadEvent() {
            var scope=this;
            this.element.find(".dcpAttribute__content__button--file").on("click", function (event) {
                scope.element.find("input[type=file]").trigger("click");
            });

            this.element.on("dragenter", function (event) {
event.stopPropagation();
  event.preventDefault();
            });
            this.element.on("dragover", function (event) {
event.stopPropagation();
  event.preventDefault();
                scope.element.addClass("dcpAttribute__content--dropzone");
            });
            this.element.on("dragleave", function (event) {
event.stopPropagation();
  event.preventDefault();
                scope.element.removeClass("dcpAttribute__content--dropzone");
            });

            this.element.on("drop", function (event) {
event.stopPropagation();
  event.preventDefault();
                console.log("DROP", event);
            });


        },


        /**
         * Return the url of common link
         * @returns {*}
         */
        getLink: function getLink() {
            var link = this._super();
            if (!link || !link.url) {
                link.url = this.options.value.url;
            }

            return link;
        },


        getType: function () {
            return "file";
        }

    });
});