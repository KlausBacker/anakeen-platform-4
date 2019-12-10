import Backbone from "backbone";
import ModelAttributeData from "../models/mAttributeData";
import ModelAttributeStructural from "../models/mAttributeStructural";
import ModelAttributeArray from "../models/mAttributeArray";

export default Backbone.Collection.extend({
  comparator: "logicalOrder",

  model: function CollectionAttributesSelectModel(attributes, options) {
    if (attributes.type === "array") {
      return new ModelAttributeArray(attributes, options);
    }
    if (attributes.type === "tab" || attributes.type === "frame") {
      return new ModelAttributeStructural(attributes, options);
    }
    return new ModelAttributeData(attributes, options);
  },

  initialize: function CollectionAttributes_initialize(values, options) {
    this.documentModel = options.documentModel;
    this.renderOptions = options.renderOptions;
    this.renderMode = options.renderMode;
  },

  destroy: function CollectionAttributes_destroy() {
    var model;
    while ((model = this.first())) {
      // jshint ignore:line
      model.destroy();
    }
    delete this.documentModel;
    delete this.renderOptions;
    delete this.renderMode;
  }
});
