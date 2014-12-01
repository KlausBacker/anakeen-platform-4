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
        currentAttributes = _.union(currentAttributes, attributes);
        _.each(attributes, function (currentAttr) {
            if (currentAttr.content) {
                currentAttributes = _.union(currentAttributes, flattenAttributes(currentAttributes, currentAttr.content, currentAttr.id));
            }
        });
        return currentAttributes;
    };

    return Backbone.Model.extend({

        idAttribute : "initid",

        defaults : {
            revisionId : false,
            renderMode : "read",
            properties : undefined,
            menus :      undefined,
            attributes : undefined
        },

        url : function () {
            var urlData = "api/v1/documents/" + encodeURIComponent(this.id);
            if (this.get("revision")) {
                urlData += "/revisions/" + encodeURIComponent(this.get("revision"));
            }
            urlData += "/views/" + encodeURIComponent(this.get("viewId"));
            return urlData;
        },

        initialize : function () {
            this.listenTo(this, "error", this.propagateSynchroError);
        },

        toData : function () {
            var returnObject = {
                document : {}
            };
            returnObject.document.properties = this.get("properties").toJSON();
            returnObject.menus = this.get("menus").toJSON();
            returnObject.templates = this.get("templates");
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
                            arrayValues.push(currentValue[i] || {value : null});
                        }
                    } else {
                        arrayValues = {value : null};
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
                // reset change also
                currentAttribute.changed = {};
            });
        },

        /**
         * reset all properties with a new set of properties
         */
        setProperties : function documentSetProperties(values) {
            var model = this;
            _.each(values, function (value, key) {
                model.get("properties").set(key, value);
            });
        },

        hasAttributesChanged :  function () {
            return this.get("attributes").some(function (currentAttr) {
                return currentAttr.hasChanged("value");
            });
        },

        /**
         * Analyze return in case of sync uncomplete and trigger event error
         *
         * @param model
         * @param xhr
         * @param options
         */
        propagateSynchroError : function (model, xhr, options) {
            var attrModel, currentModel = this, parsedReturn;
            //Analyze XHR
            var messages = [];
            try {
                var result = JSON.parse(xhr.responseText);
                messages = result.messages;
            } catch (e) {
                //Unable to parse responseText (error is not in JSON)
            }

            parsedReturn = {
                messages : messages,
                responseText : "Unexpected error: " + xhr.status + " " + xhr.statusText
            };

            if (parsedReturn.messages.length === 0) {
                //Status 0 indicate offline browser
                if (xhr.status === 0) {
                    parsedReturn.responseText = "Your navigator seems offline, try later";
                }
                currentModel.trigger("showError", {
                    "title" : "Unable to synchronise " + currentModel.get("properties").get("title"),
                    "message" :     parsedReturn.responseText
                });
            }
            _.each(parsedReturn.messages, function (message) {
                switch (message.code) {
                    case "CRUD0211":// Syntax Error
                        if (message.data && message.data.id) {
                            attrModel = this.get('attributes').get(message.data.id);
                            if (attrModel) {
                                attrModel.setErrorMessage(message.data.err, message.data.index);
                                currentModel.trigger("showError", {
                                    title :       message.contentText,
                                    htmlMessage : message.contentHtml,
                                    message : attrModel.attributes.label + ' : ' + message.data.err
                                });
                            } else {
                                currentModel.trigger("showError", {
                                    title :       message.contentText,
                                    htmlMessage : message.contentHtml,
                                    message :     message.data.err
                                });
                            }
                        }
                        break;
                    case "CRUD0212": // Constraint Error
                        if (message.data && message.data.constraint) {
                            _.each(message.data.constraint, function (constraint, aid) {
                                attrModel = currentModel.get('attributes').get(constraint.id);
                                if (attrModel) {
                                    attrModel.setErrorMessage(constraint.err, constraint.index);
                                   currentModel.trigger("showError", {
                                        title :       message.contentText,
                                        htmlMessage : message.contentHtml,
                                        message : attrModel.attributes.label + ' : ' + constraint.err
                                    });
                                } else {
                                   currentModel.trigger("showError", {
                                        title :       message.contentText,
                                        htmlMessage : message.contentHtml,
                                        message :     constraint.err
                                    });
                                }
                            });
                        }
                        if (message.data && message.data.preStore) {
                           currentModel.trigger("showError", {
                                title :       message.contentText,
                                htmlMessage : message.contentHtml,
                                message :     message.data.preStore
                            });
                        }
                        break;

                    default:
                        if (message.type === "error" && message.contentText) {
                           currentModel.trigger("showError", {
                                title :       message.contentText,
                                htmlMessage : message.contentHtml
                            });
                        } else {
                            console.error("Error", message);
                        }
                }
            });
        },

        /**
         * Validate the content of the model before synchro
         */
        validate : function () {
            var success = true,
                currentDocument = this,
                attrLabel = [];
            this.get("attributes").each(function (currentAttribute) {
                if (currentAttribute.get("needed") === true) {
                    var currentValue = currentAttribute.get("value"),
                        parentAttribute = currentDocument.get("attributes").get(currentAttribute.get("parent")),
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
                return {
                    title :   "Needed Attribute",
                    message : attrLabel.join(', ')
                };
            }
            return undefined;
        },

        /**
         * Propagate to attributes a clear message for the error displayed
         */
        clearErrorMessages : function () {
            var attrModels = this.get('attributes');
            _.each(attrModels.models, function (attrModel) {
                attrModel.setErrorMessage(null);
            });

        },

        /**
         * Parse the return of the REST API
         * @param response
         * @returns {{properties: (*|properties|exports.defaults.properties|exports.parse.properties|.createObjectExpression.properties|AST_Object.$propdoc.properties), menus: (app.views.shared.menu|*), locale: *, renderMode: string, attributes: Array, templates: *, renderOptions: *}}
         */
        parse : function parse(response) {
            var attributes = [], renderMode = "view", structureAttributes, valueAttributes, visibilityAttributes;
            if (response.success === false) {
                throw new Error("Unable to get the data from documents");
            }
            if (response.data.view.renderOptions.mode) {
                if (response.data.view.renderOptions.mode === "edit") {
                    renderMode = "edit";
                } else if (response.data.view.renderOptions.mode === "view") {
                    renderMode = "view";
                } else {
                    throw new Error("Unkown render mode " + response.data.view.renderOptions.mode);
                }
            }
            valueAttributes = response.data.view.documentData.document.attributes;
            visibilityAttributes = response.data.view.renderOptions.visibilities;
            structureAttributes = response.data.view.documentData.family.structure;
            attributes = flattenAttributes(attributes, structureAttributes);
            _.each(attributes, function (currentAttributeStructure) {
                if (currentAttributeStructure.id && valueAttributes[currentAttributeStructure.id]) {
                    currentAttributeStructure.value = valueAttributes[currentAttributeStructure.id];
                }
                if (currentAttributeStructure.id && visibilityAttributes[currentAttributeStructure.id]) {
                    currentAttributeStructure.visibility = visibilityAttributes[currentAttributeStructure.id];
                }
            });
            return {
                properties :    response.data.view.documentData.document.properties,
                menus :         response.data.view.menu,
                locale :        response.data.view.locale.culture,
                renderMode : renderMode || "view",
                attributes :    attributes,
                templates :     response.data.view.templates,
                renderOptions : response.data.view.renderOptions
            };
        },

        "set" : function setValues(attributes, options) {
            var currentModel = this;
            if (attributes.properties !== undefined) {
                if (currentModel.get("properties") instanceof DocumentProperties) {
                    currentModel.get("properties").destroy();
                }
                attributes.properties = new DocumentProperties(attributes.properties);

            }
            if (attributes.menus !== undefined) {
                if (currentModel.get("menus") instanceof CollectionMenus) {
                    currentModel.get("menus").invoke('destroy');
                }
                attributes.menus = new CollectionMenus(attributes.menus);
            }
            if (attributes.attributes !== undefined) {
                if (currentModel.get("attributes") instanceof CollectionAttributes) {
                    currentModel.get("attributes").invoke('destroy');
                    currentModel.get("attributes").destroy();
                }
                attributes.attributes = new CollectionAttributes(attributes.attributes, {
                    documentModel : currentModel,
                    renderOptions : attributes.renderOptions,
                    renderMode :    attributes.renderMode
                });
                attributes.attributes.each(function (currentAttributeModel) {
                    currentAttributeModel.setContentCollection(attributes.attributes);
                });
            }
            return Backbone.Model.prototype.set.call(this, attributes, options);
        },

        toJSON : function () {
            return {document : {attributes : this.getValues()}};
        }
    });

});