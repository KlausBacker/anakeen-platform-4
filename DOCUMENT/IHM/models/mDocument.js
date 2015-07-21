/*global define, console, require*/
define([
    'jquery',
    'underscore',
    'backbone',
    'dcpDocument/models/mDocumentProperties',
    'dcpDocument/models/mDocumentLock',
    'dcpDocument/models/mFamilyStructure',
    'dcpDocument/collections/attributes',
    'dcpDocument/collections/menus',
    'dcpDocument/i18n'
], function ($, _, Backbone, DocumentProperties, DocumentLock, FamilyStructure, CollectionAttributes, CollectionMenus, i18n)
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
        typeModel:"ddui:document",
        idAttribute: "initid",

        defaults: {
            revision: -1,
            viewId: undefined,
            renderMode: "view",
            properties: undefined,
            menus: undefined,
            attributes: undefined
        },
        // Record custom data in model directly - not in model property because must not be reset by clear method
        _customClientData: null,

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
            var customClienData = this._customClientData;
            var currentMethod = this.get("currentHttpMethod");

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

            if (customClienData && (currentMethod === "read" || currentMethod === "delete" )) {
                urlData += "?customClientData=" + encodeURIComponent(JSON.stringify(customClienData));
            }
            return urlData;
        },

        /**
         * overhide Backbone sync to record method
         * @param method
         * @param model
         * @param options
         * @returns {*}
         */
        sync: function mDocumentSync(method, model, options)
        {
            this.set("currentHttpMethod", method); // record for url method
            return Backbone.Model.prototype.sync.apply(this, arguments);
        },
        /**
         * Initialize event handling
         *
         */
        initialize: function mDocumentinitialize()
        {
            var theModel = this;

            this.listenTo(this, "error", this.propagateSynchroError);
            this.listenTo(this, "destroy", this.destroySubcollection);
            this.listenTo(this, "destroy", this.unbindLoadEvent);
            this.listenTo(this, "sync", this.completeStructure);

            $(window).on("beforeunload." + this.cid, function mDocumentBeforeUnload()
            {
                var security = theModel.get("properties") ? (theModel.get("properties").get("security")) : null;
                if (theModel.hasAttributesChanged()) {
                    return i18n.___("The form has been modified and is is not saved", "ddui");
                }


                if (theModel.get("renderMode") === "edit" && security && security.lock && security.lock.temporary) {
                    //var lockModel = new DocumentLock({"initid": theModel.get("initid"), "type": "temporary"});
                    //lockModel.destroy({wait: false});
                    // No use model destroy : page is destroyed before request is some case
                    $.ajax({
                        url: "api/v1/documents/" + theModel.get("initid") + "/locks/temporary",
                        type: "DELETE",
                        async: false
                    });
                    theModel.set("unlocking", true);
                }
            });


            $(window).on("pagehide." + this.cid, function mDocumentPageHide(event)
            {
                var security = theModel.get("properties") ? (theModel.get("properties").get("security")) : null;
                var unlocking = theModel.get("unlocking");

                if (!unlocking && theModel.get("renderMode") === "edit" && security && security.lock && security.lock.temporary) {
                    $.ajax({
                        url: "api/v1/documents/" + theModel.get("initid") + "/locks/temporary",
                        type: "DELETE",
                        async: false
                    });
                }

            });


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
                        arrayValues = [];//{value: null};
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
                properties.renderMode = this.get("renderMode");
                properties.isModified = this.hasAttributesChanged();

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
            if (!this.get("attributes")) {
                return false;
            }
            return !!this.get("attributes").some(function (currentAttr)
            {
                return (currentAttr.hasValueChanged());
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
            var attrModel, currentModel = this, parsedReturn, errorCode = null, title = "";
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
                    parsedReturn.responseText = i18n.___("Your navigator seems offline, try later", "ddui");
                    errorCode = "offline";
                }
                if (currentModel.get("properties")) {
                    title = currentModel.get("properties").get("title");
                }
                currentModel.trigger("showError", {
                    "errorCode": errorCode,
                    "title": "Unable to synchronise " + title,
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
                    if (_.isArray(currentAttribute.get("errorMessage"))) {
                        templateMessage = _.template("<%- parentLabel %> / <%- label %> " +
                        "<% for(var msg in errorMessage) { %>" +
                        "\n<%- rowText %> <%- errorMessage[msg].index + 1 %> : <%- errorMessage[msg].message %>\n <% } %> ");
                    } else {
                        templateMessage = _.template("<%- parentLabel %> / <%- label %> <%- errorMessage %>");
                    }
                    errorMessage.push(templateMessage({
                        parentLabel: parentAttribute.get('label'),
                        label: currentAttribute.get("label"),
                        rowText: i18n.___("Row #", "ddui"),
                        errorMessage: currentAttribute.get("errorMessage")
                    }));
                }
            });
            if (!success) {
                return {
                    title: i18n.___("Unable to save", "ddui"),
                    message: errorMessage.join(', ' + "\n"),
                    errorCode: "attributeNeeded"
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
            var values, renderMode = "view", view = response.data.view;


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


            this.initialProperties = _.defaults({
                "renderMode": renderMode || "view",
                "viewId": response.data.properties.requestIdentifier
            }, view.documentData.document.properties);

            values = {
                initid: response.data.properties.creationView === true ? null : view.documentData.document.properties.initid,
                properties: view.documentData.document.properties,
                menus: view.menu,
                viewId: response.data.properties.requestIdentifier,
                revision: view.documentData.document.properties.revision,
                locale: view.locale.culture,
                renderMode: renderMode || "view",
                attributes: undefined,
                templates: view.templates,
                renderOptions: view.renderOptions,
                customCSS: view.style.css,
                customJS: view.script.js,
                customServerData: view.customServerData,
                messages: response.messages,
                originalValues: view.documentData.document.attributes
            };
            this._customClientData = null;
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
         * @param keyOrValues string|object of properties or key of the current property
         * @param value
         * @returns {*}
         */
        "set": function mDocumentsetValues(keyOrValues, value)
        {
            var currentModel = this;
            if (keyOrValues.properties !== undefined) {
                if (currentModel.get("properties") instanceof DocumentProperties) {
                    currentModel.get("properties").trigger("destroy");
                }
                keyOrValues.properties = new DocumentProperties(keyOrValues.properties);
            }

            if (keyOrValues.menus !== undefined) {
                if (currentModel.get("menus") instanceof CollectionMenus) {
                    currentModel.get("menus").destroy();
                }
                keyOrValues.menus = new CollectionMenus(keyOrValues.menus);
            }
            if (keyOrValues === "attributes") {
                if (currentModel.get("attributes") instanceof CollectionAttributes) {
                    currentModel.get("attributes").destroy();
                }
                value = new CollectionAttributes(value, {
                    documentModel: currentModel,
                    renderOptions: currentModel.get("renderOptions"),
                    renderMode: currentModel.get("renderMode")
                });
                //Set the internal content collection (for structure attributes)
                value.each(function (currentAttributeModel)
                {
                    if (currentAttributeModel.get("isValueAttribute")) {
                        return;
                    }
                    var childAttributes = value.filter(function (candidateChildModel)
                    {
                        return candidateChildModel.get("parent") === currentAttributeModel.id;
                    });
                    if (childAttributes.length > 0) {
                        currentAttributeModel.setContentCollection(childAttributes);
                    }
                });
                //Propagate the change event to the model
                currentModel.listenTo(value, "change:attributeValue", function (model, value)
                {
                    currentModel.trigger("changeValue", {
                        attributeId: model.id
                    });
                });
                //Propagate the validate event to the model
                currentModel.listenTo(value, "constraint", function (options)
                {
                    currentModel.trigger("constraint", options.model.id, options.response);
                });
                //Propagate the renderDone event of the attributes to the model
                currentModel.listenTo(value, "renderDone", function (options)
                {
                    currentModel.trigger("attributeRender", options.model.id, options.$el);
                });
                //Propagate the beforeRender event of the attributes to the model
                currentModel.listenTo(value, "beforeRender", function (event, options)
                {
                    currentModel.trigger("beforeAttributeRender", event, options.model.id, options.$el);
                });
                //Propagate the array event modified to the model
                currentModel.listenTo(value, "array", function (type, model, options)
                {
                    currentModel.trigger("arrayModified", {
                        attributeId: model.id,
                        "type": type,
                        "options": options
                    });
                });
                //Propagate the event externalLinkSelected to the model
                currentModel.listenTo(value, "internalLinkSelected", function (event, options)
                {
                    currentModel.trigger("internalLinkSelected", event, options);
                });
                //Propagate the event downloadFile to the model
                currentModel.listenTo(value, "downloadFile", function (event, attrid, options)
                {
                    currentModel.trigger("downloadFile", event, attrid, options);
                });

                //Propagate the event uploadFile to the model
                currentModel.listenTo(value, "uploadFile", function (event, attrid, options)
                {
                    currentModel.trigger("uploadFile", event, attrid, options);
                });
                //Propagate the event helperSearch to the model
                currentModel.listenTo(value, "helperSearch", function (event, attrid, options)
                {
                    currentModel.trigger("helperSearch", event, attrid, options);
                });
                //Propagate the event helperResponse to the model
                currentModel.listenTo(value, "helperResponse", function (event, attrid, options)
                {
                    currentModel.trigger("helperResponse", event, attrid, options);
                });
                //Propagate the event helperResponse to the model
                currentModel.listenTo(value, "helperSelect", function (event, attrid, options)
                {
                    currentModel.trigger("helperSelect", event, attrid, options);
                });
            }
            return Backbone.Model.prototype.set.call(this, keyOrValues, value);
        },

        unbindLoadEvent: function mDocumentUnbindLoadEvent()
        {
            $(window).off("." + this.cid);
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
                },
                customClientData: this._customClientData
            };
        },


        fetchDocument: function mDocumentFetch(values, options)
        {
            var docModel = this, event = {prevent: false};
            var currentInitid = this.get("initid");

            _.defaults(values, {revision: -1, viewId: "!defaultConsultation"});

            this.trigger("beforeClose", event, values);
            if (event.prevent === false) {
                // Verify if current document need to be unlocked before fetch another
                var security = this.get("properties") ? (this.get("properties").get("security")) : null;
                var previousMode = this.get("renderMode");

                if (previousMode === "edit" && security && security.lock && security.lock.temporary) {
                    this.needUnlock = {
                        initid: currentInitid
                    };

                }
                _.each(_.pick(values, "initid", "revision", "viewId"), function mDocumentsetNewOptions(value, key)
                {
                    docModel.set(key, value);
                });
                return this.fetch(options);
            }
            return false;
        },

        fetch: function mDocumentFetch(options)
        {
            var currentModel = this, currentProperties = this.getProperties(true), lockModel,
                afterDone = function afterDone()
                {
                    currentModel.trigger("close", currentProperties);
                };
            var nextView = this.get("viewId");


            options = options || {};


            if (options.success) {
                options.success = _.wrap(options.success, function (success)
                {
                    afterDone();
                    return success.apply(this, _.rest(arguments));
                });
            } else {
                options.success = afterDone;
            }
            this.trigger("displayLoading");

            if (!nextView) {
                nextView = (this.get("renderMode") === "edit") ? "!defaultEdition" : "!defaultConsultation";
            }
            if (nextView !== "!defaultConsultation" && nextView !== "!coreCreation" && this.get("renderMode") !== "create") {
                lockModel = new DocumentLock({initid: this.get("initid"), viewId: nextView, type: "temporary"});
                lockModel.save({}, {
                    success: function ()
                    {
                        Backbone.Model.prototype.fetch.call(currentModel, options);
                    },
                    error: function (theModel, HttpResponse)
                    {
                        var response = JSON.parse(HttpResponse.responseText);

                        currentModel.trigger("showError", {
                            title: response.exceptionMessage
                        });
                    }
                });
            } else {
                if (this.needUnlock) {
                    if (this.needUnlock.initid === this.get("initid")) {
                        // If same document "get" must be perform after unlock
                        lockModel = new DocumentLock({"initid": this.needUnlock.initid, "type": "temporary"});
                        lockModel.destroy({
                            success: function ()
                            {
                                Backbone.Model.prototype.fetch.call(currentModel, options);
                            },
                            error: function (theModel, HttpResponse)
                            {
                                var response = JSON.parse(HttpResponse.responseText);

                                currentModel.trigger("showError", {
                                    title: response.exceptionMessage
                                });
                            }
                        });
                    } else {
                        lockModel = new DocumentLock({"initid": this.needUnlock.initid, "type": "temporary"});
                        lockModel.destroy();

                        this.needUnlock = null;
                        return Backbone.Model.prototype.fetch.call(this, options);
                    }

                    this.needUnlock = null;
                } else {
                    return Backbone.Model.prototype.fetch.call(this, options);
                }
            }


        },

        save: function mDocumentSave(attributes, options)
        {
            var event = {prevent: false}, currentModel = this, currentProperties = this.getProperties(true),
                afterDone = function afterDone()
                {
                    currentModel.trigger("afterSave", currentProperties);
                    currentModel.trigger("close", currentProperties);
                };
            options = options || {};
            this.trigger("beforeSave", event);
            if (event.prevent === false) {
                if (options.success) {
                    options.success = _.wrap(options.success, function (success)
                    {
                        afterDone();
                        return success.apply(this, _.rest(arguments));
                    });
                } else {
                    options.success = afterDone;
                }
                this.trigger("displayLoading", {isSaving: true});
                return Backbone.Model.prototype.save.call(this, attributes, options);
            }
            return false;
        },

        /**
         * Get complementary data : family structure
         */
        completeStructure: function mDocumentCompleteStructure()
        {
            var mStructure, documentModel = this;

            var neededAttributes = this.get("renderOptions").needed;
            var visibilityAttributes = this.get("renderOptions").visibilities;
            var valueAttributes = this.get("originalValues");

            if (this.get("properties").get("type") === "family") {
                // Family has no attributes
                this.set("attributes", []);
                this.injectJS(); // inject JS before reload the document
                return;
            }

            if (!_.isUndefined(this.get("attributes"))) {
                this.set("attributes",this.get("attributes")); // to convert attributes to models
                this.injectJS(); // trigger event to render document
                return;
            }

            mStructure = new FamilyStructure({
                familyId: this.get("properties").get("family").name,
                referencedocument: {
                    initid: this.get("initid"),
                    viewId: this.get("viewId"),
                    revision: this.get("revision")
                }
            });

            mStructure.fetch({
                success: function (structureModel, response)
                {
                    if (_.isEqual(structureModel.get("referencedocument"), {
                            initid: documentModel.get("initid"),
                            viewId: documentModel.get("viewId"),
                            revision: documentModel.get("revision")
                        })) {
                        var attributes = flattenAttributes(attributes, response.data.family.structure);
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
                        documentModel.set("attributes", attributes);
                        documentModel.injectJS(); // trigger event to render document
                    }
                },

                error: function (structureModel, HttpResponse)
                {
                    if (HttpResponse && HttpResponse.status === 0) {
                        documentModel.trigger("showError", {
                            title: i18n.___("Your navigator seems offline, try later", "ddui")
                        });
                        return;
                    }
                    try {
                        var response = JSON.parse(HttpResponse.responseText);
                        documentModel.trigger("showError", {
                            title: response.exceptionMessage
                        });
                    } catch(exception) {
                        documentModel.trigger("showError", {
                            title: exception.message
                        });
                    }

                }
            });
        },

        /**
         * Inject JS in the main page before render view
         * To launch beforeRender and beforeRenderAttribute
         */
        injectJS : function mDocumentInjectJs()
        {
            var documentModel = this,
                customJS = _.pluck(this.get("customJS"), "path");
                require(customJS, function initView() {
                    documentModel.trigger("reload");
                });
        },


        deleteDocument: function mDocumentDelete(options)
        {
            var event = {prevent: false}, currentModel = this, currentProperties = this.getProperties(true),
                afterError = function afterError(resp)
                {
                    currentModel.trigger('error', currentModel, resp);
                },
                afterDone = function afterDone(resp)
                {
                    if (!currentModel.set(currentModel.parse(resp, options), options)) {
                        return false;
                    }
                    currentModel.trigger("sync", currentModel, resp, options);
                    currentModel.trigger("afterDelete", currentProperties);
                    currentModel.trigger("close", currentProperties);
                };
            options = options || {};
            this.trigger("beforeDelete", event);
            if (event.prevent === false) {
                if (options.success) {
                    options.success = _.wrap(options.success, function (success)
                    {
                        afterDone.apply(this, _.rest(arguments));
                        return success.apply(this, _.rest(arguments));
                    });
                } else {
                    options.success = afterDone;
                }
                if (options.error) {
                    options.error = _.wrap(options.error, function (error)
                    {
                        afterError();
                        return error.apply(this, _.rest(arguments));
                    });
                }
                this.trigger("displayLoading");
                if (this.isNew()) {
                    console.error("Unable to delete new document");
                    if (options.error) {
                        options.error();
                    }
                    return false;
                }
                return this.sync('delete', this, options);
            }
            return false;
        }
    });

});
