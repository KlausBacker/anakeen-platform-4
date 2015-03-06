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
        "arrayModified", "documentLinkSelected",
        "beforeClose", "close",
        "beforeSave", "afterSave",
        "beforeDelete", "afterDelete",
        "failChangeState", "successChangeState",
        "beforeDisplayChangeState", "afterDisplayChangeState",
        "beforeChangeState", "beforeChangeStateClose"
    ];

    var ErrorNotReady = function ErrorNotReady(message)
    {
        this.name = 'WidgetDocumentNotReady';
        this.message = message || 'The document widget is not ready';
    };

    ErrorNotReady.prototype = Object.create(Error.prototype);
    ErrorNotReady.prototype.constructor = ErrorNotReady;

    $.widget("dcp.document", {

        _internalWidget: null,

        _template: _.template('<iframe class="dcpDocumentWrapper"  style="border : 0;" src="?app=DOCUMENT&id=<%= options.initid %><% if (options.viewId) { %> &vid=<%= options.viewId %> <% } %><% if (options.revision) { %> &revision=<%= options.revision %> <% } %>"></iframe>'),

        _create: function dcpDocument_create()
        {
            if (!this.options.initid) {
                throw new Error("Unable to create a document without initid");
            }
            this.options.eventList = {};
            this.options.constraintList = {};
            this._render();
            this._bindEvents();
        },

        _render: function dcpDocument_render()
        {
            var $iframe, currentWidget = this;
            //inject the iframe
            this.element.empty().append(this._template({options: this.options}));
            //bind the internal controller to the documentWidget
            $iframe = this.element.find(".dcpDocumentWrapper");
            //Listen the load to the iframe (initial JS added and page loaded)
            $iframe.on("load", function dcpDocument_setReadyEvent()
            {
                //Inject in the iframe window a callback function used by the internalController
                $iframe[0].contentWindow.documentLoaded = function dcpDocument_loadedCallback(domNode)
                {
                    //Bind the internalController function to the current widget
                    currentWidget._bindInternalWidget.call(currentWidget, domNode.data("dcpDocumentController"));
                };
                $iframe[0].contentWindow.documentUnloaded = function dcpDocument_unloadedCallback() {
                    currentWidget._trigger("unloaded");
                };
                currentWidget._unbindInternalWidget();
            }).trigger("load");
        },

        _unbindInternalWidget: function dcpDocument_unbindInternalWidget()
        {
            this.element.data("internalWidgetInitialised", false);
            this.element.data("internalWidget", false);
            this._trigger("internalWidgetUnloaded");
        },

        _bindInternalWidget : function dcpDocument_bindInternalWidget(internalController)
        {
            this.element.data("internalWidget", internalController);
            //Rebind event
            _.each(this.options.eventList, function dcpDocument_bindEvent(currentEvent) {
                internalController.addEvent(currentEvent);
            });
            //Rebind constraint
            _.each(this.options.constraintList, function dcpDocument_bindEvent(currentConstaint)
            {
                internalController.addConstraint(currentConstaint);
            });
            this.element.data("internalWidgetInitialised", true);
            this._trigger("loaded");
        },

        _bindEvents: function dcpDocument_bindEvents()
        {
            if (!this.options.withoutResize) {
                $(window).on("resize" + this.eventNamespace, _.debounce(_.bind(this._resize, this), 50));
                this._resize();
            }
        },

        _resize: function dcpDocument_resize()
        {
            var event = this._trigger("resize"),
                $documentWrapper = this.element.find(".dcpDocumentWrapper"),
                element = this.element;
            //the computation can be done by an external function and default prevented
            if (event) {
                //compute two times height (one for disapear horizontal scrollbar, two to get the actual size)
                $documentWrapper.height(element.innerHeight() - 3);
                $documentWrapper.width(element.innerWidth());
                //defer height computation to let the time to scrollbar disapear
                _.defer(function dcpDocument_computeHeight()
                {
                    $documentWrapper.height(element.innerHeight() - 3);
                });
            }
        },

        _destroy: function dcpDocument_destroy()
        {
            $(window).off(this.eventNamespace);
            this.element.empty();
            this._unbindInternalWidget();
            this._trigger("destroy");
            this._super();
        },

        options: function dcpDocument_options()
        {
            throw new Error("You cannot modify the options, you need to suppress the widget");
        },

        fetchDocument : function dcpDocument_fetchDocument(options) {
            var internalWidget;
            this.options = _.defaults(options, this.options);
            if (this.element.data("internalWidgetInitialised")) {
                internalWidget = this.element.data("internalWidget");
                internalWidget.fetchDocument.apply(internalWidget, options);
            } else {
                this._render();
            }
        },

        addEvent : function dcpDocument_addEvent(eventType, options, callback) {
            var eventContent, currentWidget = this;
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
                    "name": _.uniqueId("event_" + eventType),
                    "eventType": eventType,
                    "eventCallback": callback,
                    "externalEvent": true,
                    "once": false
                });
            }
            if (eventContent.once === true) {
                eventContent.eventCallback = _.wrap(eventContent.eventCallback, function documentController_onceWrapper(callback)
                {
                    try {
                        callback.apply(this, _.rest(arguments));
                    } catch (e) {
                        console.error(e);
                    }
                    currentWidget.removeEvent(eventContent.name);
                });
            }
            //Remove once property because already wrapped
            eventContent.once = false;
            this.options.eventList[eventContent.name] = eventContent;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").addEvent(eventContent);
            }
            return eventContent.name;
        },

        removeEvent : function dcpDocument_removeEvent(eventName) {
            var removed = [],
                testRegExp = new RegExp("\\" + eventName + "$"), newList, eventList;
            newList = _.filter(this.options.eventList, function documentController_removeCurrentEvent(currentEvent)
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
            this.options.eventList = eventList;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").removeEvent(eventName, true);
            }
            return removed;
        },

        addConstraint: function documentControlleraddConstraint(options, callback)
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
                    "documentCheck": function documentController_defaultDocumentCheck()
                    {
                        return true;
                    },
                    "attributeCheck": function documentController_defaultAttributeCheck()
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
                parameters.eventCallback = _.wrap(parameters.constraintCheck, function documentController_onceWrapper(callback)
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

        removeConstraint : function removeConstraint(constraintName) {
            var removed = [], newConstraintList, constraintList,
                testRegExp = new RegExp("\\" + constraintName + "$");
            newConstraintList = _.filter(this.options.constraintList, function documentController_removeConstraint(currentConstrait)
            {
                if (currentConstrait.name === constraintName || testRegExp.test(currentConstrait.name)) {
                    removed.push(currentConstrait);
                    return false;
                }
                return true;
            });
            constraintList = {};
            _.each(newConstraintList, function documentController_reinitConstraint(currentConstraint)
            {
                constraintList[currentConstraint.name] = currentConstraint;
            });
            this.options.constraintList = constraintList;
            if (this.element.data("internalWidgetInitialised")) {
                this.element.data("internalWidget").removeConstraint(constraintName, true);
            }
            return removed;
        }

    });

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
                    throw new ErrorNotReady();
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
