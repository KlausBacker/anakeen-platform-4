/*global define*/
define([
    'underscore',
    'backbone',
    'dcpDocument/models/mDocumentProperties',
    'dcpDocument/models/mDocumentLock',
    'dcpDocument/collections/attributes',
    'dcpDocument/collections/menus',
    'dcpDocument/i18n',
    'dcpDocument/widgets/window/wNotification'
], function (_, Backbone, DocumentProperties, DocumentLock, CollectionAttributes, CollectionMenus, i18n)
{
    'use strict';

    var flattenAttributes = function mDocumentflattenAttributes(currentAttributes, attributes, parent)
    {
        if (!_.isArray(attributes)) {
            attributes = _.values(attributes);
        }
        if (parent) {
            _.each(attributes, function (value)
            {
                value.parent = parent;
            });
        }
        currentAttributes = _.union(currentAttributes, attributes);
        _.each(attributes, function (currentAttr)
        {
            if (currentAttr.content) {
                currentAttributes = _.union(currentAttributes, flattenAttributes(currentAttributes, currentAttr.content, currentAttr.id));
            }
        });
        return currentAttributes;
    };

    return Backbone.Model.extend({

        idAttribute: "initid",

        defaults: {
            revision: -1,
            viewId: undefined,
            renderMode: "view",
            properties: undefined,
            menus: undefined,
            attributes: undefined
        },

        /**
         * Compute the REST URL for the current document
         *
         * Used internaly by backbone in fetch, save, destroy
         *
         * @returns {string}
         */
        url: function mDocumenturl()
        {
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
                    } else
                        if (this.get("renderMode") === "edit") {
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
        initialize: function mDocumentinitialize()
        {
            this.listenTo(this, "error", this.propagateSynchroError);
            this.listenTo(this, "destroy", this.destroySubcollection);


        },

        /**
         * Return a plain object of the current document for an usage in the view
         *
         * @returns {{document: {}}}
         */
        toData: function mDocumenttoData()
        {
            var returnObject = {
                document: {}
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
        getValues: function mDocumentdocumentGetValues()
        {
            var values = {};
            this.get("attributes").each(function (currentAttribute)
            {
                var currentValue = currentAttribute.get("attributeValue"), i, arrayValues = [];
                if (!currentAttribute.get("isValueAttribute")) {
                    return;
                }
                if (currentAttribute.get("multiple")) {
                    currentValue = _.toArray(currentValue);
                    if (currentValue.length > 0) {
                        for (i = 0; i < currentValue.length; i += 1) {
                            arrayValues.push(currentValue[i] || {value: null});
                        }
                    } else {
                        arrayValues = {value: null};
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
        setValues: function mDocumentdocumentSetValues(values)
        {
            this.get("attributes").each(function (currentAttribute)
            {
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
        setProperties: function mDocumentdocumentSetProperties(values)
        {
            var model = this;
            _.each(values, function (value, key)
            {
                model.get("properties").set(key, value);
            });
        },


        lockDocument: function mDocumentLockDocument()
        {
            var docModel = this;
            var lockModel = new DocumentLock({"initid": this.get("initid"), "type": "permanent"});
            var security = this.get("properties").get("security");
            lockModel.save({}, {
                    success: function (theModel, data)
                    {
                        var menu = docModel.get("menus");
                        security.lock = data.data.lock;
                        docModel.get("properties").set("security,", security);

                        menu.setMenu("lock", "visibility", "hidden");
                        menu.setMenu("unlock", "visibility", "visible");

                        docModel.get("properties").trigger("change");
                    },
                    error: function (theModel, HttpResponse)
                    {
                        var response = JSON.parse(HttpResponse.responseText);

                        docModel.trigger("showError", {
                            title: response.exceptionMessage
                        });
                    }
                }
            );
        },

        unlockDocument: function mDocumentLockDocument()
        {
            var docModel = this;
            //  type = empty means Delete all locks
            var lockModel = new DocumentLock({"initid": this.get("initid"), "type": ""});
            var security = this.get("properties").get("security");
            lockModel.destroy({
                    success: function ()
                    {
                        var menu = docModel.get("menus");
                        security.lock = {
                            lockedBy: {
                                id: 0
                            }
                        };
                        docModel.get("properties").set("security,", security);

                        menu.setMenu("lock", "visibility", "visible");
                        menu.setMenu("unlock", "visibility", "hidden");
                        docModel.get("properties").trigger("change");

                    },
                    error: function (theModel, HttpResponse)
                    {
                        var response = JSON.parse(HttpResponse.responseText);

                        docModel.trigger("showError", {
                            title: response.exceptionMessage
                        });
                    }
                }
            );
        },
        /**
         * Get a plain object with properties of the document
         *
         * @returns {*}
         */
        getProperties: function mDocumentdocumentGetProperties(initialValue)
        {
            var properties = {};
            if (initialValue === true) {
                return this.initialProperties;
            } else {
                if (this.get("properties")) {
                    properties = this.get("properties").toJSON();
                }
                properties.initid = this.get("initid");
                properties.revision = this.get("revision");
                properties.viewId = this.get("viewId");

                return properties;
            }
        },

        /**
         * Get document properties, values and labels of attributes
         */
        getDocumentData: function mDocumentGetDocumentData()
        {

            var documentData = {
                properties: this.getProperties(false),
                attributeValues: this.getValues(),
                attributeLabels: {},
                createAttributeView: function ()
                {
                    return this.id;
                }
            };
            this.get("attributes").each(function (currentAttribute)
            {
                documentData.attributeLabels[currentAttribute.id] = currentAttribute.get("label");
            });
            return documentData;
        },
        /**
         * Return true if one the attribute of the document hasChanged
         *
         * @returns {boolean|*}
         */
        hasAttributesChanged: function mDocumenthasAttributesChanged()
        {
            return this.get("attributes").some(function (currentAttr)
            {
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
        propagateSynchroError: function mDocumentpropagateSynchroError(model, xhr, options)
        {
            var attrModel, currentModel = this, parsedReturn, errorCode = null;
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
                messages: messages,
                responseText: "Unexpected error: " + xhr.status + " " + xhr.statusText
            };


            this.cleanErrorMessages();
            if (parsedReturn.messages.length === 0) {
                //Status 0 indicate offline browser
                if (xhr.status === 0) {
                    parsedReturn.responseText = "Your navigator seems offline, try later";
                    errorCode = "offline";
                }
                currentModel.trigger("showError", {
                    "errorCode": errorCode,
                    "title": "Unable to synchronise " + currentModel.get("properties").get("title"),
                    "message": parsedReturn.responseText
                });
            }
            _.each(parsedReturn.messages, function (message)
            {
                switch (message.code) {
                    case "CRUD0211":// Syntax Error
                        if (message.data && message.data.id) {
                            attrModel = currentModel.get('attributes').get(message.data.id);
                            if (attrModel) {
                                attrModel.setErrorMessage(message.data.err, message.data.index);
                                currentModel.trigger("showError", {
                                    title: message.contentText,
                                    htmlMessage: message.contentHtml,
                                    message: attrModel.attributes.label + ' : ' + message.data.err,
                                    "errorCode": message.code
                                });
                            } else {
                                currentModel.trigger("showError", {
                                    title: message.contentText,
                                    htmlMessage: message.contentHtml,
                                    message: message.data.err,
                                    "errorCode": message.code
                                });
                            }
                        }
                        break;
                    case "CRUD0212": // Constraint Error
                        if (message.data && message.data.constraint) {
                            _.each(message.data.constraint, function (constraint, aid)
                            {
                                attrModel = currentModel.get('attributes').get(constraint.id);
                                if (attrModel) {
                                    attrModel.setErrorMessage(constraint.err, constraint.index);
                                    currentModel.trigger("showError", {
                                        title: message.contentText,
                                        htmlMessage: message.contentHtml,
                                        message: attrModel.attributes.label + ' : ' + constraint.err,
                                        "errorCode": message.code
                                    });
                                } else {
                                    currentModel.trigger("showError", {
                                        title: message.contentText,
                                        htmlMessage: message.contentHtml,
                                        message: constraint.err,
                                        "errorCode": message.code
                                    });
                                }
                            });
                        }
                        if (message.data && message.data.preStore) {
                            currentModel.trigger("showError", {
                                title: message.contentText,
                                htmlMessage: message.contentHtml,
                                message: message.data.preStore,
                                "errorCode": message.code
                            });
                        }
                        break;

                    default:
                        if (message.type === "error" && message.contentText) {
                            currentModel.trigger("showError", {
                                title: message.contentText,
                                htmlMessage: message.contentHtml,
                                "errorCode": message.code
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
        validate: function mDocumentvalidate()
        {
            var success = true,
                currentDocument = this,
                errorMessage = [], event = {prevent: false},
                templateMessage;
            this.trigger("validate", event);
            if (event.prevent) {
                return {
                    title: "Unable to save"
                };
            }
            this.get("attributes").each(function (currentAttribute)
            {
                var parentAttribute = currentDocument.get("attributes").get(currentAttribute.get("parent"));
                currentAttribute.setErrorMessage(null);

                if (currentAttribute.get("needed") === true) {
                    var currentValue = currentAttribute.get("attributeValue"),
                        oneSuccess = true;

                    if (currentAttribute.get("multiple")) {
                        if (parentAttribute.get("type") === "array") {
                            // Verify each index
                            _.each(currentValue, function (attributeValue, index)
                            {
                                if ((!attributeValue || !attributeValue.value) && attributeValue.value !== 0) {
                                    currentAttribute.setErrorMessage(i18n.___("Empty value not allowed", "ddui"), index);

                                    templateMessage = _.template(i18n.___("{{parentLabel}} / {{label}} (row # {{index}}) is needed", "ddui"), {escape: /\{\{(.+?)\}\}/g});
                                    errorMessage.push(templateMessage({
                                        parentLabel: parentAttribute.get('label'),
                                        label: currentAttribute.get("label"),
                                        index: index + 1
                                    }));
                                    success = false;
                                }
                            });

                        } else {
                            if (!currentValue || currentValue.length === 0) {
                                oneSuccess = false;
                            }
                        }
                    } else {
                        if ((!currentValue || !currentValue.value) && currentValue.value !== 0) {
                            currentAttribute.setErrorMessage(i18n.___("Empty value not allowed", "ddui"));
                            oneSuccess = false;
                        }
                    }
                    if (!oneSuccess) {
                        templateMessage = _.template(i18n.___("{{parentLabel}} / {{label}} is needed", "ddui"), {escape: /\{\{(.+?)\}\}/g});
                        errorMessage.push(templateMessage({
                            parentLabel: parentAttribute.get('label'),
                            label: currentAttribute.get("label")
                        }));
                        currentAttribute.setErrorMessage(i18n.___("The field must not be empty", "ddui"));
                        success = false;
                    }
                }

                if (!currentAttribute.checkConstraint({clearError: false})) {
                    success = false;
                    templateMessage = _.template("<%- parentLabel %> / <%- label %> <%- errorMessage %>");
                    errorMessage.push(templateMessage({
                        parentLabel: parentAttribute.get('label'),
                        label: currentAttribute.get("label"),
                        errorMessage: currentAttribute.get("errorMessage")
                    }));
                }
            });
            if (!success) {
                return {
                    title: i18n.___("Unable to save", "ddui"),
                    message: errorMessage.join(', ' + "\n")
                };
            }
            return undefined;
        },

        /**
         * Redraw messages for the error displayed
         */
        redrawErrorMessages: function mDocumentredrawErrorMessages()
        {
            var attrModels = this.get('attributes') || [];
            _.each(attrModels.models, function (attrModel)
            {
                var message = attrModel.get("errorMessage");
                // redo error after document is show
                if (message) {
                    attrModel.setErrorMessage(null);// use double affect to force tooltip redraw
                    attrModel.setErrorMessage(message);
                }
            });
        },

        /**
         * Propagate to attributes a clear message for the error displayed
         */
        cleanErrorMessages: function mDocumentcleanErrorMessages()
        {
            var attrModels = this.get('attributes') || [];
            _.each(attrModels.models, function (attrModel)
            {
                attrModel.setErrorMessage(null);
            });
        },
        /**
         * Parse the return of the REST API
         * @param response
         * @returns {{properties: (*|properties|exports.defaults.properties|exports.parse.properties|.createObjectExpression.properties), menus: (app.views.shared.menu|*), locale: *, renderMode: string, attributes: Array, templates: *, renderOptions: *}}
         */
        parse: function mDocumentParse(response)
        {
            var values, attributes = [], renderMode = "view", structureAttributes, valueAttributes, visibilityAttributes,
                neededAttributes, view = response.data.view;
            if (response.success === false) {
                throw new Error("Unable to get the data from documents");
            }
            if (view.renderOptions.mode) {
                if (view.renderOptions.mode === "edit") {
                    renderMode = "edit";
                } else
                    if (view.renderOptions.mode === "view") {
                        renderMode = "view";
                    } else {
                        throw new Error("Unkown render mode " + view.renderOptions.mode);
                    }
            }
            valueAttributes = view.documentData.document.attributes;
            visibilityAttributes = view.renderOptions.visibilities;
            neededAttributes = view.renderOptions.needed;
            if (view.documentData.family) {
                structureAttributes = view.documentData.family.structure;
            } else {
                structureAttributes = [];
            }

            attributes = flattenAttributes(attributes, structureAttributes);
            _.each(attributes, function (currentAttributeStructure)
            {
                if (currentAttributeStructure.id && valueAttributes[currentAttributeStructure.id]) {
                    currentAttributeStructure.attributeValue = valueAttributes[currentAttributeStructure.id];
                    currentAttributeStructure.needed = (neededAttributes[currentAttributeStructure.id] === true);
                }
                if (currentAttributeStructure.id && visibilityAttributes[currentAttributeStructure.id]) {
                    currentAttributeStructure.visibility = visibilityAttributes[currentAttributeStructure.id];
                }
            });

            this.initialProperties = _.defaults({
                "renderMode": renderMode || "view",
                "viewId": response.data.properties.requestIdentifier
            }, view.documentData.document.properties);

            values = {
                initid: response.data.properties.creationView === true ? null : view.documentData.document.properties.initid,
                properties: view.documentData.document.properties,
                menus: view.menu,
                viewId: response.data.properties.requestIdentifier,
                locale: view.locale.culture,
                renderMode: renderMode || "view",
                attributes: attributes,
                templates: view.templates,
                renderOptions: view.renderOptions,
                customCSS: view.style.css,
                customJS: view.script.js,
                messages: response.messages
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
        "set": function mDocumentsetValues(attributes, options)
        {
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
                    documentModel: currentModel,
                    renderOptions: attributes.renderOptions,
                    renderMode: attributes.renderMode
                });
                //Set the internal content collection (for structure attributes)
                attributes.attributes.each(function (currentAttributeModel)
                {
                    if (currentAttributeModel.get("isValueAttribute")) {
                        return;
                    }
                    var childAttributes = attributes.attributes.filter(function (candidateChildModel)
                    {
                        return candidateChildModel.get("parent") === currentAttributeModel.id;
                    });
                    if (childAttributes.length > 0) {
                        currentAttributeModel.setContentCollection(childAttributes);
                    }
                });
                //Propagate the change event to the model
                currentModel.listenTo(attributes.attributes, "change:attributeValue", function (model, value)
                {
                    currentModel.trigger("changeValue", {
                        attributeId: model.id
                    });
                });
                //Propagate the validate event to the model
                currentModel.listenTo(attributes.attributes, "constraint", function (options)
                {
                    currentModel.trigger("constraint", options.model.id, options.response);
                });
                //Propagate the renderDone event of the attributes to the model
                currentModel.listenTo(attributes.attributes, "renderDone", function (options)
                {
                    currentModel.trigger("attributeRender", options.model.id, options.$el);
                });
                //Propagate the array event modified to the model
                currentModel.listenTo(attributes.attributes, "array", function (type, model, options)
                {
                    currentModel.trigger("arrayModified", {
                        attributeId: model.id,
                        "type": type,
                        "options": options
                    });
                });
                //Propagate the event externalLinkSelected to the model
                currentModel.listenTo(attributes.attributes, "internalLinkSelected", function (options)
                {
                    currentModel.trigger("internalLinkSelected", {}, options);
                });
                //Propagate the event helperSearch to the model
                currentModel.listenTo(attributes.attributes, "helperSearch", function (event, attrid, options)
                {
                    currentModel.trigger("helperSearch", event, attrid, options);
                });
                //Propagate the event helperResponse to the model
                currentModel.listenTo(attributes.attributes, "helperResponse", function (event, attrid, options)
                {
                    currentModel.trigger("helperResponse", event, attrid, options);
                });
                //Propagate the event helperResponse to the model
                currentModel.listenTo(attributes.attributes, "helperSelect", function (event, attrid, options)
                {
                    currentModel.trigger("helperSelect", event, attrid, options);
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
        clear: function mDocumentclear(options)
        {
            return Backbone.Model.prototype.clear.call(this, options);
        },

        /**
         * Destroy the collection associated to the document (used in the destroy part of the view)
         *
         */
        destroySubcollection: function mDocumentdestroySubcollection()
        {
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
        toJSON: function mDocumenttoJSON()
        {
            return {
                document: {
                    properties: this.getProperties(),
                    attributes: this.getValues()
                }
            };
        },

        fetch: function mDocumentFetch(attributes, options)
        {
            var event = {prevent: false}, currentModel = this, afterDone = function afterDone()
            {
                currentModel.trigger("close");
            };
            var security = this.get("properties") ? (this.get("properties").get("security")) : null;
            var theModel = this;
            options = options || {};
            this.trigger("beforeClose", event);
            if (event.prevent === false) {
                if (options.success) {
                    options.success = _.wrap(options.success, function (success)
                    {
                        afterDone();
                        return success.apply(this, arguments);
                    });
                } else {
                    options.success = afterDone;
                }
                this.trigger("displayLoading");
                if (this.get("renderMode") === "edit" && security && security.lock && security.lock.temporary) {

                    var lockModel = new DocumentLock({"initid": this.get("initid"), "type": "temporary"});
                    lockModel.destroy({
                        success: function ()
                        {
                            Backbone.Model.prototype.fetch.call(theModel, attributes, options);
                        },
                        error: function ()
                        {
                            Backbone.Model.prototype.fetch.call(theModel, attributes, options);
                        }
                    });
                } else {
                    return Backbone.Model.prototype.fetch.call(this, attributes, options);
                }
            }
            return false;
        },

        save: function mDocumentSave(attributes, options)
        {
            var event = {prevent: false}, currentModel = this, afterDone = function afterDone()
            {
                currentModel.trigger("afterSave");
                currentModel.trigger("close");
            };
            options = options || {};
            this.trigger("beforeSave", event);
            if (event.prevent === false) {
                if (options.success) {
                    options.success = _.wrap(options.success, function (success)
                    {
                        afterDone();
                        return success.apply(this, arguments);
                    });
                } else {
                    options.success = afterDone;
                }
                this.trigger("displayLoading", {isSaving: true});
                return Backbone.Model.prototype.save.call(this, attributes, options);
            }
            return false;
        },

        destroy: function mDocumentDestroy(attributes, options)
        {
            var event = {prevent: false}, currentModel = this, afterDone = function afterDone()
            {
                currentModel.trigger("afterDelete");
                currentModel.trigger("close");
            };
            options = options || {};
            this.trigger("beforeDelete", event);
            if (event.prevent === false) {
                if (options.success) {
                    options.success = _.wrap(options.success, function (success)
                    {
                        afterDone();
                        return success.apply(this, arguments);
                    });
                } else {
                    options.success = afterDone;
                }
                this.trigger("displayLoading");
                return Backbone.Model.prototype.destroy.call(this, attributes, options);
            }
            return false;
        }
    });

});
