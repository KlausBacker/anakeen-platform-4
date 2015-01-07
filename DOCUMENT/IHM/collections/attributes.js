/*global define*/
define([
    'underscore',
    'backbone',
    'models/mAttribute',
    'models/mAttributeArray'
], function (_, Backbone, ModelAttribute, ModelAttributeArray) {
    'use strict';

    return Backbone.Collection.extend({
        comparator : "logicalOrder",

        model :      function CollectionAttributesSelectModel(attributes, options) {
            if (attributes.type === "array") {
                return new ModelAttributeArray(attributes, options);
            }
            return new ModelAttribute(attributes, options);
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