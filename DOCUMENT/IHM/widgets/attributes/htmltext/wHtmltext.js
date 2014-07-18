define([
    'underscore',
    'mustache',
    'kendo',
    '../wAttribute',
    'widgets/attributes/text/wText'
], function (_, Mustache, kendo) {
    'use strict';

    $.widget("dcp.dcpHtmltext", $.dcp.dcpText, {

        options: {
            id: "",
            type: "htmltext"
        },

        getType: function () {
            return "htmltext";
        }

    });
});