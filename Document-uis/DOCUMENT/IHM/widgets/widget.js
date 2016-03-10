/*global define*/

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(["jquery"], factory);
    } else {
        factory(window.jQuery);
    }
}(window, function documentCatalog(jQuery)
{
    "use strict";

        var ErrorNoSuchMethod = function widget_ErrorNoSuchMethod(message)
        {
            this.name = 'noSuchMethodError';
            this.message = message || 'No such method for current widget instance';
        };

        ErrorNoSuchMethod.prototype = Object.create(Error.prototype);
        ErrorNoSuchMethod.prototype.constructor = ErrorNoSuchMethod;

        (function widget_init($, undefined) {

            var widgetUuid = 0,
                slice = Array.prototype.slice,
                _cleanData = $.cleanData;
            $.cleanData = function widget_cleanData(elems) {
                var events, elem=null, i;
                for (i = 0, elem; (elem = elems[i]) != null; i++) { // jshint ignore:line
                    try {
                        // Only trigger remove when necessary to save time
                        events = $._data( elem, "events" );
                        if ( events && events.remove ) {
                            $( elem ).triggerHandler( "remove" );
                        }
                    } catch (e) {
                    }
                }
                _cleanData(elems);
            };

            $.widget = function widget_initWidget(name, Base, prototype) {
                var fullName, existingConstructor, Constructor, basePrototype,
                // proxiedPrototype allows the provided prototype to remain unmodified
                // so that it can be used as a mixin for multiple widgets (#8876)
                    proxiedPrototype = {},
                    namespace = name.split(".")[ 0 ];

                name = name.split(".")[ 1 ];
                fullName = namespace + "-" + name;

                if (!prototype) {
                    prototype = Base;
                    Base = $.Widget;
                }

                if ( $.isArray( prototype ) ) {
                    prototype = $.extend.apply( null, [ {} ].concat( prototype ) );
                }

                // create selector for plugin
                $.expr[ ":" ][ fullName.toLowerCase() ] = function widget_createSelector(elem) {
                    return Boolean($(elem).data(fullName));
                };

                $[ namespace ] = $[ namespace ] || {};
                existingConstructor = $[ namespace ][ name ];
                Constructor = $[ namespace ][ name ] = function widget_Constructor(options, element) {
                    // allow instantiation without "new" keyword
                    if (!this._createWidget) {
                        return new Constructor(options, element);
                    }

                    // allow instantiation without initializing for simple inheritance
                    // must use "new" keyword (the code above always passes args)
                    if (arguments.length) {
                        this._createWidget(options, element);
                    }
                };
                // extend with the existing constructor to carry over any static properties
                $.extend(Constructor, existingConstructor, {
                    version :            prototype.version,
                    // copy the object used to create the prototype in case we need to
                    // redefine the widget later
                    _proto :             $.extend({}, prototype),
                    // track widgets that inherit from this widget in case this widget is
                    // redefined after a widget inherits from it
                    _childConstructors : []
                });

                basePrototype = new Base();
                // we need to make the options hash a property directly on the new instance
                // otherwise we'll modify the options hash on the prototype that we're
                // inheriting from
                basePrototype.options = $.widget.extend({}, basePrototype.options);
                $.each(prototype, function widget_proxiedElements(prop, value) {
                    if (!$.isFunction(value)) {
                        proxiedPrototype[ prop ] = value;
                        return;
                    }
                    proxiedPrototype[ prop ] = (function widget_proxiedProperties() {
                        var _super = function widget_super() {
                                return Base.prototype[ prop ].apply(this, arguments);
                            },
                            _superApply = function widget_superApply(args) {
                                return Base.prototype[ prop ].apply(this, args);
                            };
                        return function widget_proxied() {
                            var __super = this._super,
                                __superApply = this._superApply,
                                returnValue;

                            this._super = _super;
                            this._superApply = _superApply;

                            returnValue = value.apply(this, arguments);

                            this._super = __super;
                            this._superApply = __superApply;

                            return returnValue;
                        };
                    })();
                });
                Constructor.prototype = $.widget.extend(basePrototype, {
                }, proxiedPrototype, {
                    constructor :    Constructor,
                    namespace :      namespace,
                    widgetName :     name,
                    widgetFullName : fullName
                });

                // If this widget is being redefined then we need to find all widgets that
                // are inheriting from it and redefine all of them so that they inherit from
                // the new version of this widget. We're essentially trying to replace one
                // level in the prototype chain.
                if (existingConstructor) {
                    $.each(existingConstructor._childConstructors, function widget_existingConstructor(i, child) {
                        var childPrototype = child.prototype;

                        // redefine the child widget using the same prototype that was
                        // originally used, but inherit from the new version of the base
                        $.widget(childPrototype.namespace + "." + childPrototype.widgetName, Constructor, child._proto);
                    });
                    // remove the list of existing child constructors from the old constructor
                    // so the old child constructors can be garbage collected
                    delete existingConstructor._childConstructors;
                } else {
                    Base._childConstructors.push(Constructor);
                }

                $.widget.bridge(name, Constructor);

                return Constructor;
            };

            $.widget.extend = function widget_extend(target) {
                var input = slice.call(arguments, 1),
                    inputIndex = 0,
                    inputLength = input.length,
                    key,
                    value;
                for (; inputIndex < inputLength; inputIndex++) {
                    for (key in input[ inputIndex ]) {  // jshint ignore:line
                        //noinspection JSUnfilteredForInLoop
                        value = input[ inputIndex ][ key ];
                        if (input[ inputIndex ].hasOwnProperty(key) && value !== undefined) {
                            // Clone objects
                            if ($.isPlainObject(value)) {
                                target[ key ] = $.isPlainObject(target[ key ]) ?
                                    $.widget.extend({}, target[ key ], value) :
                                    // Don't extend strings, arrays, etc. with objects
                                    $.widget.extend({}, value);
                                // Copy everything else by reference
                            } else {
                                target[ key ] = value;
                            }
                        }
                    }
                }
                return target;
            };

            $.widget.bridge = function widget_bridge(name, Object) {
                var fullName = Object.prototype.widgetFullName || name;
                $.fn[ name ] = function widget_callElement(options) {
                    var isMethodCall = typeof options === "string",
                        args = slice.call(arguments, 1),
                        returnValue = this;

                    // allow multiple hashes to be passed on init
                    options = !isMethodCall && args.length ?
                        $.widget.extend.apply(null, [ options ].concat(args)) :
                        options;

                    if (isMethodCall) {
                        this.each(function widget_eachMethodCall() {
                            var methodValue,
                                instance = $(this).data( fullName);
                            if ( options === "instance" ) {
                                returnValue = instance;
                                return false;
                            }
                            if (!instance) {
                                return $.error("cannot call methods on " + name + " prior to initialization; " +
                                    "attempted to call method '" + options + "'");
                            }
                            if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                                throw new ErrorNoSuchMethod("no such method '" + options + "' for " + name + " widget instance");
                            }
                            methodValue = instance[ options ].apply(instance, args);
                            if (methodValue !== instance && methodValue !== undefined) {
                                returnValue = methodValue && methodValue.jquery ?
                                    returnValue.pushStack(methodValue.get()) :
                                    methodValue;
                                return false;
                            }
                        });
                    } else {

                        // Allow multiple hashes to be passed on init
                        if ( args.length ) {
                            options = $.widget.extend.apply( null, [ options ].concat( args ) );
                        }

                        this.each(function widget_eachDataCall() {
                            var instance = $(this).data(fullName);
                            if (instance) {
                                instance.option(options || {})._init();
                            } else {
                                $(this).data(fullName, new Object(options, this));
                            }
                        });
                    }

                    return returnValue;
                };
            };

            $.Widget = function widget_Widget(/* options, element */) {
            };
            $.Widget._childConstructors = [];

            $.Widget.prototype = {
                widgetName :          "widget",
                defaultElement :      "<div>",
                options :             {
                    classes: {},
                    disabled :    false,
                    eventPrefix : null,
                    // callbacks
                    create :      null
                },
                _createWidget :       function widget_createWidget(options, element) {
                    element = $(element || this.defaultElement || this)[ 0 ];
                    this.element = $(element);
                    this.uuid = widgetUuid++;
                    this.eventNamespace = "." + this.widgetName + this.uuid;
                    this.options = $.widget.extend({},
                        this.options,
                        this._getCreateOptions(),
                        options);

                    this.bindings = $();
                    this.classesElementLookup = {};
                    if (this.options.eventPrefix === null) {
                        this.options.eventPrefix = this.widgetName;
                    }

                    if (element !== this) {
                        $(element).data(this.widgetFullName, this);
                        this._on(true, this.element, {
                            remove : function widget_remove(event) {
                                if (event.target === element) {
                                    this.destroy();
                                }
                            }
                        });
                        this.document = $(element.style ?
                            // element within the document
                            element.ownerDocument :
                            // element is window or document
                        element.document || element);
                        this.window = $(this.document[0].defaultView || this.document[0].parentWindow);
                    }
                    this.options = $.widget.extend( {},
                        this.options,
                        this._getCreateOptions(),
                        options );

                    this._create();

                    if ( this.options.disabled ) {
                        this._setOptionDisabled( this.options.disabled );
                    }

                    this._trigger("create", null, this._getCreateEventData());
                    this._init();
                },
                _getCreateOptions: function widget_getCreateOptions() {
                    return {};
                },
                _getCreateEventData : $.noop,
                _create :             $.noop,
                _init :               $.noop,

                destroy :  function widget_destroy() {
                    var that = this;

                    this._destroy();
                    $.each( this.classesElementLookup, function widget_destroyClass( key, value ) {
                        that._removeClass( value, key );
                    } );

                    // We can probably remove the unbind calls in 2.0
                    // all event bindings should go through this._on()
                    this.element
                        .off( this.eventNamespace )
                        .removeData( this.widgetFullName );
                    this.widget()
                        .off( this.eventNamespace )
                        .removeAttr( "aria-disabled" );

                    // Clean up events and states
                    this.bindings.off( this.eventNamespace );
                },
                _destroy : $.noop,

                widget : function widget_widget() {
                    return this.element;
                },

                option :      function widget_option(key, value) {
                    var options = key,
                        parts,
                        curOption,
                        i;

                    if (arguments.length === 0) {
                        // don't return a reference to the internal hash
                        return $.widget.extend({}, this.options);
                    }

                    if (typeof key === "string") {
                        // handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
                        options = {};
                        parts = key.split(".");
                        key = parts.shift();
                        if (parts.length) {
                            curOption = options[ key ] = $.widget.extend({}, this.options[ key ]);
                            for (i = 0; i < parts.length - 1; i++) {
                                curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
                                curOption = curOption[ parts[ i ] ];
                            }
                            key = parts.pop();
                            if (arguments.length === 1) {
                                return curOption[ key ] === undefined ? null : curOption[ key ];
                            }
                            curOption[ key ] = value;
                        } else {
                            if (arguments.length === 1) {
                                return this.options[ key ] === undefined ? null : this.options[ key ];
                            }
                            options[ key ] = value;
                        }
                    }

                    this._setOptions(options);

                    return this;
                },
                _setOptions : function widget__setOptions(options) {
                    var key;

                    for (key in options) { // jshint ignore:line
                        //noinspection JSUnfilteredForInLoop
                        this._setOption(key, options[ key ]);
                    }

                    return this;
                },
                _setOption :  function widget__setOption(key, value) {
                    this.options[ key ] = value;

                    return this;
                },

                _setOptionClasses: function widget__setOptionClasses( value ) {
                    var classKey, elements, currentElements;

                    for ( classKey in value ) { // jshint ignore:line
                        //noinspection JSUnfilteredForInLoop
                        currentElements = this.classesElementLookup[ classKey ];
                        //noinspection JSUnfilteredForInLoop
                        if ( value[ classKey ] === this.options.classes[ classKey ] ||
                            !currentElements ||
                            !currentElements.length ) {
                            continue;
                        }

                        // We are doing this to create a new jQuery object because the _removeClass() call
                        // on the next line is going to destroy the reference to the current elements being
                        // tracked. We need to save a copy of this collection so that we can add the new classes
                        // below.
                        elements = $( currentElements.get() );
                        //noinspection JSUnfilteredForInLoop
                        this._removeClass( currentElements, classKey );

                        // We don't use _addClass() here, because that uses this.options.classes
                        // for generating the string of classes. We want to use the value passed in from
                        // _setOption(), this is the new value of the classes option which was passed to
                        // _setOption(). We pass this value directly to _classes().
                        //noinspection JSUnfilteredForInLoop
                        elements.addClass( this._classes( {
                            element: elements,
                            keys: classKey,
                            classes: value,
                            add: true
                        } ) );
                    }
                },

                _classes: function widget__classes( options ) {
                    var full = [];
                    var that = this, processClassString;

                    options = $.extend( {
                        element: this.element,
                        classes: this.options.classes || {}
                    }, options );

                    processClassString = function widget_processClassString( classes, checkOption ) {
                        var current, i;
                        for ( i = 0; i < classes.length; i++ ) {
                            current = that.classesElementLookup[ classes[ i ] ] || $();
                            if ( options.add ) {
                                current = $( $.unique( current.get().concat( options.element.get() ) ) );
                            } else {
                                current = $( current.not( options.element ).get() );
                            }
                            that.classesElementLookup[ classes[ i ] ] = current;
                            full.push( classes[ i ] );
                            if ( checkOption && options.classes[ classes[ i ] ] ) {
                                full.push( options.classes[ classes[ i ] ] );
                            }
                        }
                    };

                    if ( options.keys ) {
                        processClassString( options.keys.match( /\S+/g ) || [], true );
                    }
                    if ( options.extra ) {
                        processClassString( options.extra.match( /\S+/g ) || [] );
                    }

                    return full.join( " " );
                },

                _removeClass: function widget__removeClass( element, keys, extra ) {
                    return this._toggleClass( element, keys, extra, false );
                },

                _addClass: function widget__addClass( element, keys, extra ) {
                    return this._toggleClass( element, keys, extra, true );
                },

                _toggleClass: function widget__toggleClass( element, keys, extra, add ) {
                    add = ( typeof add === "boolean" ) ? add : extra;
                    var shift = ( typeof element === "string" || element === null ),
                        options = {
                            extra: shift ? keys : extra,
                            keys: shift ? element : keys,
                            element: shift ? this.element : element,
                            add: add
                        };
                    options.element.toggleClass( this._classes( options ), add );
                    return this;
                },

                _on : function widget__on(suppressDisabledCheck, element, handlers) {
                    var delegateElement,
                        instance = this;

                    // no suppressDisabledCheck flag, shuffle arguments
                    if (typeof suppressDisabledCheck !== "boolean") {
                        handlers = element;
                        element = suppressDisabledCheck;
                        //suppressDisabledCheck = false;
                    }

                    // no element argument, shuffle and use this.element
                    if (!handlers) {
                        handlers = element;
                        element = this.element;
                        delegateElement = this.widget();
                    } else {
                        // accept selectors, DOM elements
                        element = delegateElement = $(element);
                        this.bindings = this.bindings.add(element);
                    }

                    $.each(handlers, function widget_iterateHandler(event, handler) {
                        var handlerProxy = function handlerProxy() {
                            // allow widgets to customize the disabled handling
                            // - disabled as an array instead of boolean
                            // - disabled class as method for disabling individual parts
                            return ( typeof handler === "string" ? instance[ handler ] : handler )
                                .apply(instance, arguments);
                        };

                        // copy the guid so direct unbinding works
                        if (typeof handler !== "string") {
                            handlerProxy.guid = handler.guid =
                                handler.guid || handlerProxy.guid || $.guid++;
                        }

                        var match = event.match(/^(\w+)\s*(.*)$/),
                            eventName = match[1] + instance.eventNamespace,
                            selector = match[2];
                        if (selector) {
                            delegateElement.delegate(selector, eventName, handlerProxy);
                        } else {
                            element.bind(eventName, handlerProxy);
                        }
                    });
                },

                _off : function widget__off(element, eventName) {
                    eventName = (eventName || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace;
                    element.unbind(eventName).undelegate(eventName);
                },

                _delay : function widget__delay(handler, delay) {
                    var handlerProxy = function handlerProxy() {
                        return ( typeof handler === "string" ? instance[ handler ] : handler )
                            .apply(instance, arguments);
                    };

                    var instance = this;
                    return setTimeout(handlerProxy, delay || 0);
                },

                _trigger : function widget__trigger(type, event, data) {
                    var prop, orig,
                        callback = this.options[ type ];

                    data = data || {};
                    event = $.Event(event);
                    event.type = this.options.eventPrefix ? this.options.eventPrefix + type : type;
                    event.type = event.type.toLocaleLowerCase();
                    // the original event may come from any element
                    // so we need to reset the target on the new event
                    event.target = this.element[ 0 ];

                    // copy original event properties over to the new event
                    orig = event.originalEvent;
                    if (orig) {
                        for (prop in orig) {// jshint ignore:line
                            if (!( prop in event )) {
                                //noinspection JSUnfilteredForInLoop
                                event[ prop ] = orig[ prop ];
                            }
                        }
                    }

                    this.element.trigger(event, data);
                    return !( $.isFunction(callback) &&
                    callback.apply(this.element[0], [ event ].concat(data)) === false ||
                    event.isDefaultPrevented() );
                }
            };

        })(jQuery);
}));