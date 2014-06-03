define([
    'underscore',
    'backbone',
    'models/attribute'
], function (_, Backbone, ModelAttribute) {
    'use strict';

    return Backbone.Collection.extend({
        model : ModelAttribute
    });
});