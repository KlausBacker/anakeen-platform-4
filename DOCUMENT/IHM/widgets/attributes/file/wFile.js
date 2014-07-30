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