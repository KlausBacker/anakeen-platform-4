define([
    'underscore',
    'backbone',
    'models/menu'
], function (_, Backbone, ModelMenu) {
    'use strict';

    return Backbone.Collection.extend({
        model : ModelMenu
    });
});