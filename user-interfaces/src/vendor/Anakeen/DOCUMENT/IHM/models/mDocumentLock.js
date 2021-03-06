import _ from "underscore";
import Backbone from "backbone";

const urlCore = _.template("/api/v2/smart-elements/<%- initid %>/locks/<%- type %>");
const urlView = _.template("/api/v2/smart-elements/<%- initid %>/views/<%- viewId %>/locks/<%- type %>");

export default Backbone.Model.extend({
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
