define(["underscore", "backbone"], function require_lock(_, Backbone) {
  "use strict";

  var urlCore = _.template("api/v2/documents/<%- initid %>/locks/<%- type %>");
  var urlView = _.template(
    "api/v2/documents/<%- initid %>/views/<%- viewId %>/locks/<%- type %>"
  );

  return Backbone.Model.extend({
    typeModel: "ddui:lock",
    idAttribute: "initid",
    viewId: "",

    url: function mDocumentLock_url() {
      //console.log("lock model", this.get("viewId"));
      if (this.get("viewId")) {
        return urlView({
          initid: this.get("initid"),
          type: this.get("type"),
          viewId: this.get("viewId")
        });
      } else {
        return urlCore({
          initid: this.get("initid"),
          type: this.get("type")
        });
      }
    }
  });
});
