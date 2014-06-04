define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    return Backbone.Collection.extend({

        toData : function() {
            var elements = [];
            this.each(function(currentAttribute) {
                elements.push(currentAttribute.toData());
            });
            return elements;
        }
    });
});