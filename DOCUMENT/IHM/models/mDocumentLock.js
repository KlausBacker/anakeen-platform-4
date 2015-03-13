define([
    "underscore",
    "backbone"
], function (_, Backbone) {
    "use strict";

    var url = _.template("api/v1/documents/<%- initid %>/locks/<%- type %>");

    return Backbone.Model.extend({

        idAttribute: "initid",

        url : function mDocumentLock_url() {
            return url({
                initid : this.get("initid"),
                type : this.get("type")
            });
        }
    });
});