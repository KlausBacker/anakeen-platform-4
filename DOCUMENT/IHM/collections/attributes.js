/*global define*/
define([
    'underscore',
    'backbone',
    'models/mAttribute'
], function (_, Backbone, ModelAttribute) {
    'use strict';

    return Backbone.Collection.extend({
        comparator : "logicalOrder",
        model : ModelAttribute,

        initialize : function initialize(values, options) {
            this.documentModel = options.documentModel;
            this.renderOptions = options.renderOptions;
            this.renderMode = options.renderMode;
        },

        destroy : function() {
            this.invoke("trigger", "destroy");
            delete this.documentModel;
            delete this.renderOptions;
            delete this.renderMode;
        }
    });
});