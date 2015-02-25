define([
    'jquery',
    'underscore',
    'backbone',
    'routers/router',
    'models/mDocument',
    'controllerObjects/attributeInterface',
    'views/document/vDocument',
    'views/workflow/vTransition',
    'widgets/widget',
    'widgets/window/wConfirm',
    'widgets/window/wLoading'
], function ($, _, Backbone, Router, DocumentModel, AttributeInterface, DocumentView) {
    'use strict';

    var eventList = ["ready", "change", "message", "error", "validate", "attributeReady",
        "helperSearch", "helperResponse", "helperSelect",
        "arrayModified", "documentLinkSelected",
        "beforeClose", "close",
        "beforeSave", "afterSave",
        "beforeDelete", "afterDelete"];

    $.widget("dcp.documentController", {

        options : {
            eventPrefix :    "document",
            initid :         null,
            viewId :         undefined,
            revision :       undefined,
            constraintList : [],
            eventList :      []
        },

        /**
         * Create widget
         * @private
         */
        _create : function documentController_create() {
            if (!this.options.initid) {
                throw new Error("Widget cannot be initialized without an initid");
            }
            this.initialLoaded = false;
            this.options.constraintList = [];
            this.options.eventList = [];
            this.activatedConstraint = [];
            this.activatedEvent = [];
            this._initExternalElements();
            this._initModel(this._getModelValue());
            this._initView();
            this._model.fetch();
            this._initRouter();
            this._super();
        },

        /**
         * Delete the widget
         * @private
         */
        _destroy : function documentController_destroy() {
            this.view.remove();
            delete this._model;
            this._trigger("destroy");
            this._super();
        },

        /**
         * Return essential element of the current document
         *
         * @returns {Object}
         * @private
         */
        _getModelValue : function documentController_getModelValue() {
            return _.pick(this.options, "initid", "viewId", "revision");
        },

        /**
         * Generate the dom where the view is inserted
         * @private
         */
        _initDom : function documentController_initDom() {
            var $document = this.element.find(".dcpDocument");
            if (!this.$document || $document.length === 0) {
                this.element.append('<div class="dcpDocument"></div>');
                this.$document = this.element.find(".dcpDocument");
            }
        },

        /**
         * Init the model and bind the events
         *
         * @param initialValue
         * @returns {DocumentModel}
         * @private
         */
        _initModel : function documentController_initModel(initialValue) {
            var model = new DocumentModel(initialValue);
            this._model = model;
            this._initModelEvents();
            return model;
        },

        /**
         * Init the view and bind the events
         *
         * @returns {DocumentView}
         * @private
         */
        _initView : function documentController_initView() {
            var documentView, $document;
            this._initDom();
            $document = this.$document;
            documentView = new DocumentView({model : this._model, el : $document[0]});
            this.view = documentView;
            this._initViewEvents();
            return documentView;
        },

        /**
         * Clear and reinit the model with current widget values
         *
         * @private
         */
        _reinitModel : function documentController_reinitModel() {
            this._model.clear().set(this._getModelValue());
        },

        /**
         * Init the external elements (loading bar and notification widget)
         * @private
         */
        _initExternalElements : function documentController_initExternalElements() {
            this.$loading = $(".dcpLoading").dcpLoading();
            this.$notification = $('body').dcpNotification(); // active notification
        },

        /**
         * Bind the model event
         *
         * Re-trigger the event
         *
         * @private
         */
        _initModelEvents : function documentController_initEvents() {
            var currentWidget = this;
            this._model.listenTo(this._model, "invalid", function documentController_triggerShowInvalid(model, error) {
                var result = currentWidget._triggerControllerEvent("error",
                    currentWidget._model.getProperties(), error);
                if (result) {
                    currentWidget.$notification.dcpNotification("showError", error);
                }
            });
            this._model.listenTo(this._model, "showError", function documentController_triggerShowError(error) {
                var result = currentWidget._triggerControllerEvent("error",
                    currentWidget._model.getProperties(), error);
                if (result) {
                    currentWidget.$notification.dcpNotification("showError", error);
                }
            });
            this._model.listenTo(this._model, "sync", function documentController_triggerSync() {
                currentWidget.options.initid = currentWidget._model.id;
                currentWidget.options.viewId = currentWidget._model.get("viewId");
                currentWidget.options.revision = currentWidget._model.get("revision");
                currentWidget.element.data(currentWidget._getModelValue());
                currentWidget._initActivatedConstraint();
                currentWidget._initActivatedEvents({launchReady : false});
            });
            this._model.listenTo(this._model, "beforeClose", function documentController_triggerBeforeClose(event) {
                if (currentWidget.initialLoaded !== false) {
                    event.prevent = !currentWidget._triggerControllerEvent("beforeClose",
                        currentWidget._model.getProperties(true));
                }
            });
            this._model.listenTo(this._model, "close", function documentController_triggerClose() {
                if (currentWidget.initialLoaded !== false) {
                    currentWidget._triggerControllerEvent("close",
                        currentWidget._model.getProperties(true));
                }
            });
            this._model.listenTo(this._model, "beforeSave", function documentController_triggerBeforeSave(event) {
                event.prevent = !currentWidget._triggerControllerEvent("beforeSave",
                    currentWidget._model.getProperties(true));
            });
            this._model.listenTo(this._model, "afterSave", function documentController_triggerAfterSave(event) {
                currentWidget._triggerControllerEvent("afterSave",
                    currentWidget._model.getProperties(true));
            });
            this._model.listenTo(this._model, "beforeDelete", function documentController_triggerBeforeDelete(event) {
                event.prevent = !currentWidget._triggerControllerEvent("beforeDelete",
                    currentWidget._model.getProperties(true));
            });
            this._model.listenTo(this._model, "afterDelete", function documentController_triggerAfterDelete(event) {
                currentWidget._triggerControllerEvent("afterDelete",
                    currentWidget._model.getProperties(true));
            });
            this._model.listenTo(this._model, "validate", function documentController_triggerValidate(event) {
                event.prevent = !currentWidget._triggerControllerEvent("validate", currentWidget._model.getProperties());
            });
            this._model.listenTo(this._model, "changeValue", function documentController_triggerChangeValue(options) {
                var currentAttribute = currentWidget.getAttribute(options.attributeId);
                currentWidget._triggerAttributeControllerEvent("change", currentAttribute,
                    currentWidget._model.getProperties(),
                    currentAttribute,
                    currentAttribute.getValue("all")
                );
            });
            this._model.listenTo(this._model, "attributeRender", function documentController_triggerAttributeRender(attributeId) {
                var currentAttribute = currentWidget.getAttribute(attributeId);
                currentWidget._triggerAttributeControllerEvent("attributeReady", currentAttribute,
                    currentWidget._model.getProperties(),
                    currentAttribute
                );
            });
            this._model.listenTo(this._model, "arrayModified", function documentController_triggerArrayModified(options) {
                var currentAttribute = currentWidget.getAttribute(options.attributeId);
                currentWidget._triggerAttributeControllerEvent("arrayModified", currentAttribute,
                    currentWidget._model.getProperties(),
                    currentAttribute,
                    options.type,
                    options.options
                );
            });
            this._model.listenTo(this._model, "internalLinkSelected", function documentController_triggerInternalLinkSelected(event, options) {
                event.prevent = !currentWidget._triggerControllerEvent("documentLinkSelected",
                    currentWidget._model.getProperties(),
                    options
                );
            });
            this._model.listenTo(this._model, "helperSearch", function documentController_triggerHelperSearch(event, attrid, options) {
                var currentAttribute = currentWidget.getAttribute(attrid);
                event.prevent = !currentWidget._triggerAttributeControllerEvent("helperSearch", currentAttribute,
                    currentWidget._model.getProperties(),
                    currentAttribute,
                    options
                );
            });
            this._model.listenTo(this._model, "helperResponse", function documentController_triggerHelperResponse(event, attrid, options) {
                var currentAttribute = currentWidget.getAttribute(attrid);
                event.prevent = !currentWidget._triggerAttributeControllerEvent("helperResponse", currentAttribute,
                    currentWidget._model.getProperties(),
                    currentAttribute,
                    options
                );
            });
            this._model.listenTo(this._model, "helperSelect", function documentController_triggerHelperSelect(event, attrid, options) {
                var currentAttribute = currentWidget.getAttribute(attrid);
                event.prevent = !currentWidget._triggerAttributeControllerEvent("helperSelect", currentAttribute,
                    currentWidget._model.getProperties(),
                    currentAttribute,
                    options
                );
            });
            this._model.listenTo(this._model, "constraint", function documentController_triggerConstraint(attribute, response) {
                var currentAttribute = currentWidget.getAttribute(attribute),
                    currentModel = currentWidget._model.getProperties();
                _.each(currentWidget.activatedConstraint, function triggerCurrentConstraint(currentConstraint) {
                    if (currentConstraint.attributeCheck(currentModel, currentAttribute)) {
                        currentConstraint.constraintCheck(
                            response,
                            currentModel,
                            currentAttribute,
                            currentAttribute.getValue("all")
                        );
                    }
                });
            });
        },

        /**
         * Bind the view
         * Re-trigger the events
         *
         * @private
         */
        _initViewEvents : function documentController_initViewEvents() {
            var currentWidget = this;
            this.view.on("cleanNotification", function documentController_triggerCleanNotification() {
                currentWidget.$notification.dcpNotification("clear");
            });
            this.view.on('loading', function documentController_triggerLoading(data) {
                currentWidget.$loading.dcpLoading('setPercent', data);
            });
            this.view.on('loaderShow', function documentController_triggerLoaderShow() {
                console.time("xhr+render document view");
                currentWidget.$loading.dcpLoading('show');
            });
            this.view.on('loaderHide', function documentController_triggerHide() {
                currentWidget.$loading.dcpLoading('hide');
            });
            this.view.on('partRender', function documentController_triggerPartRender() {
                currentWidget.$loading.dcpLoading('addItem');
            });
            this.view.on('renderDone', function documentController_triggerRenderDone() {
                console.timeEnd("xhr+render document view");
                currentWidget.$loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
                currentWidget.initialLoaded = true;
                currentWidget._triggerControllerEvent("ready", currentWidget._model.getProperties());
                _.delay(function () {
                    currentWidget.$loading.dcpLoading("hide");
                    console.timeEnd('main');
                }, 250);
            });
            this.view.on("showMessage", function documentController_triggerShowMessage(message) {
                var result = currentWidget._triggerControllerEvent("message",
                    currentWidget._model.getProperties(), message);
                if (result) {
                    currentWidget.$notification.dcpNotification("show", message.type, message);
                }
            });
            this.view.on("showSuccess", function documentController_triggerShowSuccess(message) {
                var result = currentWidget._triggerControllerEvent("message",
                    currentWidget._model.getProperties(), message);
                if (result) {
                    currentWidget.$notification.dcpNotification("showSuccess", message);
                }
            });
            this.view.on("reinit", function documentController_triggerReinit() {
                currentWidget._initModel(currentWidget._getModelValue());
                currentWidget._initView();
                currentWidget._model.fetch();
            });
        },

        /**
         * Init the pushstate router
         *
         * @private
         */
        _initRouter : function documentController_initRouter() {
            if (window.history && history.pushState) {
                Backbone.history.start({pushState : true});
            } else {
                //For browser without API history
                Backbone.history.start();
            }
            this.router = new Router({document : this._model});
        },

        /**
         * Get a backbone model of an attribute
         *
         * @param attributeId
         * @returns {*}
         */
        _getAttributeModel : function documentController_getAttributeModel(attributeId) {
            var attribute = this._model.get("attributes").get(attributeId);
            if (!attribute) {
                throw new Error("The attribute " + attributeId + " doesn't exist");
            }
            return attribute;
        },

        _getRenderedAttributes : function documentController_getRenderedAttributes() {
            return this._model.get("attributes").filter(function documentController_getRenderedAttribute(currentAttribute) {
                return currentAttribute.haveView();
            });
        },

        /**
         * Get max index of an array
         *
         * @param attributeArray
         * @returns {*}
         */
        _getMaxIndex : function documentController_getMaxIndex(attributeArray) {
            return _.size(attributeArray.get("content").max(function documentController_getMax(currentAttr) {
                return _.size(currentAttr.get("attributeValue"));
            }).get("attributeValue"));
        },

        /**
         * Activate constraint on the current document
         * Used on the fetch of a new document
         *
         */
        _initActivatedConstraint : function documentController_initActivatedConstraint() {
            var currentDocumentProperties = this._model.getProperties();
            this.activatedConstraint = _.filter(this.options.constraintList, function documentController_getActivatedConstraint(currentConstraint) {
                return currentConstraint.documentCheck(currentDocumentProperties);
            });
        },

        /**
         * Activate events on the current document
         * Used on the fetch of a new document
         */
        _initActivatedEvents : function documentController_initActivatedEvents(options) {
            var currentDocumentProperties = this._model.getProperties(), currentWidget = this;
            options = options || {};
            this.activatedEvent = _.filter(this.options.eventList, function documentController_getActivatedEvent(currentEvent) {
                if (!_.isFunction(currentEvent.documentCheck)) {
                    return true;
                }
                return currentEvent.documentCheck(currentDocumentProperties);
            });
            //Trigger new added ready event
            if (this.initialLoaded !== false && options.launchReady !== false) {
                this._triggerControllerEvent("ready", currentDocumentProperties);
                _.each(this._getRenderedAttributes(), function documentController_triggerRenderedAttributes(currentAttribute) {
                    currentAttribute = currentWidget.getAttribute(currentAttribute.id);
                    currentWidget._triggerAttributeControllerEvent("attributeReady", currentAttribute,
                        currentDocumentProperties,
                        currentAttribute
                    );
                });
            }
        },

        _addAndInitNewEvents : function documentController_addAndInitNewEvents(newEvent) {
            var currentDocumentProperties = this._model.getProperties(), currentWidget = this, event;
            this.options.eventList.push(newEvent);
            // Check if the event is for the current document
            if (!_.isFunction(newEvent.documentCheck) || newEvent.documentCheck(currentDocumentProperties)) {
                this.activatedEvent.push(newEvent);
                // Check if we need to manually trigger this callback (late registered : only for ready events)
                if (this.initialLoaded !== false) {
                    if (newEvent.eventType === "ready") {
                        event = $.Event(newEvent.eventType);
                        event.target = currentWidget.element;
                        try {
                            // add element as function context
                            newEvent.eventCallback.call(currentWidget.element, event, currentDocumentProperties);
                        } catch (e) {
                            console.error(e);
                        }

                    }
                    if (newEvent.eventType === "attributeReady") {
                        event = $.Event(newEvent.eventType);
                        event.target = currentWidget.element;
                        _.each(this._getRenderedAttributes(), function documentController_triggerRenderedAttributes(currentAttribute) {
                            currentAttribute = currentWidget.getAttribute(currentAttribute.id);
                            if (!_.isFunction(newEvent.attributeCheck) || newEvent.attributeCheck(currentAttribute)) {
                                try {
                                    // add element as function context
                                    newEvent.eventCallback.call(currentWidget.element, event, currentDocumentProperties, currentAttribute);
                                } catch (e) {
                                    console.error(e);
                                }

                            }
                        });
                    }
                }
            }
        },

        /**
         * Trigger attribute event
         *
         * Similar at trigger document event with a constraint on attribute
         *
         * @param eventName
         * @param attributeInternalElement
         * @returns {boolean}
         */
        _triggerAttributeControllerEvent : function documentController_triggerAttributeControllerEvent(eventName, attributeInternalElement) {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 2), event = $.Event(eventName), externalEventArgument;
            event.target = currentWidget.element;
            // internal event trigger
            args.unshift(event);
            _.chain(this.activatedEvent).filter(function documentController__filterUsableEvents(currentEvent) {
                // Check by eventType (only call callback with good eventType)
                if (currentEvent.eventType === eventName) {
                    //Check with attributeCheck if the function exist
                    if (!_.isFunction(currentEvent.attributeCheck)) {
                        return true;
                    }
                    return currentEvent.attributeCheck(attributeInternalElement);
                }
                return false;
            }).each(function documentController_applyCallBack(currentEvent) {
                try {
                    currentEvent.eventCallback.apply(currentWidget.element, args);
                } catch (e) {
                    if (window.dcp.logger) {
                        window.dcp.logger(e);
                    } else {
                        console.error(e);
                    }
                }
            });
            externalEventArgument = Array.prototype.slice.call(arguments, 0);
            externalEventArgument.splice(1, 1);
            currentWidget._triggerExternalEvent.apply(currentWidget, externalEventArgument);
            return !event.isDefaultPrevented();
        },

        /**
         * Trigger a controller event
         * That kind of event are only for this widget
         *
         * @param eventName
         * @returns {boolean}
         */
        _triggerControllerEvent : function documentController_triggerControllerEvent(eventName) {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 1), event = $.Event(eventName);
            event.target = currentWidget.element;
            // internal event trigger
            args.unshift(event);
            _.chain(this.activatedEvent).filter(function documentController_getEventName(currentEvent) {
                return currentEvent.eventType === eventName;
            }).each(function documentController_triggerAnEvent(currentEvent) {
                try {
                    currentEvent.eventCallback.apply(currentWidget.element, args);
                } catch (e) {
                    if (window.dcp.logger) {
                        window.dcp.logger(e);
                    } else {
                        console.error(e);
                    }
                }
            });
            currentWidget._triggerExternalEvent.apply(currentWidget, arguments);
            return !event.isDefaultPrevented();
        },

        /**
         * Trigger event as jQuery standard events (all events are prefixed by document)
         *
         * @param type
         */
        _triggerExternalEvent : function documentController_triggerExternalEvent(type) {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 1), event = $.Event(type);
            //prepare argument for widget event trigger (we want type, event, data)
            // add the eventObject
            args.unshift(event);
            // add the type
            args.unshift(type);
            // concatenate other argument in one element (to respect widget pattern)
            args[2] = args.slice(2);
            // suppress other arguments (since they have been concatened)
            args = args.slice(0, 3);
            //trigger external event
            currentWidget._trigger.apply(currentWidget, args);
        },

        /***************************************************************************************************************
         * External function
         **************************************************************************************************************/
        /**
         * Reinit the current document (close it and re-open it)
         */
        reinitDocument :        function documentControllerReinitDocument() {
            this._reinitModel();
            this._model.fetch();
        },

        /**
         * Fetch a new document
         * @param options object {"initid" : int, "revision" : int, "viewId" : string}
         */
        fetchDocument : function documentControllerFetchDocument(options) {
            options = _.isUndefined(options) ? {} : options;
            if (!_.isObject(options)) {
                throw new Error('Fetch argument must be an object {"initid":, "revision": , "viewId": }');
            }
            options = _.defaults(options, {
                "revision" : -1,
                "viewId" :   "!defaultConsultation"
            });
            this.options = _.defaults(options, this.options);
            this.reinitDocument();
        },

        /**
         * Save the current document
         * Reload the interface in the same mode
         */
        saveDocument : function documentControllerSave() {
            this._model.save();
        },

        /**
         * Delete the current document
         * Reload the interface in the same mode
         */
        deleteDocument : function documentControllerDelete() {
            var currentWidget = this, destroy = this._model.destroy();
            destroy.done(function documentController_destroyer() {
                currentWidget._initModel(currentWidget._getModelValue());
                currentWidget._initView();
                currentWidget._model.fetch();
            });
        },

        /**
         * Get a property value
         *
         * @param property
         * @returns {*}
         */
        getProperty : function documentControllerGetDocumentProperty(property) {
            return this._model.getProperties()[property];
        },

        /**
         * Get all the properties
         * @returns {*}
         */
        getProperties : function documentControllerGetDocumentProperties() {
            return this._model.getProperties();
        },

        /**
         * Get the attribute interface object
         *
         * @param attributeId
         * @returns {AttributeInterface}
         */
        getAttribute : function documentControllerGetAttribute(attributeId) {
            return new AttributeInterface(this._getAttributeModel(attributeId));
        },

        /**
         * Get all the attributes of the current document
         *
         * @returns [AttributeInterface]
         */
        getAttributes : function documentControllerGetAttributes() {
            return this._model.get("attributes").map(function documentController_mapAttribute(currentAttribute) {
                    return new AttributeInterface(currentAttribute);
                }
            );
        },

        /**
         * Get an attribute value
         *
         * @param attributeId
         * @param type string (current|previous|initial|all) what kind of value (default : current)
         * @returns {*}
         */
        getValue : function documentControllerGetValue(attributeId, type) {
            var attribute = new AttributeInterface(this._getAttributeModel(attributeId));
            return attribute.getValue(type);
        },

        /**
         * Get all the values
         *
         * @returns {*|{}}
         */
        getValues : function documentControllerGetValues() {
            return this._model.getValues();
        },

        /**
         * Set a value
         * Trigger a change event
         *
         * @param attributeId string attribute identifier
         * @param value object { "value" : *, "displayValue" : *}
         * @returns {*}
         */
        setValue : function documentControllerSetValue(attributeId, value) {
            var attribute = new AttributeInterface(this._getAttributeModel(attributeId));
            return attribute.setValue(value);
        },

        /**
         * Add a row to an array
         *
         * @param attributeId string attribute array
         * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
         */
        appendArrayRow : function documentControllerAddArrayRow(attributeId, values) {
            var attribute = this._getAttributeModel(attributeId);
            if (attribute.get("type") !== "array") {
                throw new Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            if (!_.isObject(values)) {
                throw new Error("Values must be an object where each properties is an attribute of the array for " + attributeId);
            }
            attribute.get("content").each(function documentController_addACell(currentAttribute) {
                var currentValue = values[currentAttribute.id];
                if (_.isUndefined(currentValue)) {
                    return;
                }
                currentValue = _.defaults(currentValue, {value : "", displayValue : ""});
                currentAttribute.addValue(currentValue);
            });
        },

        /**
         * Add a row before another row
         *
         * @param attributeId string attribute array
         * @param values object { "attributeId" : { "value" : *, "displayValue" : * }, ...}
         * @param index int index of the row
         */
        insertBeforeArrayRow : function documentControllerInsertBeforeArrayRow(attributeId, values, index) {
            var attribute = this._getAttributeModel(attributeId), maxValue;
            if (attribute.get("type") !== "array") {
                throw new Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            if (!_.isObject(values)) {
                throw new Error("Values must be an object where each properties is an attribute of the array for " + attributeId);
            }
            maxValue = this._getMaxIndex(attribute);
            if (index < 0 || index > maxValue) {
                throw new Error("Index must be between 0 and " + maxValue);
            }
            attribute.get("content").each(function documentController_addACell(currentAttribute) {
                var currentValue = values[currentAttribute.id];
                if (!_.isUndefined(currentValue)) {
                    currentValue = _.defaults(currentValue, {value : "", displayValue : ""});
                }
                currentAttribute.addIndexedValue(currentValue, index);
            });
        },

        /**
         * Remove an array row
         * @param attributeId string attribute array
         * @param index int index of the row
         */
        removeArrayRow : function documentControllerRemoveArrayRow(attributeId, index) {
            var attribute = this._getAttributeModel(attributeId), maxIndex;
            if (attribute.get("type") !== "array") {
                throw Error("Attribute " + attributeId + " must be an attribute of type array");
            }
            maxIndex = this._getMaxIndex(attribute) - 1;
            if (index < 0 || index > maxIndex) {
                throw Error("Index must be between 0 and " + maxIndex + " for " + attributeId);
            }
            attribute.get("content").each(function documentController_removeACell(currentAttribute) {
                currentAttribute.removeIndexValue(index);
            });
            attribute.removeIndexedLine(index);
        },

        /**
         * Add a constraint to the widget
         *
         * @param options object { "name" : string, "documentCheck": function}
         * @param callback function callback called when the event is triggered
         * @returns {*}
         */
        addConstraint : function documentControlleraddConstraint(options, callback) {
            var parameters;
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            if (!_.isFunction(callback)) {
                throw new Error("An event need a callback");
            }
            parameters = _.defaults(options, {
                "documentCheck" :   function documentController_defaultDocumentCheck() {
                    return true;
                },
                "attributeCheck" :  function documentController_defaultAttributeCheck() {
                    return true;
                },
                "constraintCheck" : callback,
                "name" :            _.uniqueId("constraint")
            });
            this.options.constraintList.push(parameters);
            this._initActivatedConstraint();
            return parameters.name;
        },

        /**
         * List the constraint of the widget
         *
         * @returns {*}
         */
        listConstraints : function documentControllerListConstraint() {
            return this.options.constraintList.splice(0);
        },

        /**
         * Remove a constraint of the widget
         *
         * @param constraintName
         * @returns {*}
         */
        removeConstraint : function documentControllerRemoveConstraint(constraintName) {
            var removed = [],
                testRegExp = new RegExp("\\" + constraintName + "$");
            this.options.constraintList = _.filter(this.options.constraintList, function documentController_removeConstraint(currentConstrait) {
                if (currentConstrait.name === constraintName || testRegExp.test(currentConstrait.name)) {
                    removed.push(currentConstrait);
                    return false;
                }
                return true;
            });
            this._initActivatedConstraint();
            return removed;
        },

        /**
         * Add an event to the widget
         *
         * @param eventType string kind of event
         * @param options object { "name" : string, "documentCheck": function}
         * @param callback function callback called when the event is triggered
         * @returns {*|Window.options.name}
         */
        addEvent : function documentControllerAddEvent(eventType, options, callback) {
            var eventContent;
            //options is facultative and the callback can be the second parameters
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            // the first parameters can be the final object (chain removeEvent and addEvent)
            if (_.isObject(eventType) && _.isUndefined(options) && _.isUndefined(callback)) {
                eventContent = eventType;
                if (!eventContent.name) {
                    throw new Error("When an event is initiated with a single object, this object needs to have the name property ".JSON.stringify(eventContent));
                }
            } else {
                eventContent = _.defaults(options, {
                    "name" :          _.uniqueId("event_" + eventType),
                    "eventType" :     eventType,
                    "eventCallback" : callback
                });
            }
            // the eventType must be one the list
            if (!_.isString(eventContent.eventType) || !_.find(eventList, function documentController_CheckEventType(currentEvent) {
                    return currentEvent === eventContent.eventType;
                })) {
                throw new Error("The event type " + eventContent.eventType + " is not known. It must be one of " + eventList.join(" ,"));
            }
            // callback is mandatory and must be a function
            if (!_.isFunction(eventContent.eventCallback)) {
                throw new Error("An event needs a callback that is a function");
            }

            this._addAndInitNewEvents(eventContent);
            // return the name of
            return options.name;
        },

        /**
         * List of the events of the current widget
         *
         * @returns {*}
         */
        listEvents : function documentControllerListEvents() {
            return this.options.eventList.slice();
        },

        /**
         * Remove an event of the current widget
         *
         * @param eventName string can be an event name or a namespace
         * @returns {*}
         */
        removeEvent : function documentControllerRemoveEvent(eventName) {
            var removed = [], testRegExp = new RegExp("\\" + eventName + "$");
            this.options.eventList = _.filter(this.options.eventList, function documentController_removeCurrentEvent(currentEvent) {
                if (currentEvent.name === eventName || testRegExp.test(currentEvent.name)) {
                    removed.push(currentEvent);
                    return false;
                }
                return true;
            });
            this._initActivatedEvents({"launchReady" : false});
            return removed;
        },

        /**
         * Hide a visible attribute
         *
         * @param attributeId
         */
        hideAttribute : function documentControllerHideAttribute(attributeId) {
            this._getAttributeModel(attributeId).trigger("hide");
        },
        /**
         * show a visible attribute (previously hidden)
         *
         * @param attributeId
         */
        showAttribute : function documentControllerShowAttribute(attributeId) {
            this._getAttributeModel(attributeId).trigger("show");
        },

        /**
         * Display a message to the user
         *
         * @param message
         */
        showMessage : function documentControllerShowMessage(message) {
            if (_.isString(message)) {
                message = {
                    type :    "info",
                    message : message
                };
            }
            if (_.isObject(message)) {
                message = _.defaults(message, {
                    type : "info"
                });
            }
            this.$notification.dcpNotification("show", message.type, message);
        },

        /**
         * Add an error message to an attribute
         *
         * @param attributeId
         * @param message
         * @param index
         */
        setAttributeErrorMessage : function documentControllersetAttributeErrorMessage(attributeId, message, index) {
            this._getAttributeModel(attributeId).setErrorMessage(message, index);
        },

        /**
         * Clean the error message of an attribute
         *
         * @param attributeId
         * @param index
         */
        cleanAttributeErrorMessage : function documentControllercleanAttributeErrorMessage(attributeId, index) {
            this._getAttributeModel(attributeId).setErrorMessage(null, index);
        }

    });

    return $.fn.documentController;
});