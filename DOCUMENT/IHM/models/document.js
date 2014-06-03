/*global define*/
define([
    'underscore',
    'backbone',
    'models/documentProperties',
    'collections/attributes',
    'collections/menus'
], function (_, Backbone, DocumentProperties, CollectionAttributes, CollectionMenus) {
    'use strict';

    var flattenAttributes = function (currentAttributes, attributes, parent) {
        if (!_.isArray(attributes)) {
            attributes = _.values(attributes);
        }
        if (parent) {
            _.each(attributes, function(value) {
                value.parent = parent;
            });
        }
        _.each(attributes, function (value) {
            value.documentMode = dcp.renderOptions.mode || "read";
        });
        currentAttributes = _.union(currentAttributes, attributes);
        _.each(attributes, function (currentAttr) {
            if (currentAttr.content) {
                currentAttributes = _.union(currentAttributes, flattenAttributes(currentAttributes, currentAttr.content, currentAttr.id));
            }
        });
        return currentAttributes;
    };

    return Backbone.Model.extend({

        initialize : function(values, options) {
            var attributes = [];
            this.set("properties", new DocumentProperties(options.properties));
            this.set("menus", new CollectionMenus(options.menus));
            attributes = flattenAttributes(attributes, options.family.structure);
            _.each(attributes, function(value) {
                if (value.id && options.attributes[value.id]) {
                    _.extend(value, options.attributes[value.id]);
                }
            });
            this.set("attributes", new CollectionAttributes(attributes));
            attributes = this.get("attributes");
            attributes.each(function(currentAttributeModel) {
               currentAttributeModel.setContentCollection(attributes);
            });
        },

        toData : function() {
            var returnObject = {
                document : {}
            };
            returnObject.document.properties = this.get("properties").toJSON();
            returnObject.menus = this.get("menus").toJSON();
            return returnObject;
        }
    });

});