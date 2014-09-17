/*global define*/
define([
    'underscore',
    'backbone',
    'models/documentProperties',
    'collections/attributes',
    'collections/menus',
    'widgets/window/wNotification'
], function (_, Backbone, DocumentProperties, CollectionAttributes, CollectionMenus) {
    'use strict';

    var flattenAttributes = function (mode, currentAttributes, attributes, parent) {
        if (!_.isArray(attributes)) {
            attributes = _.values(attributes);
        }
        if (parent) {
            _.each(attributes, function (value) {
                value.parent = parent;
            });
        }
        _.each(attributes, function (value) {
            value.documentMode = mode || "read";
        });
        currentAttributes = _.union(currentAttributes, attributes);
        _.each(attributes, function (currentAttr) {
            if (currentAttr.content) {
                currentAttributes = _.union(currentAttributes, flattenAttributes(mode, currentAttributes, currentAttr.content, currentAttr.id));
            }
        });
        return currentAttributes;
    };

    return Backbone.Model.extend({

        initialize : function (values, options) {
            var attributes = [],
                currentModel = this;
            this.id = options.properties.id;
            this.set("properties", new DocumentProperties(options.properties));
            this.set("menus", new CollectionMenus(options.menus));
            this.set("renderMode", options.renderMode);
            this.set("locale", options.locale);
            attributes = flattenAttributes(options.renderMode, attributes, options.family.structure);
            _.each(attributes, function (value) {
                if (value.id && options.attributes[value.id]) {
                    value.value = options.attributes[value.id];
                }
            });
            this.set("attributes", new CollectionAttributes(attributes));
            attributes = this.get("attributes");
            attributes.each(function (currentAttributeModel) {
                currentAttributeModel.setContentCollection(attributes, currentModel);
            });
        },

        toData : function () {
            var returnObject = {
                document : {}
            };
            returnObject.document.properties = this.get("properties").toJSON();
            returnObject.menus = this.get("menus").toJSON();
            return returnObject;
        },

        getValues : function documentGetValues() {
            var values = {};
            this.get("attributes").each(function (currentAttribute) {
                var currentValue = currentAttribute.get("value"), i, arrayValues = [];
                if (!currentAttribute.get("valueAttribute")) {
                    return;
                }
                if (currentAttribute.get("multiple")) {
                    currentValue = _.toArray(currentValue);
                    if (currentValue.length > 0) {
                        for (i = 0; i < currentValue.length; i++) {
                            arrayValues.push(currentValue[i] || { value : null});
                        }
                    } else {
                        arrayValues = { value : null};
                    }
                    values[currentAttribute.id] = arrayValues;
                } else {
                    values[currentAttribute.id] = currentValue;
                }
            });
            return values;
        },

        /**
         * reset all values with a new set of values
         */
        setValues : function documentSetValues(values) {
            this.get("attributes").each(function (currentAttribute) {
                var newValue = values[currentAttribute.id];
                if (!currentAttribute.get("valueAttribute")) {
                    return;
                }
                currentAttribute.set("value", newValue);
            });
        },

        /**
         * reset all properties with a new set of properties
         */
        setProperties : function documentSetProperties(values) {
            var model=this;
            _.each(values , function (value, key) {
                model.get("properties").set(key, value);
            });
        },
        hasAttributesChanged :            function () {
            return this.get("attributes").some(function (currentAttr) {
                return currentAttr.hasChanged("value");
            });
        },

        // add new attribute error
        addErrorMessage :                 function (message) {
            var attrModel;
            var scope = this;
            var $notification = $('body').dcpNotification();
            switch (message.code) {
                case "API0211":// Syntax Error
                    if (message.data && message.data.id) {
                        attrModel = this.get('attributes').get(message.data.id);
                        if (attrModel) {
                            attrModel.setErrorMessage(message.data.err, message.data.index);
                            $notification.dcpNotification("showError", {
                                title :       message.contentText,
                                htmlMessage : message.contentHtml,
                                message :     attrModel.attributes.label + ' : ' + message.data.err});
                        } else {
                            $notification.dcpNotification("showError", {
                                title :       message.contentText,
                                htmlMessage : message.contentHtml,
                                message :     message.data.err});
                        }
                    }
                    break;
                case "API0212": // Constraint Error
                    if (message.data && message.data.constraint) {
                        _.each(message.data.constraint, function (constraint, aid) {
                            attrModel = scope.get('attributes').get(aid);
                            if (attrModel) {
                                attrModel.setErrorMessage(constraint.err, constraint.index);
                                $notification.dcpNotification("showError", {
                                    title :       message.contentText,
                                    htmlMessage : message.contentHtml,
                                    message :     attrModel.attributes.label + ' : ' + constraint.err});
                            } else {
                                $notification.dcpNotification("showError", {
                                    title :       message.contentText,
                                    htmlMessage : message.contentHtml,
                                    message :     message.constraint.err});
                            }
                        });
                    }
                    if (message.data && message.data.preStore) {
                        $notification.dcpNotification("showError", {
                            title :       message.contentText,
                            htmlMessage : message.contentHtml,
                            message :     message.data.preStore});
                    }
                    break;

                default:
                    if (message.contentText) {
                        $notification.dcpNotification("showError", {
                            title :       message.contentText,
                            htmlMessage : message.contentHtml

                        });
                    } else {
                        console.error("Error", message);
                    }
            }
        },
        /**
         * Verify
         * @returns {boolean}
         */
        verifyAndNotifyNeededAttributes : function () {
            var $notification = $('body').dcpNotification(),
                success = true,
                scope = this,
                attrLabel = [];
            this.get("attributes").each(function (currentAttribute) {
                if (currentAttribute.get("needed") === true) {
                    var currentValue = currentAttribute.get("value"),
                        parentAttribute = scope.get("attributes").get(currentAttribute.get("parent")),
                        oneSuccess = true;
                    if (currentAttribute.get("multiple")) {
                        if (!currentValue || currentValue.length === 0) {
                            oneSuccess = false;
                        }
                    } else {
                        if (!currentValue || !currentValue.value) {
                            currentAttribute.setErrorMessage("Empty value not allowed");
                            oneSuccess = false;
                        }
                    }
                    if (!oneSuccess) {
                        attrLabel.push(parentAttribute.get('label') + ' / ' + currentAttribute.get("label"));
                        success = false;
                    }

                }
            });
            if (!success) {
                $notification.dcpNotification("showError", {
                    title :   "Needed Attribute",
                    message : attrLabel.join(', ')});
                success = false;
            }
            return success;
        },

        clearErrorMessages : function () {
            var attrModels = this.get('attributes');
            _.each(attrModels.models, function (attrModel) {
                attrModel.setErrorMessage(null);
            });

        }
    });

});