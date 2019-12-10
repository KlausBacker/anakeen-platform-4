import _ from "underscore";
import Backbone from "backbone";

var url = _.template("/api/v2/smart-elements/<%- initid %>/usertags/lasttab");

export default Backbone.Model.extend({
  typeModel: "ddui:attributeTab",
  idAttribute: "initid",

  url: function mDocumentTag_url() {
    return url({ initid: this.get("initid") });
  },

  toJSON: function mDocumentTag_toJSON() {
    return this.get("tabId");
  }
});
