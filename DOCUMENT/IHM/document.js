/*global define, console*/
define([
    'underscore',
    'jquery',
    'dcpDocument/widgets/widget'
], function (_, $)
{
    'use strict';

    var eventList = ["ready", "change", "message", "error", "validate", "attributeReady",
        "helperSearch", "helperResponse", "helperSelect",
        "arrayModified", "actionClick",
        "beforeClose", "close",
        "beforeSave", "afterSave",
        "beforeDelete", "afterDelete",
        "failChangeState", "successChangeState",
        "beforeDisplayChangeState", "afterDisplayChangeState",
        "beforeChangeState", "beforeChangeStateClose"
    ];

    //Create a new kind of event
    var ErrorNotLoaded = function dcpDocument_ErrorNotLoaded(message)
    {
        this.name = 'WidgetDocumentNotLoaded';
        this.message = message || 'The document widget is not loaded, wait for the documentloaded event';
    };
    ErrorNotLoaded.prototype = Object.create(Error.prototype);
    ErrorNotLoaded.prototype.constructor = ErrorNotLoaded;

    $.widget("dcp.document", {

        _template: _.template('<iframe class="dcpDocumentWrapper"  style="border : 0;" src="?app=DOCUMENT&id=<%= options.initid %><% if (options.viewId) { %>&vid=<%= options.viewId %><% } %><% if (options.revision) { %>&revision=<%= options.revision %><% } %>"></iframe>'),

        defaults: {
            "resizeMarginHeight": 3,
            "resizeMarginWidth": 0,
            "resizeDebounceTime": 50,
            "withoutResize": false
        },

        /**
         * Create the widget
         *
         * Check if initid is present
         */
        _create: function dcpDocument_create()
        {
            if (!this.options.initid) {
                throw new Error("Unable to create a document without initid");
            }
            this.options = _.extend({}, this.defaults, this.options);
            this.options.eventListener = {};
            this.options.constraintList = {};
            this._render();
            this._bindEvents();
        },

        /**
         * Create the iframe with the content and register to load event
         */
        _render: function dcpDocument_render()
        {
            var $iframe, currentWidget = this, documentWindow;
            //inject the iframe
            this.element.empty().append(this._template({options: this.options}));
            //bind the internal controller to the documentWidget
            $iframe = this.element.find(".dcpDocumentWrapper");
            //Listen the load to the iframe (initial JS added and page loaded)


            if ($iframe.length > 0) {
                documentWindow = $iframe[0].contentWindow;


                // This event is used when use a hard link (aka href anchor) to change document
                // It is load also the first time
                $iframe.on("load", function dcpDocument_setReadyEvent()
                {
                    documentWindow.documentLoaded = function dcpDocument_loadedCallback(domNode)
                    {
                        //Re Bind the internalController function to the current widget
                        currentWidget._bindInternalWidget.call(currentWidget, domNode.data("dcpDocumentController"));
                    };

                    $(documentWindow).on("unload", function dcpDocument_setUnloadEvent()
                    {
                        currentWidget._unbindInternalWidget.call(currentWidget);
                    });
                });

            }
            this._resize();
        },

        /**
         * Suppress internal widget reference
         */
        _unbindInternalWidget: function dcpDocument_unbindInternalWidget()
        {
            this.element.data("internalWidgetInitialised", false);
            this.element.data("internalWidget", false);
            this._trigger("internalWidgetUnloaded");
        },

        /**
         * Bind the internal controller to the current widget
         * Reinit the constraint and the event
         *
         * @param internalController
         */
        _bindInternalWidget: function dcpDocument_bindInternalWidget(internalController)
        {
            if (!this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget", internalController);
                //Rebind event
                _.each(this.options.eventListener, function dcpDocument_bindEvent(currentEvent)
                {
                    internalController.addEventListener(currentEvent);
                });
                //Rebind constraint
                _.each(this.options.constraintList, function dcpDocument_bindEvent(currentConstaint)
                {
                    internalController.addConstraint(currentConstaint);
                });
                this.element.data("internalWidgetInitialised", true);
                this._trigger("loaded");
            }
        },

        /**
         * Add resize event
         */
        _bindEvents: function dcpDocument_bindEvents()
        {
            if (!this.options.withoutResize) {
                $(window).on("resize" + this.eventNamespace, _.debounce(_.bind(this._resize, this), parseInt(this.options.resizeDebounceTime, 10)));
                this._resize();
            }
        },

        /**
         * Compute the size of the widget
         */
        _resize: function dcpDocument_resize()
        {
            var event = this._trigger("autoresize"),
                $documentWrapper = this.element.find(".dcpDocumentWrapper"),
                currentWidget = this,
                element = this.element;
            //the computation can be done by an external function and default prevented
            if (!this.options.withoutResize && event) {
                //compute two times height (one for disapear horizontal scrollbar, two to get the actual size)
                //noinspection JSValidateTypes
                $documentWrapper.height(element.innerHeight() - parseInt(currentWidget.options.resizeMarginHeight, 10));
                //noinspection JSValidateTypes
                $documentWrapper.width(element.innerWidth() - parseInt(currentWidget.options.resizeMarginWidth, 10));
                //defer height computation to let the time to scrollbar disapear
                _.defer(function dcpDocument_computeHeight()
                {
                    //noinspection JSValidateTypes
                    $documentWrapper.height(element.innerHeight() - parseInt(currentWidget.options.resizeMarginHeight, 10));
                });
            }
        },

        /**
         * Destroy the widget
         */
        _destroy: function dcpDocument_destroy()
        {
            $(window).off(this.eventNamespace);
            this.element.empty();
            this._unbindInternalWidget();
            this._trigger("destroy");
            this._super();
        },

        /**
         * Check if event name is valid
         *
         * @param eventName string
         * @private
         */
        _checkEventName: function documentController_checkEventName(eventName)
        {
            if (_.isString(eventName) &&
                (eventName.indexOf("custom:") === 0 ||
                _.find(eventList, function documentController_CheckEventType(currentEventType)
                {
                    return currentEventType === eventName;
                })
                )) {
                return true;
            }
            throw new Error("The event type " + eventName + " is not known. It must be one of " + eventList.join(" ,"));
        },

        /**
         * Update options
         */
        options: function dcpDocument_options()
        {
            throw new Error("You cannot modify the options, you need to suppress the widget");
        },

        /**
         * Fetch a new document
         *
         * Use internal controller if ready
         * Re-render the widget if internal is not ready
         * @param options
         */
        fetchDocument: function dcpDocument_fetchDocument(options)
        {
            var internalWidget, currentWidget = this;
            _.each(_.pick(options, "initid", "revision", "viewId"), function dcpDocument_setNewOptions(value, key)
            {
                currentWidget.options[key] = value;
            });
            if (this.element.data("internalWidgetInitialised")) {
                internalWidget = this.element.data("internalWidget");
                internalWidget.fetchDocument.call(internalWidget, options);
            } else {
                this._render();
            }
        },

        /**
         * Reinit the document
         *
         */
        reinitDocument: function dcpDocument_reinitDocument()
        {
            this.fetchDocument();
        },

        /**
         * Add a new external event
         * The event is added in the widget and is auto-rebinded when the internal widget is reloaded
         *
         * @param eventType string|object type of the widget or an object event
         * @param options object|function conf of the event or callback
         * @param callback function callback
         * @returns {*}
         */
        addEventListener : function dcpDocument_addEventListener(eventType, options, callback)
        {
            var currentEvent, currentWidget = this;
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            // the first parameters can be the final object (chain removeEvent and addEvent)
            if (_.isObject(eventType) && _.isUndefined(options) && _.isUndefined(callback)) {
                currentEvent = eventType;
                if (!currentEvent.name) {
                    throw new Error("When an event is initiated with a single object, this object needs to have the name property ".JSON.stringify(currentEvent));
                }
            } else {
                currentEvent = _.defaults(options, {
                    "name": _.uniqueId("event_" + eventType),
                    "eventType": eventType,
                    "eventCallback": callback,
                    "externalEvent": true,
                    "once": false
                });
            }
            // the eventType must be one the list
            this._checkEventName(currentEvent.eventType);
            if (currentEvent.once === true) {
                currentEvent.eventCallback = _.wrap(currentEvent.eventCallback, function dcpDocument_onceWrapper(callback)
                {
                    try {
                        callback.apply(this, _.rest(arguments));
                    } catch (e) {
                        console.error(e);
                    }
                    currentWidget.removeEventListener(currentEvent.name);
                });
            }
            //Remove once property because already wrapped
            currentEvent.once = false;
            this.options.eventListener[currentEvent.name] = currentEvent;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").addEventListener(currentEvent);
            }
            return currentEvent.name;
        },

        /**
         * List of the events of the current widget
         *
         * @returns {*}
         */
        listEventListeners: function documentControllerListEvents()
        {
            if (this.element.data("internalWidgetInitialised")) {
                return this.element.data("internalWidget").listEventListeners();
            } else {
                return this.options.eventListener;
            }
        },

        /**
         * Remove the event of the widget list and the internal list (if internal is ready)
         *
         * @param eventName
         * @returns {Array}
         */
        removeEventListener: function dcpDocument_removeEventListener(eventName)
        {
            var removed = [],
                testRegExp = new RegExp("\\" + eventName + "$"), newList, eventList;
            newList = _.filter(this.options.eventListener, function dcpDocument_removeCurrentEvent(currentEvent)
            {
                if (currentEvent.name === eventName || testRegExp.test(currentEvent.name)) {
                    removed.push(currentEvent);
                    return false;
                }
                return true;
            });
            eventList = {};
            _.each(newList, function (currentEvent)
            {
                eventList[currentEvent.name] = currentEvent;
            });
            this.options.eventListener = eventList;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").removeEventListener(eventName, true);
            }
            return removed;
        },

        /**
         * Add a constraint
         * The constraint is added in the widget and is auto-rebinded when the internal widget is reloaded
         *
         * @param options
         * @param callback
         * @returns {*}
         */
        addConstraint: function dcpDocument_addConstraint(options, callback)
        {
            var parameters, currentWidget = this;
            if (_.isUndefined(callback) && _.isFunction(options)) {
                callback = options;
                options = {};
            }
            if (_.isObject(options) && _.isUndefined(callback)) {
                if (!options.name) {
                    throw new Error("When a constraint is initiated with a single object, this object needs to have the name property ".JSON.stringify(options));
                }
            } else {
                parameters = _.defaults(options, {
                    "documentCheck": function dcpDocument_defaultDocumentCheck()
                    {
                        return true;
                    },
                    "attributeCheck": function dcpDocument_defaultAttributeCheck()
                    {
                        return true;
                    },
                    "constraintCheck": callback,
                    "name": _.uniqueId("constraint"),
                    "externalConstraint": false,
                    "once": false
                });
            }
            if (!_.isFunction(parameters.constraintCheck)) {
                throw new Error("An event need a callback");
            }
            if (parameters.once === true) {
                parameters.eventCallback = _.wrap(parameters.constraintCheck, function dcpDocument_onceWrapper(callback)
                {
                    try {
                        callback.apply(this, _.rest(arguments));
                    } catch (e) {
                        console.error(e);
                    }
                    currentWidget.removeConstraint(parameters.name, parameters.externalConstraint);
                });
            }
            this.options.constraintList[parameters.name] = parameters;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").addConstraint(parameters);
            }
            return parameters.name;
        },
        /**
         * List the constraint of the widget
         *
         * @returns {*}
         */
        listConstraints: function documentControllerListConstraint()
        {
            if (this.element.data("internalWidgetInitialised")) {
                return this.element.data("internalWidget").listConstraints();
            } else {
                return this.options.constraintList;
            }
        },
        /**
         * Remove the constraint of the widget
         *
         * @param constraintName
         * @returns {Array}
         */
        removeConstraint: function dcpDocument_removeConstraint(constraintName)
        {
            var removed = [], newConstraintList, constraintList,
                testRegExp = new RegExp("\\" + constraintName + "$");
            newConstraintList = _.filter(this.options.constraintList, function dcpDocument_removeConstraint(currentConstrait)
            {
                if (currentConstrait.name === constraintName || testRegExp.test(currentConstrait.name)) {
                    removed.push(currentConstrait);
                    return false;
                }
                return true;
            });
            constraintList = {};
            _.each(newConstraintList, function dcpDocument_reinitConstraint(currentConstraint)
            {
                constraintList[currentConstraint.name] = currentConstraint;
            });
            this.options.constraintList = constraintList;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").removeConstraint(constraintName, true);
            }
            return removed;
        },

        isLoaded: function dcpDocument_isLoaded()
        {
            return this.element.data("internalWidgetInitialised");
        }

    });

    /**
     * Wrap the brigde that find the function to be executed
     * Search in the current widget if the function is here
     * Search in the internal widget (if ready to find the widget)
     *
     * @type {Function|function(): Function|function(): _Chain<T>|*}
     */
        //noinspection JSUnresolvedVariable
    $.fn.document = _.wrap($.fn.document, function (initialDocumentBridge, methodName)
    { // jshint ignore:line
        var isMethodCall, internalWidget;
        try {
            return initialDocumentBridge.apply(this, _.rest(arguments));
        } catch (error) {
            if (error.name === "noSuchMethodError") {
                isMethodCall = typeof methodName === "string";
                if (isMethodCall && !this.data("internalWidgetInitialised")) {
                    throw new ErrorNotLoaded();
                }
                internalWidget = this.data("internalWidget");
                if (_.isFunction(internalWidget[methodName]) && methodName.charAt(0) !== "_") {
                    return internalWidget[methodName].apply(internalWidget, _.rest(arguments, 2));
                }
            }
            throw error;
        }
    });

});
