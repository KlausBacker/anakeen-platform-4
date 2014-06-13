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
            this.id = options.properties.id;
            this.set("properties", new DocumentProperties(options.properties));
            this.set("menus", new CollectionMenus(options.menus));
            attributes = flattenAttributes(attributes, options.family.structure);
            _.each(attributes, function(value) {
                if (value.id && options.attributes[value.id]) {
                    value.value = options.attributes[value.id];
                }
            });
            this.set("attributes", new CollectionAttributes(attributes));
            attributes = this.get("attributes");
            attributes.each(function(currentAttributeModel) {
               currentAttributeModel.setContentCollection(attributes);
            });
            this.listenTo(this.get("attributes"), "change:value", this.notifyChange);
        },

        toData : function() {
            var returnObject = {
                document : {}
            };
            returnObject.document.properties = this.get("properties").toJSON();
            returnObject.menus = this.get("menus").toJSON();
            return returnObject;
        },

        notifyChange : function(attribute, newValue) {
            //console.log(arguments);
            //debugger;
        },

        getValues : function() {
            var values = {};
            this.get("attributes").each(function(currentAttribute) {
                var currentValue = currentAttribute.get("value"), nbLines, i, arrayValues = [];
                if (!currentAttribute.get("valueAttribute")) {
                    return;
                }
                if (currentAttribute.get("multiple")) {
                    nbLines = currentAttribute.getNbLines();
                    for (i = 0; i <= nbLines; i++) {
                        arrayValues.push(currentValue[i] || { value : null});
                    }
                    values[currentAttribute.id] = arrayValues;
                    return;
                }
                values[currentAttribute.id] = currentValue;
            });
            return values;
        },

        hasAttributesChanged : function() {
            return this.get("attributes").some(function(currentAttr) {
                return currentAttr.hasChanged("value");
            })
        }
    });

});