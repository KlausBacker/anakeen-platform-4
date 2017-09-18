define([
    "underscore",
    "dcpDocument/models/mAttribute",
    'dcpDocument/collections/contentAttributes'
], function (_, AttributeModel, CollectionContentAttributes) {
    'use strict';

    return AttributeModel.extend({
        typeModel:"ddui:structureAttribute",
        defaults : {
            content :      []
        },

        setContentCollection : function mAttributesetContentCollection(attributes) {
            var collection = new CollectionContentAttributes();
            _.each(attributes, function (currentChild) {
                collection.push(currentChild);
            });
            this.set("content", collection);
        }

    });
});