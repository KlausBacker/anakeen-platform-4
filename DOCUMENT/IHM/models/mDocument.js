/*global define*/
define([
    'underscore',
    'backbone',
    'models/mDocumentProperties',
    'collections/attributes',
    'collections/menus',
    'widgets/window/wNotification'
], function (_, Backbone, DocumentProperties, CollectionAttributes, CollectionMenus) {
    'use strict';

    var flattenAttributes = function mDocumentflattenAttributes(currentAttributes, attributes, parent) {
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
            revision :   -1,
            viewId :     undefined,
            renderMode : "view",
            properties : undefined,
            menus :      undefined,
            attributes : undefined
        },

        /**
         * Compute the REST URL for the current document
         *
         * Used internaly by backbone in fetch, save, destroy
         *
         * @returns {string}
         */
        url : function mDocumenturl() {
            var urlData = "api/v1/", viewId = this.get("viewId");
            if (this.get("creationFamid") && this.id === null) {
                urlData += "families/" + encodeURIComponent(this.get("creationFamid")) + "/documentsViews/";
            } else {
                urlData += "documents/" + encodeURIComponent(this.id);
                if (this.get("revision") >= 0) {
                    urlData += "/revisions/" + encodeURIComponent(this.get("revision"));
                }
                if (viewId === undefined) {
                    if (this.get("renderMode") === "view") {
                        viewId = "!defaultConsultation";
                    } else if (this.get("renderMode") === "edit") {
                        viewId = "!defaultEdition";
                    } else {
                        viewId = "!defaultConsultation";
                    }
                }
                urlData += "/views/" + encodeURIComponent(viewId);
            }
            return urlData;
        },

        /**
         * Initialize event handling
         *
         */
        initialize : function mDocumentinitialize() {
            this.listenTo(this, "error", this.propagateSynchroError);
            this.listenTo(this, "destroy", this.destroySubcollection);
        },

        /**
         * Return a plain object of the current document for an usage in the view
         *
         * @returns {{document: {}}}
         */
        toData : function mDocumenttoData() {
            var returnObject = {
                document : {}
            };
            returnObject.document.properties = this.getProperties();
            returnObject.menus = this.get("menus").toJSON();
            returnObject.templates = this.get("templates");
            return returnObject;
        },

        /**
         * Return all the values of the current document
         *
         * @returns {{}}
         */
        getValues : function mDocumentdocumentGetValues() {
            var values = {};
            this.get("attributes").each(function (currentAttribute) {
                var currentValue = currentAttribute.get("attributeValue"), i, arrayValues = [];
                if (!currentAttribute.get("isValueAttribute")) {
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
        setValues : function mDocumentdocumentSetValues(values) {
            this.get("attributes").each(function (currentAttribute) {
                var newValue = values[currentAttribute.id];
                if (!currentAttribute.get("isValueAttribute")) {
                    return;
                }
                currentAttribute.set("attributeValue", newValue);
                // reset change also
                currentAttribute.changed = {};
            });
        },

        /**
         * reset all properties with a new set of properties
         */
        setProperties : function mDocumentdocumentSetProperties(values) {
            var model = this;
            _.each(values, function (value, key) {
                model.get("properties").set(key, value);
            });
        },

        /**
         * Get a plain object with properties of the document
         *
         * @returns {*}
         */
        getProperties : function mDocumentdocumentGetProperties(initialValue) {
            var properties;
            if (initialValue === true) {
                return this.initialProperties;
            } else {
                properties = this.get("properties").toJSON();
                properties.viewId = this.get("viewId");
                properties.renderMode = this.get("renderMode");
                return properties;
            }
        },

        /**
         * Return true if one the attribute of the document hasChanged
         *
         * @returns {boolean|*}
         */
        hasAttributesChanged : function mDocumenthasAttributesChanged() {
            return this.get("attributes").some(function (currentAttr) {
                return currentAttr.hasChanged("attributeValue");
            });
        },

        /**
         * Analyze return in case of sync uncomplete and trigger event error
         *
         * @param model
         * @param xhr
         * @param options
         */
        propagateSynchroError : function mDocumentpropagateSynchroError(model, xhr, options) {
            var attrModel, currentModel = this, parsedReturn;
            //Analyze XHR
            var messages = [];
            try {
                var result = JSON.parse(xhr.responseText);
                messages = result.messages;
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
                //Unable to parse responseText (error is not in JSON)
            }

            parsedReturn = {
                messages :     messages,
                responseText : "Unexpected error: " + xhr.status + " " + xhr.statusText
            };

            this.cleanErrorMessages();
            if (parsedReturn.messages.length === 0) {
                //Status 0 indicate offline browser
                if (xhr.status === 0) {
                    parsedReturn.responseText = "Your navigator seems offline, try later";
                }
                currentModel.trigger("showError", {
                    "title" :   "Unable to synchronise " + currentModel.get("properties").get("title"),
                    "message" : parsedReturn.responseText
                });
            }
            _.each(parsedReturn.messages, function (message) {
                switch (message.code) {
                    case "CRUD0211":// Syntax Error
                        if (message.data && message.data.id) {
                            attrModel = currentModel.get('attributes').get(message.data.id);
                            if (attrModel) {
                                attrModel.setErrorMessage(message.data.err, message.data.index);
                                currentModel.trigger("showError", {
                                    title :       message.contentText,
                                    htmlMessage : message.contentHtml,
                                    message :     attrModel.attributes.label + ' : ' + message.data.err
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
                                        message :     attrModel.attributes.label + ' : ' + constraint.err
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
        validate : function mDocumentvalidate() {
            var success = true,
                currentDocument = this,
                errorMessage = [];
            this.get("attributes").each(function (currentAttribute) {
                var parentAttribute = currentDocument.get("attributes").get(currentAttribute.get("parent"));
                if (currentAttribute.get("needed") === true) {
                    var currentValue = currentAttribute.get("attributeValue"),
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
                        errorMessage.push(parentAttribute.get('label') + ' / ' + currentAttribute.get("label") + ' is needed');
                        success = false;
                    }
                }
                if (!currentAttribute.checkConstraint()) {
                    success = false;
                    errorMessage.push(parentAttribute.get('label') + ' / ' + currentAttribute.get("label") + ' ' + currentAttribute.get("errorMessage"));
                }
            });
            if (!success) {
                return {
                    title :   "Unable to save",
                    message : errorMessage.join(', ')
                };
            }
            return undefined;
        },

        /**
         * Redraw messages for the error displayed
         */
        redrawErrorMessages : function mDocumentredrawErrorMessages() {
            var attrModels = this.get('attributes') || [];
            _.each(attrModels.models, function (attrModel) {
                var message = attrModel.get("errorMessage");
                // redo error after document is show
                attrModel.setErrorMessage(null);
                attrModel.setErrorMessage(message);
            });
        },

        /**
         * Propagate to attributes a clear message for the error displayed
         */
        cleanErrorMessages : function mDocumentcleanErrorMessages() {
            var attrModels = this.get('attributes') || [];
            _.each(attrModels.models, function (attrModel) {
                attrModel.setErrorMessage(null);
            });
        },
        /**
         * Parse the return of the REST API
         * @param response
         * @returns {{properties: (*|properties|exports.defaults.properties|exports.parse.properties|.createObjectExpression.properties), menus: (app.views.shared.menu|*), locale: *, renderMode: string, attributes: Array, templates: *, renderOptions: *}}
         */
        parse :              function mDocumentParse(response) {
            var values, attributes = [], renderMode = "view", structureAttributes, valueAttributes, visibilityAttributes,
                view = response.data.view;
            if (response.success === false) {
                throw new Error("Unable to get the data from documents");
            }
            if (view.renderOptions.mode) {
                if (view.renderOptions.mode === "edit") {
                    renderMode = "edit";
                } else if (view.renderOptions.mode === "view") {
                    renderMode = "view";
                } else {
                    throw new Error("Unkown render mode " + view.renderOptions.mode);
                }
            }
            valueAttributes = view.documentData.document.attributes;
            visibilityAttributes = view.renderOptions.visibilities;
            if (view.documentData.family) {
                structureAttributes = view.documentData.family.structure;
            } else {
                structureAttributes = [];
            }

            attributes = flattenAttributes(attributes, structureAttributes);
            _.each(attributes, function (currentAttributeStructure) {
                if (currentAttributeStructure.id && valueAttributes[currentAttributeStructure.id]) {
                    currentAttributeStructure.attributeValue = valueAttributes[currentAttributeStructure.id];
                }
                if (currentAttributeStructure.id && visibilityAttributes[currentAttributeStructure.id]) {
                    currentAttributeStructure.visibility = visibilityAttributes[currentAttributeStructure.id];
                }
            });

            this.initialProperties = _.defaults({
                "renderMode" : renderMode || "view",
                "viewId" :     response.data.properties.requestIdentifier
            }, view.documentData.document.properties);

            values = {
                initid :        response.data.properties.creationView === true ? null : view.documentData.document.properties.initid,
                properties :    view.documentData.document.properties,
                menus :         view.menu,
                viewId :        response.data.properties.requestIdentifier,
                locale :        view.locale.culture,
                renderMode :    renderMode || "view",
                attributes :    attributes,
                templates :     view.templates,
                renderOptions : view.renderOptions,
                customCSS :     view.style.css,
                customJS :      view.script.js,
                messages :      response.messages
            };
            if (response.data.properties.creationView === true) {
                values.creationFamid = view.documentData.document.properties.family.name;
            } else {
                values.creationFamid = false;
            }
            return values;
        },

        /**
         * Generate the collection of the current model
         *
         * @param attributes
         * @param options
         * @returns {*}
         */
        "set" : function mDocumentsetValues(attributes, options) {
            var currentModel = this;
            if (attributes.properties !== undefined) {
                if (currentModel.get("properties") instanceof DocumentProperties) {
                    currentModel.get("properties").trigger("destroy");
                }
                attributes.properties = new DocumentProperties(attributes.properties);
            }

            if (attributes.menus !== undefined) {
                if (currentModel.get("menus") instanceof CollectionMenus) {
                    currentModel.get("menus").destroy();
                }
                attributes.menus = new CollectionMenus(attributes.menus);
            }
            if (attributes.attributes !== undefined) {
                if (currentModel.get("attributes") instanceof CollectionAttributes) {
                    currentModel.get("attributes").destroy();
                }
                attributes.attributes = new CollectionAttributes(attributes.attributes, {
                    documentModel : currentModel,
                    renderOptions : attributes.renderOptions,
                    renderMode :    attributes.renderMode
                });
                //Set the internal content collection (for structure attributes)
                attributes.attributes.each(function (currentAttributeModel) {
                    if (currentAttributeModel.get("isValueAttribute")) {
                        return;
                    }
                    var childAttributes = attributes.attributes.filter(function (candidateChildModel) {
                        return candidateChildModel.get("parent") === currentAttributeModel.id;
                    });
                    if (childAttributes.length > 0) {
                        currentAttributeModel.setContentCollection(childAttributes);
                    }
                });
                //Propagate the change event to the model
                currentModel.listenTo(attributes.attributes, "change:attributeValue", function (model, value) {
                    currentModel.trigger("changeValue", {
                        documentId :    currentModel.get("initid"),
                        attributeId :   model.id,
                        value :         value,
                        previousValue : model.previous("attributeValue")
                    });
                });
                //Propagate the validate event to the model
                currentModel.listenTo(attributes.attributes, "constraint", function (options) {
                    currentModel.trigger("constraint", currentModel.get("properties").toJSON(), options.model.toJSON(), options.response);
                });
            }
            return Backbone.Model.prototype.set.call(this, attributes, options);
        },

        /**
         * Destroy sub collection on clear event
         *
         * @param options
         * @returns {*}
         */
        clear : function mDocumentclear(options) {
            this.destroySubcollection();
            return Backbone.Model.prototype.clear.call(this, options);
        },

        /**
         * Destroy the collection associated to the document (used in the destroy part of the view)
         *
         */
        destroySubcollection : function mDocumentdestroySubcollection() {
            if (this.get("menus") instanceof CollectionMenus) {
                this.get("menus").destroy();
            }
            if (this.get("properties") instanceof DocumentProperties) {
                this.get("properties").trigger("destroy");
            }
            if (this.get("attributes") instanceof CollectionAttributes) {
                this.get("attributes").destroy();
            }
        },

        /**
         * Used by backbone for the save part
         * @returns {{document: {attributes: *, properties : *}}}
         */
        toJSON : function mDocumenttoJSON() {
            return {
                document : {
                    properties : this.getProperties(),
                    attributes : this.getValues()
                }
            };
        }
    });

});
