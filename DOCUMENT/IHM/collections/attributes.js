/*global define*/
define([
    'underscore',
    'backbone',
    'models/mAttributeData',
    'models/mAttributeStructural',
    'models/mAttributeArray'
], function (_, Backbone, ModelAttributeData, ModelAttributeStructural, ModelAttributeArray) {
    'use strict';

    return Backbone.Collection.extend({
        comparator : "logicalOrder",

        model :      function CollectionAttributesSelectModel(attributes, options) {
            if (attributes.type === "array") {
                return new ModelAttributeArray(attributes, options);
            }
            if (attributes.type === "tab" || attributes.type === "frame") {
                return new ModelAttributeStructural(attributes, options);
            }
            return new ModelAttributeData(attributes, options);
        },

        initialize : function initialize(values, options) {
            this.documentModel = options.documentModel;
            this.renderOptions = options.renderOptions;
            this.renderMode = options.renderMode;
        },

        destroy : function () {
            this.invoke("trigger", "destroy");
            delete this.documentModel;
            delete this.renderOptions;
            delete this.renderMode;
        }
    });
});