/*global define*/
define([
    'underscore',
    'backbone',
    'models/attribute'
], function (_, Backbone, ModelAttribute) {
    'use strict';

    return Backbone.Collection.extend({
        comparator : "logicalOrder",
        model : ModelAttribute,

        initialize : function initialize(values, options) {
            this.documentModel = options.documentModel;
            this.renderOptions = options.renderOptions;
            this.renderMode = options.renderMode;
        }
    });
});