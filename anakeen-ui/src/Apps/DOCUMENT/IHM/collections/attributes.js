/*global define*/
define([
  "underscore",
  "backbone",
  "dcpDocument/models/mAttributeData",
  "dcpDocument/models/mAttributeStructural",
  "dcpDocument/models/mAttributeArray"
], function(
  _,
  Backbone,
  ModelAttributeData,
  ModelAttributeStructural,
  ModelAttributeArray
) {
  "use strict";

  return Backbone.Collection.extend({
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
});
