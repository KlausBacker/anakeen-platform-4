import _ from "underscore";
import Backbone from "backbone";

export default Backbone.Model.extend({
  typeModel: "ddui:familyStructure",
  idAttribute: "familyId",

  url: function mFamilyStructure_url() {
    var urlStructure = _.template("/api/v2/smart-structures/<%- familyId %>/views/structure");

    return urlStructure({
      familyId: this.get("familyId")
    });
  }
});
