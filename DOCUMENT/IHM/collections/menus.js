define([
    'underscore',
    'backbone',
    'models/mMenu'
], function (_, Backbone, ModelMenu) {
    'use strict';

    return Backbone.Collection.extend({
        model : ModelMenu,

        destroy : function () {
            this.invoke("trigger", "destroy");
        }
    });
});