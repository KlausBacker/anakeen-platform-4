define([
    'jquery',
    'underscore',
    'backbone',
    'routers/router',
    'models/mDocument',
    'controllerObjects/attributeInterface',
    'views/document/vDocument',
    'widgets/widget',
    'widgets/window/wConfirm',
    'widgets/window/wLoading'
], function ($, _, Backbone, Router, DocumentModel, AttributeInterface, DocumentView) {
    'use strict';

    var eventList = ["ready", "close", "save", "change", "message", "error", "validate", "delete"];

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
            this._model.listenTo(this._model, "invalid", function showInvalid(model, error) {
                var result = currentWidget._triggerControllerEvent("error",
                    currentWidget._model.getProperties(), error);
                if (result) {
                    currentWidget.$notification.dcpNotification("showError", error);
                }
            });
            this._model.listenTo(this._model, "showError", function showError(error) {
                var result = currentWidget._triggerControllerEvent("error",
                    currentWidget._model.getProperties(), error);
                if (result) {
                    currentWidget.$notification.dcpNotification("showError", error);
                }
            });
            this._model.listenTo(this._model, "sync", function () {
                currentWidget.options.initid = currentWidget._model.id;
                currentWidget.options.viewId = currentWidget._model.get("viewId");
                currentWidget.options.revision = currentWidget._model.get("revision");
                currentWidget.element.data(currentWidget._getModelValue());
                currentWidget._initActivatedConstraint();
                currentWidget._initActivatedEvents();
            });
            this._model.listenTo(this._model, "close", function (event) {
                if (currentWidget.initialLoaded !== false) {
                    event.prevent = !currentWidget._triggerControllerEvent("close", currentWidget._model.getProperties(true));
                }
            });
            this._model.listenTo(this._model, "save", function (event) {
                event.prevent = !currentWidget._triggerControllerEvent("save", currentWidget._model.getProperties(true));
            });
            this._model.listenTo(this._model, "delete", function (event) {
                event.prevent = !currentWidget._triggerControllerEvent("delete", currentWidget._model.getProperties(true));
            });
            this._model.listenTo(this._model, "validate", function (event) {
                event.prevent = !currentWidget._triggerControllerEvent("validate", currentWidget._model.getProperties());
            });
            this._model.listenTo(this._model, "changeValue", function (options) {
                var currentAttribute = currentWidget.getAttribute(options.attributeId);
                currentWidget._triggerControllerEvent("change",
                    currentWidget._model.getProperties(),
                    currentAttribute,
                    currentAttribute.getValue("all")
                );
            });
            this._model.listenTo(this._model, "constraint", function (attribute, response) {
                var currentAttribute = currentWidget.getAttribute(attribute),
                    currentModel = currentWidget._model.getProperties();
                _.each(currentWidget.activatedConstraint, function (currentConstraint) {
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
            this.view.on("cleanNotification", function () {
                currentWidget.$notification.dcpNotification("clear");
            });
            this.view.on('loading', function (data) {
                currentWidget.$loading.dcpLoading('setPercent', data);
            });
            this.view.on('loaderShow', function () {
                console.time("xhr+render document view");
                currentWidget.$loading.dcpLoading('show');
            });
            this.view.on('loaderHide', function () {
                currentWidget.$loading.dcpLoading('hide');
            });
            this.view.on('partRender', function () {
                currentWidget.$loading.dcpLoading('addItem');
            });
            this.view.on('renderDone', function () {
                console.timeEnd("xhr+render document view");
                currentWidget._triggerControllerEvent("ready",
                    currentWidget._model.getProperties());
                currentWidget.$loading.dcpLoading("setPercent", 100).addClass("dcpLoading--hide");
                currentWidget.initialLoaded = true;
                _.delay(function () {
                    currentWidget.$loading.dcpLoading("hide");
                    console.timeEnd('main');
                }, 250);
            });
            this.view.on("showMessage", function showMessage(message) {
                var result = currentWidget._triggerControllerEvent("message",
                    currentWidget._model.getProperties(), message);
                if (result) {
                    currentWidget.$notification.dcpNotification("show", message.type, message);
                }
            });
            this.view.on("showSuccess", function showSuccess(message) {
                var result = currentWidget._triggerControllerEvent("message",
                    currentWidget._model.getProperties(), message);
                if (result) {
                    currentWidget.$notification.dcpNotification("showSuccess", message);
                }
            });
            this.view.on("reinit", function reinit() {
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
            Backbone.history.start({pushState : true});
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

        /**
         * Get max index of an array
         *
         * @param attributeArray
         * @returns {*}
         */
        _getMaxIndex : function documentController_getMaxIndex(attributeArray) {
            return _.size(attributeArray.get("content").max(function (currentAttr) {
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
            this.activatedConstraint = _.filter(this.options.constraintList, function (currentConstraint) {
                return currentConstraint.documentCheck(currentDocumentProperties);
            });
        },

        /**
         * Activate events on the current document
         * Used on the fetch of a new document
         */
        _initActivatedEvents : function documentController_initActivatedEvents() {
            var currentDocumentProperties = this._model.getProperties();
            this.activatedEvent = _.filter(this.options.eventList, function (currentEvent) {
                return currentEvent.documentCheck(currentDocumentProperties);
            });
            //Trigger new added ready event
            if (this.initialLoaded !== false) {
                this._triggerControllerEvent("ready");
            }
        },

        /**
         * Trigger a controller event
         * That kind of event are only for this widget
         *
         * @param eventName
         * @returns {boolean}
         */
        _triggerControllerEvent : function documentController_triggerControllerEvent(eventName) {
            var currentWidget = this, args = Array.prototype.slice.call(arguments, 1), event = $.Event(eventName), onArgs;
            event.target = currentWidget.element;
            // internal event trigger
            args.unshift(event);
            _.chain(this.activatedEvent).filter(function (currentEvent) {
                return currentEvent.eventType === eventName;
            }).some(function (currentEvent) {
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
            //prepare argument for widget event trigger
            // duplicate
            onArgs = args.slice(0);
            // add event type
            onArgs.unshift(eventName);
            // concatenate other argument in one element
            onArgs[2] = onArgs.slice(2);
            // suppress other arguments
            onArgs = onArgs.slice(0, 3);
            //trigger external event
            currentWidget._trigger.apply(currentWidget, onArgs);
            return !event.isDefaultPrevented();
        },

        /***************************************************************************************************************
         * External function
         **************************************************************************************************************/
        /**
         * Reinit the current document (close it and re-open it)
         */
        reinitDocument :          function documentControllerReinitDocument() {
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
            return this._model.get("attributes").map(function (currentAttribute) {
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
            attribute.get("content").each(function addACell(currentAttribute) {
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
            attribute.get("content").each(function addACell(currentAttribute) {
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
            attribute.get("content").each(function removeACell(currentAttribute) {
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
                "documentCheck" :   function () {
                    return true;
                },
                "attributeCheck" :  function () {
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
            this.options.constraintList = _.filter(this.options.constraintList, function (currentConstrait) {
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
            //options is facultative
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            if (!_.isString("event") || !_.find(eventList, function (currentEvent) {
                    return currentEvent === eventType;
                })) {
                throw new Error("The event type " + eventType + " is not known. It must be one of " + eventList.join(" ,"));
            }
            if (!_.isFunction(callback)) {
                throw new Error("An event need a callback");
            }
            options = _.defaults(options, {
                "name" :          _.uniqueId("event_" + eventType),
                "eventType" :     eventType,
                "eventCallback" : callback,
                "documentCheck" : function () {
                    return true;
                }
            });
            this.options.eventList.push(options);
            this._initActivatedEvents();
            return options.name;
        },

        /**
         * List of the events of the current widget
         *
         * @returns {*}
         */
        listEvents : function documentControllerListEvents() {
            return this.options.eventList.splice(0);
        },

        /**
         * Remove an event of the current widget
         *
         * @param eventName string can be an event name or a namespace
         * @returns {*}
         */
        removeEvent : function documentControllerRemoveEvent(eventName) {
            var removed = [], testRegExp = new RegExp("\\" + eventName + "$");
            this.options.eventList = _.filter(this.options.eventList, function (currentEvent) {
                if (currentEvent.name === eventName || testRegExp.test(currentEvent.name)) {
                    removed.push(currentEvent);
                    return false;
                }
                return true;
            });
            this._initActivatedEvents();
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