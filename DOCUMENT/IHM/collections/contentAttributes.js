define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    return Backbone.Collection.extend({

        comparator : "logicalOrder",

        toData : function() {
            var elements = [];
            this.each(function(currentAttribute) {
                elements.push(currentAttribute.toData());
            });
            return elements;
        },

        destroy : function () {
            this.invoke("trigger", "destroy");
        }
    });
});