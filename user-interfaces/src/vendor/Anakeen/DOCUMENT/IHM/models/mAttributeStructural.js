import _ from "underscore";
import AttributeModel from "./mAttribute";
import CollectionContentAttributes from "../collections/contentAttributes";

export default AttributeModel.extend({
  typeModel: "ddui:structureAttribute",
  defaults: {
    content: []
  },

  setContentCollection: function mAttributesetContentCollection(attributes) {
    var collection = new CollectionContentAttributes();
    _.each(attributes, function(currentChild) {
      collection.push(currentChild);
    });
    this.set("content", collection);
  }
});
