define([
    'underscore',
    'backbone',
    'dcpDocument/models/mMenu'
], function (_, Backbone, ModelMenu) {
    'use strict';

    return Backbone.Collection.extend({
        model : ModelMenu,

        destroy : function () {
            this.invoke("trigger", "destroy");
        }
    });
});