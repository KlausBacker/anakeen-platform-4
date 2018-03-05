define([
    "underscore",
    "backbone"
], function (_, Backbone) {
    "use strict";

    var url = _.template("api/v2/documents/<%- initid %>/usertags/lasttab");

    return Backbone.Model.extend({

        typeModel:"ddui:attributeTab",
        idAttribute : "initid",

        url : function mDocumentTag_url() {
            return url({initid : this.get("initid")});
        },

        toJSON : function mDocumentTag_toJSON() {
            return this.get("tabId");
        }


    });
});