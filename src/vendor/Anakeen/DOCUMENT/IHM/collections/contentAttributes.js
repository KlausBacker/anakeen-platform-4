/*global define*/
define(["underscore", "backbone"], function(_, Backbone) {
  "use strict";

  return Backbone.Collection.extend({
    comparator: "logicalOrder",

    toData: function(index, extended) {
      var elements = [];
      this.each(function(currentAttribute) {
        elements.push(currentAttribute.toData(index, extended));
      });
      return elements;
    },

    destroy: function() {
      var model;
      while ((model = this.first())) {
        // jshint ignore:line
        model.destroy();
      }
    },

    propageEvent: function(eventName) {
      this.each(function(currentModel) {
        currentModel.trigger(eventName);
        if (currentModel.get("content")) {
          currentModel.get("content").propageEvent(eventName);
        }
      });
    }
  });
});
