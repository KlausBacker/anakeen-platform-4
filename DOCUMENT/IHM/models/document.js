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

    var flattenAttributes = function (currentAttributes, attributes, parent) {
        if (!_.isArray(attributes)) {
            attributes = _.values(attributes);
        }
        if (parent) {
            _.each(attributes, function (value) {
                value.parent = parent;
            });
        }
        _.each(attributes, function (value) {
            value.documentMode = window.dcp.renderOptions.mode || "read";
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

        initialize: function (values, options) {
            var attributes = [];
            this.id = options.properties.id;
            this.set("properties", new DocumentProperties(options.properties));
            this.set("menus", new CollectionMenus(options.menus));
            attributes = flattenAttributes(attributes, options.family.structure);
            _.each(attributes, function (value) {
                if (value.id && options.attributes[value.id]) {
                    value.value = options.attributes[value.id];
                }
            });
            this.set("attributes", new CollectionAttributes(attributes));
            attributes = this.get("attributes");
            attributes.each(function (currentAttributeModel) {
                currentAttributeModel.setContentCollection(attributes);
            });
            this.listenTo(this.get("attributes"), "change:value", this.notifyChange);
        },

        toData: function () {
            var returnObject = {
                document: {}
            };
            returnObject.document.properties = this.get("properties").toJSON();
            returnObject.menus = this.get("menus").toJSON();
            return returnObject;
        },

        notifyChange: function (attribute, newValue) {
            //console.log(arguments);
            //debugger;
        },

        getValues: function () {
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
                            arrayValues.push(currentValue[i] || { value: null});
                        }
                    } else {
                        arrayValues = { value: null};
                    }
                    values[currentAttribute.id] = arrayValues;
                } else {
                    values[currentAttribute.id] = currentValue;
                }
            });
            return values;
        },

        hasAttributesChanged: function () {
            return this.get("attributes").some(function (currentAttr) {
                return currentAttr.hasChanged("value");
            });
        },

        // add new attribute error
        addErrorMessage: function (message) {
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
                                title: message.contentText,
                                htmlMessage: message.contentHtml,
                                message: attrModel.attributes.label + ' : ' + message.data.err});
                        } else {
                            $notification.dcpNotification("showError", {
                                title: message.contentText,
                                htmlMessage: message.contentHtml,
                                message: message.data.err});
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
                                    title: message.contentText,
                                    htmlMessage: message.contentHtml,
                                    message: attrModel.attributes.label + ' : ' + constraint.err});
                            } else {
                                $notification.dcpNotification("showError", {
                                    title: message.contentText,
                                    htmlMessage: message.contentHtml,
                                    message: message.constraint.err});
                            }
                        });
                    }
                    if (message.data && message.data.preStore) {
                        $notification.dcpNotification("showError", {
                            title: message.contentText,
                            htmlMessage: message.contentHtml,
                            message: message.data.preStore});
                    }
                    break;

                default:
                    window.alert(message.code);
            }
        },
        /**
         * Verify
         * @returns {boolean}
         */
        verifyAndNotifyNeededAttributes: function () {
            var $notification = $('body').dcpNotification();
            var success = true;
            var scope = this;
            var attrLabel = [];
            this.get("attributes").each(function (currentAttribute) {
                if (currentAttribute.get("needed") === true) {
                    console.log("verify", currentAttribute.id, currentAttribute);
                    var currentValue = currentAttribute.get("value");
                    var parentAttribute = scope.get("attributes").get(currentAttribute.get("parent"));
                    var oneSuccess = true;
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
                    title: "Needed Attribute",
                    message: attrLabel.join(', ')});
                success = false;
            }
            return success;
        },


        clearErrorMessages: function () {
            var attrModels = this.get('attributes');
            _.each(attrModels.models, function (attrModel) {
                attrModel.setErrorMessage(null);
            });

        }
    });

});