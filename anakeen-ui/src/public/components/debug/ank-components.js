/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "components/debug/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 52);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var bind = __webpack_require__(13);
var isBuffer = __webpack_require__(47);

/*global toString:true*/

// utils is a library of generic helper functions non-specific to axios

var toString = Object.prototype.toString;

/**
 * Determine if a value is an Array
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an Array, otherwise false
 */
function isArray(val) {
  return toString.call(val) === '[object Array]';
}

/**
 * Determine if a value is an ArrayBuffer
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an ArrayBuffer, otherwise false
 */
function isArrayBuffer(val) {
  return toString.call(val) === '[object ArrayBuffer]';
}

/**
 * Determine if a value is a FormData
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an FormData, otherwise false
 */
function isFormData(val) {
  return typeof FormData !== 'undefined' && val instanceof FormData;
}

/**
 * Determine if a value is a view on an ArrayBuffer
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a view on an ArrayBuffer, otherwise false
 */
function isArrayBufferView(val) {
  var result;
  if (typeof ArrayBuffer !== 'undefined' && ArrayBuffer.isView) {
    result = ArrayBuffer.isView(val);
  } else {
    result = val && val.buffer && val.buffer instanceof ArrayBuffer;
  }
  return result;
}

/**
 * Determine if a value is a String
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a String, otherwise false
 */
function isString(val) {
  return typeof val === 'string';
}

/**
 * Determine if a value is a Number
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Number, otherwise false
 */
function isNumber(val) {
  return typeof val === 'number';
}

/**
 * Determine if a value is undefined
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if the value is undefined, otherwise false
 */
function isUndefined(val) {
  return typeof val === 'undefined';
}

/**
 * Determine if a value is an Object
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is an Object, otherwise false
 */
function isObject(val) {
  return val !== null && (typeof val === 'undefined' ? 'undefined' : _typeof(val)) === 'object';
}

/**
 * Determine if a value is a Date
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Date, otherwise false
 */
function isDate(val) {
  return toString.call(val) === '[object Date]';
}

/**
 * Determine if a value is a File
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a File, otherwise false
 */
function isFile(val) {
  return toString.call(val) === '[object File]';
}

/**
 * Determine if a value is a Blob
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Blob, otherwise false
 */
function isBlob(val) {
  return toString.call(val) === '[object Blob]';
}

/**
 * Determine if a value is a Function
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Function, otherwise false
 */
function isFunction(val) {
  return toString.call(val) === '[object Function]';
}

/**
 * Determine if a value is a Stream
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a Stream, otherwise false
 */
function isStream(val) {
  return isObject(val) && isFunction(val.pipe);
}

/**
 * Determine if a value is a URLSearchParams object
 *
 * @param {Object} val The value to test
 * @returns {boolean} True if value is a URLSearchParams object, otherwise false
 */
function isURLSearchParams(val) {
  return typeof URLSearchParams !== 'undefined' && val instanceof URLSearchParams;
}

/**
 * Trim excess whitespace off the beginning and end of a string
 *
 * @param {String} str The String to trim
 * @returns {String} The String freed of excess whitespace
 */
function trim(str) {
  return str.replace(/^\s*/, '').replace(/\s*$/, '');
}

/**
 * Determine if we're running in a standard browser environment
 *
 * This allows axios to run in a web worker, and react-native.
 * Both environments support XMLHttpRequest, but not fully standard globals.
 *
 * web workers:
 *  typeof window -> undefined
 *  typeof document -> undefined
 *
 * react-native:
 *  navigator.product -> 'ReactNative'
 */
function isStandardBrowserEnv() {
  if (typeof navigator !== 'undefined' && navigator.product === 'ReactNative') {
    return false;
  }
  return typeof window !== 'undefined' && typeof document !== 'undefined';
}

/**
 * Iterate over an Array or an Object invoking a function for each item.
 *
 * If `obj` is an Array callback will be called passing
 * the value, index, and complete array for each item.
 *
 * If 'obj' is an Object callback will be called passing
 * the value, key, and complete object for each property.
 *
 * @param {Object|Array} obj The object to iterate
 * @param {Function} fn The callback to invoke for each item
 */
function forEach(obj, fn) {
  // Don't bother if no value provided
  if (obj === null || typeof obj === 'undefined') {
    return;
  }

  // Force an array if not already something iterable
  if ((typeof obj === 'undefined' ? 'undefined' : _typeof(obj)) !== 'object' && !isArray(obj)) {
    /*eslint no-param-reassign:0*/
    obj = [obj];
  }

  if (isArray(obj)) {
    // Iterate over array values
    for (var i = 0, l = obj.length; i < l; i++) {
      fn.call(null, obj[i], i, obj);
    }
  } else {
    // Iterate over object keys
    for (var key in obj) {
      if (Object.prototype.hasOwnProperty.call(obj, key)) {
        fn.call(null, obj[key], key, obj);
      }
    }
  }
}

/**
 * Accepts varargs expecting each argument to be an object, then
 * immutably merges the properties of each object and returns result.
 *
 * When multiple objects contain the same key the later object in
 * the arguments list will take precedence.
 *
 * Example:
 *
 * ```js
 * var result = merge({foo: 123}, {foo: 456});
 * console.log(result.foo); // outputs 456
 * ```
 *
 * @param {Object} obj1 Object to merge
 * @returns {Object} Result of all merge properties
 */
function merge() /* obj1, obj2, obj3, ... */{
  var result = {};
  function assignValue(val, key) {
    if (_typeof(result[key]) === 'object' && (typeof val === 'undefined' ? 'undefined' : _typeof(val)) === 'object') {
      result[key] = merge(result[key], val);
    } else {
      result[key] = val;
    }
  }

  for (var i = 0, l = arguments.length; i < l; i++) {
    forEach(arguments[i], assignValue);
  }
  return result;
}

/**
 * Extends object a by mutably adding to it the properties of object b.
 *
 * @param {Object} a The object to be extended
 * @param {Object} b The object to copy properties from
 * @param {Object} thisArg The object to bind function to
 * @return {Object} The resulting value of object a
 */
function extend(a, b, thisArg) {
  forEach(b, function assignValue(val, key) {
    if (thisArg && typeof val === 'function') {
      a[key] = bind(val, thisArg);
    } else {
      a[key] = val;
    }
  });
  return a;
}

module.exports = {
  isArray: isArray,
  isArrayBuffer: isArrayBuffer,
  isBuffer: isBuffer,
  isFormData: isFormData,
  isArrayBufferView: isArrayBufferView,
  isString: isString,
  isNumber: isNumber,
  isObject: isObject,
  isUndefined: isUndefined,
  isDate: isDate,
  isFile: isFile,
  isBlob: isBlob,
  isFunction: isFunction,
  isStream: isStream,
  isURLSearchParams: isURLSearchParams,
  isStandardBrowserEnv: isStandardBrowserEnv,
  forEach: forEach,
  merge: merge,
  extend: extend,
  trim: trim
};

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function (useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if (item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function (modules, mediaQuery) {
		if (typeof modules === "string") modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for (var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if (typeof id === "number") alreadyImportedModules[id] = true;
		}
		for (i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if (typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if (mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if (mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */';
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
  Modified by Evan You @yyx990803
*/

var hasDocument = typeof document !== 'undefined'

if (typeof DEBUG !== 'undefined' && DEBUG) {
  if (!hasDocument) {
    throw new Error(
    'vue-style-loader cannot be used in a non-browser environment. ' +
    "Use { target: 'node' } in your Webpack config to indicate a server-rendering environment."
  ) }
}

var listToStyles = __webpack_require__(50)

/*
type StyleObject = {
  id: number;
  parts: Array<StyleObjectPart>
}

type StyleObjectPart = {
  css: string;
  media: string;
  sourceMap: ?string
}
*/

var stylesInDom = {/*
  [id: number]: {
    id: number,
    refs: number,
    parts: Array<(obj?: StyleObjectPart) => void>
  }
*/}

var head = hasDocument && (document.head || document.getElementsByTagName('head')[0])
var singletonElement = null
var singletonCounter = 0
var isProduction = false
var noop = function () {}

// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
// tags it will allow on a page
var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\b/.test(navigator.userAgent.toLowerCase())

module.exports = function (parentId, list, _isProduction) {
  isProduction = _isProduction

  var styles = listToStyles(parentId, list)
  addStylesToDom(styles)

  return function update (newList) {
    var mayRemove = []
    for (var i = 0; i < styles.length; i++) {
      var item = styles[i]
      var domStyle = stylesInDom[item.id]
      domStyle.refs--
      mayRemove.push(domStyle)
    }
    if (newList) {
      styles = listToStyles(parentId, newList)
      addStylesToDom(styles)
    } else {
      styles = []
    }
    for (var i = 0; i < mayRemove.length; i++) {
      var domStyle = mayRemove[i]
      if (domStyle.refs === 0) {
        for (var j = 0; j < domStyle.parts.length; j++) {
          domStyle.parts[j]()
        }
        delete stylesInDom[domStyle.id]
      }
    }
  }
}

function addStylesToDom (styles /* Array<StyleObject> */) {
  for (var i = 0; i < styles.length; i++) {
    var item = styles[i]
    var domStyle = stylesInDom[item.id]
    if (domStyle) {
      domStyle.refs++
      for (var j = 0; j < domStyle.parts.length; j++) {
        domStyle.parts[j](item.parts[j])
      }
      for (; j < item.parts.length; j++) {
        domStyle.parts.push(addStyle(item.parts[j]))
      }
      if (domStyle.parts.length > item.parts.length) {
        domStyle.parts.length = item.parts.length
      }
    } else {
      var parts = []
      for (var j = 0; j < item.parts.length; j++) {
        parts.push(addStyle(item.parts[j]))
      }
      stylesInDom[item.id] = { id: item.id, refs: 1, parts: parts }
    }
  }
}

function createStyleElement () {
  var styleElement = document.createElement('style')
  styleElement.type = 'text/css'
  head.appendChild(styleElement)
  return styleElement
}

function addStyle (obj /* StyleObjectPart */) {
  var update, remove
  var styleElement = document.querySelector('style[data-vue-ssr-id~="' + obj.id + '"]')

  if (styleElement) {
    if (isProduction) {
      // has SSR styles and in production mode.
      // simply do nothing.
      return noop
    } else {
      // has SSR styles but in dev mode.
      // for some reason Chrome can't handle source map in server-rendered
      // style tags - source maps in <style> only works if the style tag is
      // created and inserted dynamically. So we remove the server rendered
      // styles and inject new ones.
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  if (isOldIE) {
    // use singleton mode for IE9.
    var styleIndex = singletonCounter++
    styleElement = singletonElement || (singletonElement = createStyleElement())
    update = applyToSingletonTag.bind(null, styleElement, styleIndex, false)
    remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true)
  } else {
    // use multi-style-tag mode in all other cases
    styleElement = createStyleElement()
    update = applyToTag.bind(null, styleElement)
    remove = function () {
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  update(obj)

  return function updateStyle (newObj /* StyleObjectPart */) {
    if (newObj) {
      if (newObj.css === obj.css &&
          newObj.media === obj.media &&
          newObj.sourceMap === obj.sourceMap) {
        return
      }
      update(obj = newObj)
    } else {
      remove()
    }
  }
}

var replaceText = (function () {
  var textStore = []

  return function (index, replacement) {
    textStore[index] = replacement
    return textStore.filter(Boolean).join('\n')
  }
})()

function applyToSingletonTag (styleElement, index, remove, obj) {
  var css = remove ? '' : obj.css

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = replaceText(index, css)
  } else {
    var cssNode = document.createTextNode(css)
    var childNodes = styleElement.childNodes
    if (childNodes[index]) styleElement.removeChild(childNodes[index])
    if (childNodes.length) {
      styleElement.insertBefore(cssNode, childNodes[index])
    } else {
      styleElement.appendChild(cssNode)
    }
  }
}

function applyToTag (styleElement, obj) {
  var css = obj.css
  var media = obj.media
  var sourceMap = obj.sourceMap

  if (media) {
    styleElement.setAttribute('media', media)
  }

  if (sourceMap) {
    // https://developer.chrome.com/devtools/docs/javascript-debugging
    // this makes source maps inside style tags work properly in Chrome
    css += '\n/*# sourceURL=' + sourceMap.sources[0] + ' */'
    // http://stackoverflow.com/a/26603875
    css += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + ' */'
  }

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild)
    }
    styleElement.appendChild(document.createTextNode(css))
  }
}


/***/ }),
/* 3 */
/***/ (function(module, exports) {

// this module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  scopeId,
  cssModules
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  // inject cssModules
  if (cssModules) {
    var computed = Object.create(options.computed || null)
    Object.keys(cssModules).forEach(function (key) {
      var module = cssModules[key]
      computed[key] = function () { return module }
    })
    options.computed = computed
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout() {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
})();
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch (e) {
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch (e) {
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }
}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e) {
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e) {
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }
}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while (len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) {
    return [];
};

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () {
    return '/';
};
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function () {
    return 0;
};

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(process, global, setImmediate) {

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/*!
 * Vue.js v2.5.2
 * (c) 2014-2017 Evan You
 * Released under the MIT License.
 */
/*  */

// these helpers produces better vm code in JS engines due to their
// explicitness and function inlining
function isUndef(v) {
  return v === undefined || v === null;
}

function isDef(v) {
  return v !== undefined && v !== null;
}

function isTrue(v) {
  return v === true;
}

function isFalse(v) {
  return v === false;
}

/**
 * Check if value is primitive
 */
function isPrimitive(value) {
  return typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean';
}

/**
 * Quick object check - this is primarily used to tell
 * Objects from primitive values when we know the value
 * is a JSON-compliant type.
 */
function isObject(obj) {
  return obj !== null && (typeof obj === 'undefined' ? 'undefined' : _typeof(obj)) === 'object';
}

/**
 * Get the raw type string of a value e.g. [object Object]
 */
var _toString = Object.prototype.toString;

function toRawType(value) {
  return _toString.call(value).slice(8, -1);
}

/**
 * Strict object type check. Only returns true
 * for plain JavaScript objects.
 */
function isPlainObject(obj) {
  return _toString.call(obj) === '[object Object]';
}

function isRegExp(v) {
  return _toString.call(v) === '[object RegExp]';
}

/**
 * Check if val is a valid array index.
 */
function isValidArrayIndex(val) {
  var n = parseFloat(String(val));
  return n >= 0 && Math.floor(n) === n && isFinite(val);
}

/**
 * Convert a value to a string that is actually rendered.
 */
function toString(val) {
  return val == null ? '' : (typeof val === 'undefined' ? 'undefined' : _typeof(val)) === 'object' ? JSON.stringify(val, null, 2) : String(val);
}

/**
 * Convert a input value to a number for persistence.
 * If the conversion fails, return original string.
 */
function toNumber(val) {
  var n = parseFloat(val);
  return isNaN(n) ? val : n;
}

/**
 * Make a map and return a function for checking if a key
 * is in that map.
 */
function makeMap(str, expectsLowerCase) {
  var map = Object.create(null);
  var list = str.split(',');
  for (var i = 0; i < list.length; i++) {
    map[list[i]] = true;
  }
  return expectsLowerCase ? function (val) {
    return map[val.toLowerCase()];
  } : function (val) {
    return map[val];
  };
}

/**
 * Check if a tag is a built-in tag.
 */
var isBuiltInTag = makeMap('slot,component', true);

/**
 * Check if a attribute is a reserved attribute.
 */
var isReservedAttribute = makeMap('key,ref,slot,slot-scope,is');

/**
 * Remove an item from an array
 */
function remove(arr, item) {
  if (arr.length) {
    var index = arr.indexOf(item);
    if (index > -1) {
      return arr.splice(index, 1);
    }
  }
}

/**
 * Check whether the object has the property.
 */
var hasOwnProperty = Object.prototype.hasOwnProperty;
function hasOwn(obj, key) {
  return hasOwnProperty.call(obj, key);
}

/**
 * Create a cached version of a pure function.
 */
function cached(fn) {
  var cache = Object.create(null);
  return function cachedFn(str) {
    var hit = cache[str];
    return hit || (cache[str] = fn(str));
  };
}

/**
 * Camelize a hyphen-delimited string.
 */
var camelizeRE = /-(\w)/g;
var camelize = cached(function (str) {
  return str.replace(camelizeRE, function (_, c) {
    return c ? c.toUpperCase() : '';
  });
});

/**
 * Capitalize a string.
 */
var capitalize = cached(function (str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
});

/**
 * Hyphenate a camelCase string.
 */
var hyphenateRE = /\B([A-Z])/g;
var hyphenate = cached(function (str) {
  return str.replace(hyphenateRE, '-$1').toLowerCase();
});

/**
 * Simple bind, faster than native
 */
function bind(fn, ctx) {
  function boundFn(a) {
    var l = arguments.length;
    return l ? l > 1 ? fn.apply(ctx, arguments) : fn.call(ctx, a) : fn.call(ctx);
  }
  // record original fn length
  boundFn._length = fn.length;
  return boundFn;
}

/**
 * Convert an Array-like object to a real Array.
 */
function toArray(list, start) {
  start = start || 0;
  var i = list.length - start;
  var ret = new Array(i);
  while (i--) {
    ret[i] = list[i + start];
  }
  return ret;
}

/**
 * Mix properties into target object.
 */
function extend(to, _from) {
  for (var key in _from) {
    to[key] = _from[key];
  }
  return to;
}

/**
 * Merge an Array of Objects into a single Object.
 */
function toObject(arr) {
  var res = {};
  for (var i = 0; i < arr.length; i++) {
    if (arr[i]) {
      extend(res, arr[i]);
    }
  }
  return res;
}

/**
 * Perform no operation.
 * Stubbing args to make Flow happy without leaving useless transpiled code
 * with ...rest (https://flow.org/blog/2017/05/07/Strict-Function-Call-Arity/)
 */
function noop(a, b, c) {}

/**
 * Always return false.
 */
var no = function no(a, b, c) {
  return false;
};

/**
 * Return same value
 */
var identity = function identity(_) {
  return _;
};

/**
 * Generate a static keys string from compiler modules.
 */

/**
 * Check if two values are loosely equal - that is,
 * if they are plain objects, do they have the same shape?
 */
function looseEqual(a, b) {
  if (a === b) {
    return true;
  }
  var isObjectA = isObject(a);
  var isObjectB = isObject(b);
  if (isObjectA && isObjectB) {
    try {
      var isArrayA = Array.isArray(a);
      var isArrayB = Array.isArray(b);
      if (isArrayA && isArrayB) {
        return a.length === b.length && a.every(function (e, i) {
          return looseEqual(e, b[i]);
        });
      } else if (!isArrayA && !isArrayB) {
        var keysA = Object.keys(a);
        var keysB = Object.keys(b);
        return keysA.length === keysB.length && keysA.every(function (key) {
          return looseEqual(a[key], b[key]);
        });
      } else {
        /* istanbul ignore next */
        return false;
      }
    } catch (e) {
      /* istanbul ignore next */
      return false;
    }
  } else if (!isObjectA && !isObjectB) {
    return String(a) === String(b);
  } else {
    return false;
  }
}

function looseIndexOf(arr, val) {
  for (var i = 0; i < arr.length; i++) {
    if (looseEqual(arr[i], val)) {
      return i;
    }
  }
  return -1;
}

/**
 * Ensure a function is called only once.
 */
function once(fn) {
  var called = false;
  return function () {
    if (!called) {
      called = true;
      fn.apply(this, arguments);
    }
  };
}

var SSR_ATTR = 'data-server-rendered';

var ASSET_TYPES = ['component', 'directive', 'filter'];

var LIFECYCLE_HOOKS = ['beforeCreate', 'created', 'beforeMount', 'mounted', 'beforeUpdate', 'updated', 'beforeDestroy', 'destroyed', 'activated', 'deactivated', 'errorCaptured'];

/*  */

var config = {
  /**
   * Option merge strategies (used in core/util/options)
   */
  optionMergeStrategies: Object.create(null),

  /**
   * Whether to suppress warnings.
   */
  silent: false,

  /**
   * Show production mode tip message on boot?
   */
  productionTip: process.env.NODE_ENV !== 'production',

  /**
   * Whether to enable devtools
   */
  devtools: process.env.NODE_ENV !== 'production',

  /**
   * Whether to record perf
   */
  performance: false,

  /**
   * Error handler for watcher errors
   */
  errorHandler: null,

  /**
   * Warn handler for watcher warns
   */
  warnHandler: null,

  /**
   * Ignore certain custom elements
   */
  ignoredElements: [],

  /**
   * Custom user key aliases for v-on
   */
  keyCodes: Object.create(null),

  /**
   * Check if a tag is reserved so that it cannot be registered as a
   * component. This is platform-dependent and may be overwritten.
   */
  isReservedTag: no,

  /**
   * Check if an attribute is reserved so that it cannot be used as a component
   * prop. This is platform-dependent and may be overwritten.
   */
  isReservedAttr: no,

  /**
   * Check if a tag is an unknown element.
   * Platform-dependent.
   */
  isUnknownElement: no,

  /**
   * Get the namespace of an element
   */
  getTagNamespace: noop,

  /**
   * Parse the real tag name for the specific platform.
   */
  parsePlatformTagName: identity,

  /**
   * Check if an attribute must be bound using property, e.g. value
   * Platform-dependent.
   */
  mustUseProp: no,

  /**
   * Exposed for legacy reasons
   */
  _lifecycleHooks: LIFECYCLE_HOOKS
};

/*  */

var emptyObject = Object.freeze({});

/**
 * Check if a string starts with $ or _
 */
function isReserved(str) {
  var c = (str + '').charCodeAt(0);
  return c === 0x24 || c === 0x5F;
}

/**
 * Define a property.
 */
function def(obj, key, val, enumerable) {
  Object.defineProperty(obj, key, {
    value: val,
    enumerable: !!enumerable,
    writable: true,
    configurable: true
  });
}

/**
 * Parse simple path.
 */
var bailRE = /[^\w.$]/;
function parsePath(path) {
  if (bailRE.test(path)) {
    return;
  }
  var segments = path.split('.');
  return function (obj) {
    for (var i = 0; i < segments.length; i++) {
      if (!obj) {
        return;
      }
      obj = obj[segments[i]];
    }
    return obj;
  };
}

/*  */

// can we use __proto__?
var hasProto = '__proto__' in {};

// Browser environment sniffing
var inBrowser = typeof window !== 'undefined';
var UA = inBrowser && window.navigator.userAgent.toLowerCase();
var isIE = UA && /msie|trident/.test(UA);
var isIE9 = UA && UA.indexOf('msie 9.0') > 0;
var isEdge = UA && UA.indexOf('edge/') > 0;
var isAndroid = UA && UA.indexOf('android') > 0;
var isIOS = UA && /iphone|ipad|ipod|ios/.test(UA);
var isChrome = UA && /chrome\/\d+/.test(UA) && !isEdge;

// Firefox has a "watch" function on Object.prototype...
var nativeWatch = {}.watch;

var supportsPassive = false;
if (inBrowser) {
  try {
    var opts = {};
    Object.defineProperty(opts, 'passive', {
      get: function get() {
        /* istanbul ignore next */
        supportsPassive = true;
      }
    }); // https://github.com/facebook/flow/issues/285
    window.addEventListener('test-passive', null, opts);
  } catch (e) {}
}

// this needs to be lazy-evaled because vue may be required before
// vue-server-renderer can set VUE_ENV
var _isServer;
var isServerRendering = function isServerRendering() {
  if (_isServer === undefined) {
    /* istanbul ignore if */
    if (!inBrowser && typeof global !== 'undefined') {
      // detect presence of vue-server-renderer and avoid
      // Webpack shimming the process
      _isServer = global['process'].env.VUE_ENV === 'server';
    } else {
      _isServer = false;
    }
  }
  return _isServer;
};

// detect devtools
var devtools = inBrowser && window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

/* istanbul ignore next */
function isNative(Ctor) {
  return typeof Ctor === 'function' && /native code/.test(Ctor.toString());
}

var hasSymbol = typeof Symbol !== 'undefined' && isNative(Symbol) && typeof Reflect !== 'undefined' && isNative(Reflect.ownKeys);

var _Set;
/* istanbul ignore if */ // $flow-disable-line
if (typeof Set !== 'undefined' && isNative(Set)) {
  // use native Set when available.
  _Set = Set;
} else {
  // a non-standard Set polyfill that only works with primitive keys.
  _Set = function () {
    function Set() {
      this.set = Object.create(null);
    }
    Set.prototype.has = function has(key) {
      return this.set[key] === true;
    };
    Set.prototype.add = function add(key) {
      this.set[key] = true;
    };
    Set.prototype.clear = function clear() {
      this.set = Object.create(null);
    };

    return Set;
  }();
}

/*  */

var warn = noop;
var tip = noop;
var generateComponentTrace = noop; // work around flow check
var formatComponentName = noop;

if (process.env.NODE_ENV !== 'production') {
  var hasConsole = typeof console !== 'undefined';
  var classifyRE = /(?:^|[-_])(\w)/g;
  var classify = function classify(str) {
    return str.replace(classifyRE, function (c) {
      return c.toUpperCase();
    }).replace(/[-_]/g, '');
  };

  warn = function warn(msg, vm) {
    var trace = vm ? generateComponentTrace(vm) : '';

    if (config.warnHandler) {
      config.warnHandler.call(null, msg, vm, trace);
    } else if (hasConsole && !config.silent) {
      console.error("[Vue warn]: " + msg + trace);
    }
  };

  tip = function tip(msg, vm) {
    if (hasConsole && !config.silent) {
      console.warn("[Vue tip]: " + msg + (vm ? generateComponentTrace(vm) : ''));
    }
  };

  formatComponentName = function formatComponentName(vm, includeFile) {
    if (vm.$root === vm) {
      return '<Root>';
    }
    var options = typeof vm === 'function' && vm.cid != null ? vm.options : vm._isVue ? vm.$options || vm.constructor.options : vm || {};
    var name = options.name || options._componentTag;
    var file = options.__file;
    if (!name && file) {
      var match = file.match(/([^/\\]+)\.vue$/);
      name = match && match[1];
    }

    return (name ? "<" + classify(name) + ">" : "<Anonymous>") + (file && includeFile !== false ? " at " + file : '');
  };

  var repeat = function repeat(str, n) {
    var res = '';
    while (n) {
      if (n % 2 === 1) {
        res += str;
      }
      if (n > 1) {
        str += str;
      }
      n >>= 1;
    }
    return res;
  };

  generateComponentTrace = function generateComponentTrace(vm) {
    if (vm._isVue && vm.$parent) {
      var tree = [];
      var currentRecursiveSequence = 0;
      while (vm) {
        if (tree.length > 0) {
          var last = tree[tree.length - 1];
          if (last.constructor === vm.constructor) {
            currentRecursiveSequence++;
            vm = vm.$parent;
            continue;
          } else if (currentRecursiveSequence > 0) {
            tree[tree.length - 1] = [last, currentRecursiveSequence];
            currentRecursiveSequence = 0;
          }
        }
        tree.push(vm);
        vm = vm.$parent;
      }
      return '\n\nfound in\n\n' + tree.map(function (vm, i) {
        return "" + (i === 0 ? '---> ' : repeat(' ', 5 + i * 2)) + (Array.isArray(vm) ? formatComponentName(vm[0]) + "... (" + vm[1] + " recursive calls)" : formatComponentName(vm));
      }).join('\n');
    } else {
      return "\n\n(found in " + formatComponentName(vm) + ")";
    }
  };
}

/*  */

var uid$1 = 0;

/**
 * A dep is an observable that can have multiple
 * directives subscribing to it.
 */
var Dep = function Dep() {
  this.id = uid$1++;
  this.subs = [];
};

Dep.prototype.addSub = function addSub(sub) {
  this.subs.push(sub);
};

Dep.prototype.removeSub = function removeSub(sub) {
  remove(this.subs, sub);
};

Dep.prototype.depend = function depend() {
  if (Dep.target) {
    Dep.target.addDep(this);
  }
};

Dep.prototype.notify = function notify() {
  // stabilize the subscriber list first
  var subs = this.subs.slice();
  for (var i = 0, l = subs.length; i < l; i++) {
    subs[i].update();
  }
};

// the current target watcher being evaluated.
// this is globally unique because there could be only one
// watcher being evaluated at any time.
Dep.target = null;
var targetStack = [];

function pushTarget(_target) {
  if (Dep.target) {
    targetStack.push(Dep.target);
  }
  Dep.target = _target;
}

function popTarget() {
  Dep.target = targetStack.pop();
}

/*  */

var VNode = function VNode(tag, data, children, text, elm, context, componentOptions, asyncFactory) {
  this.tag = tag;
  this.data = data;
  this.children = children;
  this.text = text;
  this.elm = elm;
  this.ns = undefined;
  this.context = context;
  this.functionalContext = undefined;
  this.functionalOptions = undefined;
  this.functionalScopeId = undefined;
  this.key = data && data.key;
  this.componentOptions = componentOptions;
  this.componentInstance = undefined;
  this.parent = undefined;
  this.raw = false;
  this.isStatic = false;
  this.isRootInsert = true;
  this.isComment = false;
  this.isCloned = false;
  this.isOnce = false;
  this.asyncFactory = asyncFactory;
  this.asyncMeta = undefined;
  this.isAsyncPlaceholder = false;
};

var prototypeAccessors = { child: { configurable: true } };

// DEPRECATED: alias for componentInstance for backwards compat.
/* istanbul ignore next */
prototypeAccessors.child.get = function () {
  return this.componentInstance;
};

Object.defineProperties(VNode.prototype, prototypeAccessors);

var createEmptyVNode = function createEmptyVNode(text) {
  if (text === void 0) text = '';

  var node = new VNode();
  node.text = text;
  node.isComment = true;
  return node;
};

function createTextVNode(val) {
  return new VNode(undefined, undefined, undefined, String(val));
}

// optimized shallow clone
// used for static nodes and slot nodes because they may be reused across
// multiple renders, cloning them avoids errors when DOM manipulations rely
// on their elm reference.
function cloneVNode(vnode, deep) {
  var cloned = new VNode(vnode.tag, vnode.data, vnode.children, vnode.text, vnode.elm, vnode.context, vnode.componentOptions, vnode.asyncFactory);
  cloned.ns = vnode.ns;
  cloned.isStatic = vnode.isStatic;
  cloned.key = vnode.key;
  cloned.isComment = vnode.isComment;
  cloned.isCloned = true;
  if (deep && vnode.children) {
    cloned.children = cloneVNodes(vnode.children);
  }
  return cloned;
}

function cloneVNodes(vnodes, deep) {
  var len = vnodes.length;
  var res = new Array(len);
  for (var i = 0; i < len; i++) {
    res[i] = cloneVNode(vnodes[i], deep);
  }
  return res;
}

/*
 * not type checking this file because flow doesn't play well with
 * dynamically accessing methods on Array prototype
 */

var arrayProto = Array.prototype;
var arrayMethods = Object.create(arrayProto);['push', 'pop', 'shift', 'unshift', 'splice', 'sort', 'reverse'].forEach(function (method) {
  // cache original method
  var original = arrayProto[method];
  def(arrayMethods, method, function mutator() {
    var args = [],
        len = arguments.length;
    while (len--) {
      args[len] = arguments[len];
    }var result = original.apply(this, args);
    var ob = this.__ob__;
    var inserted;
    switch (method) {
      case 'push':
      case 'unshift':
        inserted = args;
        break;
      case 'splice':
        inserted = args.slice(2);
        break;
    }
    if (inserted) {
      ob.observeArray(inserted);
    }
    // notify change
    ob.dep.notify();
    return result;
  });
});

/*  */

var arrayKeys = Object.getOwnPropertyNames(arrayMethods);

/**
 * By default, when a reactive property is set, the new value is
 * also converted to become reactive. However when passing down props,
 * we don't want to force conversion because the value may be a nested value
 * under a frozen data structure. Converting it would defeat the optimization.
 */
var observerState = {
  shouldConvert: true
};

/**
 * Observer class that are attached to each observed
 * object. Once attached, the observer converts target
 * object's property keys into getter/setters that
 * collect dependencies and dispatches updates.
 */
var Observer = function Observer(value) {
  this.value = value;
  this.dep = new Dep();
  this.vmCount = 0;
  def(value, '__ob__', this);
  if (Array.isArray(value)) {
    var augment = hasProto ? protoAugment : copyAugment;
    augment(value, arrayMethods, arrayKeys);
    this.observeArray(value);
  } else {
    this.walk(value);
  }
};

/**
 * Walk through each property and convert them into
 * getter/setters. This method should only be called when
 * value type is Object.
 */
Observer.prototype.walk = function walk(obj) {
  var keys = Object.keys(obj);
  for (var i = 0; i < keys.length; i++) {
    defineReactive(obj, keys[i], obj[keys[i]]);
  }
};

/**
 * Observe a list of Array items.
 */
Observer.prototype.observeArray = function observeArray(items) {
  for (var i = 0, l = items.length; i < l; i++) {
    observe(items[i]);
  }
};

// helpers

/**
 * Augment an target Object or Array by intercepting
 * the prototype chain using __proto__
 */
function protoAugment(target, src, keys) {
  /* eslint-disable no-proto */
  target.__proto__ = src;
  /* eslint-enable no-proto */
}

/**
 * Augment an target Object or Array by defining
 * hidden properties.
 */
/* istanbul ignore next */
function copyAugment(target, src, keys) {
  for (var i = 0, l = keys.length; i < l; i++) {
    var key = keys[i];
    def(target, key, src[key]);
  }
}

/**
 * Attempt to create an observer instance for a value,
 * returns the new observer if successfully observed,
 * or the existing observer if the value already has one.
 */
function observe(value, asRootData) {
  if (!isObject(value) || value instanceof VNode) {
    return;
  }
  var ob;
  if (hasOwn(value, '__ob__') && value.__ob__ instanceof Observer) {
    ob = value.__ob__;
  } else if (observerState.shouldConvert && !isServerRendering() && (Array.isArray(value) || isPlainObject(value)) && Object.isExtensible(value) && !value._isVue) {
    ob = new Observer(value);
  }
  if (asRootData && ob) {
    ob.vmCount++;
  }
  return ob;
}

/**
 * Define a reactive property on an Object.
 */
function defineReactive(obj, key, val, customSetter, shallow) {
  var dep = new Dep();

  var property = Object.getOwnPropertyDescriptor(obj, key);
  if (property && property.configurable === false) {
    return;
  }

  // cater for pre-defined getter/setters
  var getter = property && property.get;
  var setter = property && property.set;

  var childOb = !shallow && observe(val);
  Object.defineProperty(obj, key, {
    enumerable: true,
    configurable: true,
    get: function reactiveGetter() {
      var value = getter ? getter.call(obj) : val;
      if (Dep.target) {
        dep.depend();
        if (childOb) {
          childOb.dep.depend();
          if (Array.isArray(value)) {
            dependArray(value);
          }
        }
      }
      return value;
    },
    set: function reactiveSetter(newVal) {
      var value = getter ? getter.call(obj) : val;
      /* eslint-disable no-self-compare */
      if (newVal === value || newVal !== newVal && value !== value) {
        return;
      }
      /* eslint-enable no-self-compare */
      if (process.env.NODE_ENV !== 'production' && customSetter) {
        customSetter();
      }
      if (setter) {
        setter.call(obj, newVal);
      } else {
        val = newVal;
      }
      childOb = !shallow && observe(newVal);
      dep.notify();
    }
  });
}

/**
 * Set a property on an object. Adds the new property and
 * triggers change notification if the property doesn't
 * already exist.
 */
function set(target, key, val) {
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.length = Math.max(target.length, key);
    target.splice(key, 1, val);
    return val;
  }
  if (hasOwn(target, key)) {
    target[key] = val;
    return val;
  }
  var ob = target.__ob__;
  if (target._isVue || ob && ob.vmCount) {
    process.env.NODE_ENV !== 'production' && warn('Avoid adding reactive properties to a Vue instance or its root $data ' + 'at runtime - declare it upfront in the data option.');
    return val;
  }
  if (!ob) {
    target[key] = val;
    return val;
  }
  defineReactive(ob.value, key, val);
  ob.dep.notify();
  return val;
}

/**
 * Delete a property and trigger change if necessary.
 */
function del(target, key) {
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.splice(key, 1);
    return;
  }
  var ob = target.__ob__;
  if (target._isVue || ob && ob.vmCount) {
    process.env.NODE_ENV !== 'production' && warn('Avoid deleting properties on a Vue instance or its root $data ' + '- just set it to null.');
    return;
  }
  if (!hasOwn(target, key)) {
    return;
  }
  delete target[key];
  if (!ob) {
    return;
  }
  ob.dep.notify();
}

/**
 * Collect dependencies on array elements when the array is touched, since
 * we cannot intercept array element access like property getters.
 */
function dependArray(value) {
  for (var e = void 0, i = 0, l = value.length; i < l; i++) {
    e = value[i];
    e && e.__ob__ && e.__ob__.dep.depend();
    if (Array.isArray(e)) {
      dependArray(e);
    }
  }
}

/*  */

/**
 * Option overwriting strategies are functions that handle
 * how to merge a parent option value and a child option
 * value into the final value.
 */
var strats = config.optionMergeStrategies;

/**
 * Options with restrictions
 */
if (process.env.NODE_ENV !== 'production') {
  strats.el = strats.propsData = function (parent, child, vm, key) {
    if (!vm) {
      warn("option \"" + key + "\" can only be used during instance " + 'creation with the `new` keyword.');
    }
    return defaultStrat(parent, child);
  };
}

/**
 * Helper that recursively merges two data objects together.
 */
function mergeData(to, from) {
  if (!from) {
    return to;
  }
  var key, toVal, fromVal;
  var keys = Object.keys(from);
  for (var i = 0; i < keys.length; i++) {
    key = keys[i];
    toVal = to[key];
    fromVal = from[key];
    if (!hasOwn(to, key)) {
      set(to, key, fromVal);
    } else if (isPlainObject(toVal) && isPlainObject(fromVal)) {
      mergeData(toVal, fromVal);
    }
  }
  return to;
}

/**
 * Data
 */
function mergeDataOrFn(parentVal, childVal, vm) {
  if (!vm) {
    // in a Vue.extend merge, both should be functions
    if (!childVal) {
      return parentVal;
    }
    if (!parentVal) {
      return childVal;
    }
    // when parentVal & childVal are both present,
    // we need to return a function that returns the
    // merged result of both functions... no need to
    // check if parentVal is a function here because
    // it has to be a function to pass previous merges.
    return function mergedDataFn() {
      return mergeData(typeof childVal === 'function' ? childVal.call(this) : childVal, typeof parentVal === 'function' ? parentVal.call(this) : parentVal);
    };
  } else if (parentVal || childVal) {
    return function mergedInstanceDataFn() {
      // instance merge
      var instanceData = typeof childVal === 'function' ? childVal.call(vm) : childVal;
      var defaultData = typeof parentVal === 'function' ? parentVal.call(vm) : parentVal;
      if (instanceData) {
        return mergeData(instanceData, defaultData);
      } else {
        return defaultData;
      }
    };
  }
}

strats.data = function (parentVal, childVal, vm) {
  if (!vm) {
    if (childVal && typeof childVal !== 'function') {
      process.env.NODE_ENV !== 'production' && warn('The "data" option should be a function ' + 'that returns a per-instance value in component ' + 'definitions.', vm);

      return parentVal;
    }
    return mergeDataOrFn.call(this, parentVal, childVal);
  }

  return mergeDataOrFn(parentVal, childVal, vm);
};

/**
 * Hooks and props are merged as arrays.
 */
function mergeHook(parentVal, childVal) {
  return childVal ? parentVal ? parentVal.concat(childVal) : Array.isArray(childVal) ? childVal : [childVal] : parentVal;
}

LIFECYCLE_HOOKS.forEach(function (hook) {
  strats[hook] = mergeHook;
});

/**
 * Assets
 *
 * When a vm is present (instance creation), we need to do
 * a three-way merge between constructor options, instance
 * options and parent options.
 */
function mergeAssets(parentVal, childVal, vm, key) {
  var res = Object.create(parentVal || null);
  if (childVal) {
    process.env.NODE_ENV !== 'production' && assertObjectType(key, childVal, vm);
    return extend(res, childVal);
  } else {
    return res;
  }
}

ASSET_TYPES.forEach(function (type) {
  strats[type + 's'] = mergeAssets;
});

/**
 * Watchers.
 *
 * Watchers hashes should not overwrite one
 * another, so we merge them as arrays.
 */
strats.watch = function (parentVal, childVal, vm, key) {
  // work around Firefox's Object.prototype.watch...
  if (parentVal === nativeWatch) {
    parentVal = undefined;
  }
  if (childVal === nativeWatch) {
    childVal = undefined;
  }
  /* istanbul ignore if */
  if (!childVal) {
    return Object.create(parentVal || null);
  }
  if (process.env.NODE_ENV !== 'production') {
    assertObjectType(key, childVal, vm);
  }
  if (!parentVal) {
    return childVal;
  }
  var ret = {};
  extend(ret, parentVal);
  for (var key$1 in childVal) {
    var parent = ret[key$1];
    var child = childVal[key$1];
    if (parent && !Array.isArray(parent)) {
      parent = [parent];
    }
    ret[key$1] = parent ? parent.concat(child) : Array.isArray(child) ? child : [child];
  }
  return ret;
};

/**
 * Other object hashes.
 */
strats.props = strats.methods = strats.inject = strats.computed = function (parentVal, childVal, vm, key) {
  if (childVal && process.env.NODE_ENV !== 'production') {
    assertObjectType(key, childVal, vm);
  }
  if (!parentVal) {
    return childVal;
  }
  var ret = Object.create(null);
  extend(ret, parentVal);
  if (childVal) {
    extend(ret, childVal);
  }
  return ret;
};
strats.provide = mergeDataOrFn;

/**
 * Default strategy.
 */
var defaultStrat = function defaultStrat(parentVal, childVal) {
  return childVal === undefined ? parentVal : childVal;
};

/**
 * Validate component names
 */
function checkComponents(options) {
  for (var key in options.components) {
    var lower = key.toLowerCase();
    if (isBuiltInTag(lower) || config.isReservedTag(lower)) {
      warn('Do not use built-in or reserved HTML elements as component ' + 'id: ' + key);
    }
  }
}

/**
 * Ensure all props option syntax are normalized into the
 * Object-based format.
 */
function normalizeProps(options, vm) {
  var props = options.props;
  if (!props) {
    return;
  }
  var res = {};
  var i, val, name;
  if (Array.isArray(props)) {
    i = props.length;
    while (i--) {
      val = props[i];
      if (typeof val === 'string') {
        name = camelize(val);
        res[name] = { type: null };
      } else if (process.env.NODE_ENV !== 'production') {
        warn('props must be strings when using array syntax.');
      }
    }
  } else if (isPlainObject(props)) {
    for (var key in props) {
      val = props[key];
      name = camelize(key);
      res[name] = isPlainObject(val) ? val : { type: val };
    }
  } else if (process.env.NODE_ENV !== 'production') {
    warn("Invalid value for option \"props\": expected an Array or an Object, " + "but got " + toRawType(props) + ".", vm);
  }
  options.props = res;
}

/**
 * Normalize all injections into Object-based format
 */
function normalizeInject(options, vm) {
  var inject = options.inject;
  var normalized = options.inject = {};
  if (Array.isArray(inject)) {
    for (var i = 0; i < inject.length; i++) {
      normalized[inject[i]] = { from: inject[i] };
    }
  } else if (isPlainObject(inject)) {
    for (var key in inject) {
      var val = inject[key];
      normalized[key] = isPlainObject(val) ? extend({ from: key }, val) : { from: val };
    }
  } else if (process.env.NODE_ENV !== 'production' && inject) {
    warn("Invalid value for option \"inject\": expected an Array or an Object, " + "but got " + toRawType(inject) + ".", vm);
  }
}

/**
 * Normalize raw function directives into object format.
 */
function normalizeDirectives(options) {
  var dirs = options.directives;
  if (dirs) {
    for (var key in dirs) {
      var def = dirs[key];
      if (typeof def === 'function') {
        dirs[key] = { bind: def, update: def };
      }
    }
  }
}

function assertObjectType(name, value, vm) {
  if (!isPlainObject(value)) {
    warn("Invalid value for option \"" + name + "\": expected an Object, " + "but got " + toRawType(value) + ".", vm);
  }
}

/**
 * Merge two option objects into a new one.
 * Core utility used in both instantiation and inheritance.
 */
function mergeOptions(parent, child, vm) {
  if (process.env.NODE_ENV !== 'production') {
    checkComponents(child);
  }

  if (typeof child === 'function') {
    child = child.options;
  }

  normalizeProps(child, vm);
  normalizeInject(child, vm);
  normalizeDirectives(child);
  var extendsFrom = child.extends;
  if (extendsFrom) {
    parent = mergeOptions(parent, extendsFrom, vm);
  }
  if (child.mixins) {
    for (var i = 0, l = child.mixins.length; i < l; i++) {
      parent = mergeOptions(parent, child.mixins[i], vm);
    }
  }
  var options = {};
  var key;
  for (key in parent) {
    mergeField(key);
  }
  for (key in child) {
    if (!hasOwn(parent, key)) {
      mergeField(key);
    }
  }
  function mergeField(key) {
    var strat = strats[key] || defaultStrat;
    options[key] = strat(parent[key], child[key], vm, key);
  }
  return options;
}

/**
 * Resolve an asset.
 * This function is used because child instances need access
 * to assets defined in its ancestor chain.
 */
function resolveAsset(options, type, id, warnMissing) {
  /* istanbul ignore if */
  if (typeof id !== 'string') {
    return;
  }
  var assets = options[type];
  // check local registration variations first
  if (hasOwn(assets, id)) {
    return assets[id];
  }
  var camelizedId = camelize(id);
  if (hasOwn(assets, camelizedId)) {
    return assets[camelizedId];
  }
  var PascalCaseId = capitalize(camelizedId);
  if (hasOwn(assets, PascalCaseId)) {
    return assets[PascalCaseId];
  }
  // fallback to prototype chain
  var res = assets[id] || assets[camelizedId] || assets[PascalCaseId];
  if (process.env.NODE_ENV !== 'production' && warnMissing && !res) {
    warn('Failed to resolve ' + type.slice(0, -1) + ': ' + id, options);
  }
  return res;
}

/*  */

function validateProp(key, propOptions, propsData, vm) {
  var prop = propOptions[key];
  var absent = !hasOwn(propsData, key);
  var value = propsData[key];
  // handle boolean props
  if (isType(Boolean, prop.type)) {
    if (absent && !hasOwn(prop, 'default')) {
      value = false;
    } else if (!isType(String, prop.type) && (value === '' || value === hyphenate(key))) {
      value = true;
    }
  }
  // check default value
  if (value === undefined) {
    value = getPropDefaultValue(vm, prop, key);
    // since the default value is a fresh copy,
    // make sure to observe it.
    var prevShouldConvert = observerState.shouldConvert;
    observerState.shouldConvert = true;
    observe(value);
    observerState.shouldConvert = prevShouldConvert;
  }
  if (process.env.NODE_ENV !== 'production') {
    assertProp(prop, key, value, vm, absent);
  }
  return value;
}

/**
 * Get the default value of a prop.
 */
function getPropDefaultValue(vm, prop, key) {
  // no default, return undefined
  if (!hasOwn(prop, 'default')) {
    return undefined;
  }
  var def = prop.default;
  // warn against non-factory defaults for Object & Array
  if (process.env.NODE_ENV !== 'production' && isObject(def)) {
    warn('Invalid default value for prop "' + key + '": ' + 'Props with type Object/Array must use a factory function ' + 'to return the default value.', vm);
  }
  // the raw prop value was also undefined from previous render,
  // return previous default value to avoid unnecessary watcher trigger
  if (vm && vm.$options.propsData && vm.$options.propsData[key] === undefined && vm._props[key] !== undefined) {
    return vm._props[key];
  }
  // call factory function for non-Function types
  // a value is Function if its prototype is function even across different execution context
  return typeof def === 'function' && getType(prop.type) !== 'Function' ? def.call(vm) : def;
}

/**
 * Assert whether a prop is valid.
 */
function assertProp(prop, name, value, vm, absent) {
  if (prop.required && absent) {
    warn('Missing required prop: "' + name + '"', vm);
    return;
  }
  if (value == null && !prop.required) {
    return;
  }
  var type = prop.type;
  var valid = !type || type === true;
  var expectedTypes = [];
  if (type) {
    if (!Array.isArray(type)) {
      type = [type];
    }
    for (var i = 0; i < type.length && !valid; i++) {
      var assertedType = assertType(value, type[i]);
      expectedTypes.push(assertedType.expectedType || '');
      valid = assertedType.valid;
    }
  }
  if (!valid) {
    warn("Invalid prop: type check failed for prop \"" + name + "\"." + " Expected " + expectedTypes.map(capitalize).join(', ') + ", got " + toRawType(value) + ".", vm);
    return;
  }
  var validator = prop.validator;
  if (validator) {
    if (!validator(value)) {
      warn('Invalid prop: custom validator check failed for prop "' + name + '".', vm);
    }
  }
}

var simpleCheckRE = /^(String|Number|Boolean|Function|Symbol)$/;

function assertType(value, type) {
  var valid;
  var expectedType = getType(type);
  if (simpleCheckRE.test(expectedType)) {
    var t = typeof value === 'undefined' ? 'undefined' : _typeof(value);
    valid = t === expectedType.toLowerCase();
    // for primitive wrapper objects
    if (!valid && t === 'object') {
      valid = value instanceof type;
    }
  } else if (expectedType === 'Object') {
    valid = isPlainObject(value);
  } else if (expectedType === 'Array') {
    valid = Array.isArray(value);
  } else {
    valid = value instanceof type;
  }
  return {
    valid: valid,
    expectedType: expectedType
  };
}

/**
 * Use function string name to check built-in types,
 * because a simple equality check will fail when running
 * across different vms / iframes.
 */
function getType(fn) {
  var match = fn && fn.toString().match(/^\s*function (\w+)/);
  return match ? match[1] : '';
}

function isType(type, fn) {
  if (!Array.isArray(fn)) {
    return getType(fn) === getType(type);
  }
  for (var i = 0, len = fn.length; i < len; i++) {
    if (getType(fn[i]) === getType(type)) {
      return true;
    }
  }
  /* istanbul ignore next */
  return false;
}

/*  */

function handleError(err, vm, info) {
  if (vm) {
    var cur = vm;
    while (cur = cur.$parent) {
      var hooks = cur.$options.errorCaptured;
      if (hooks) {
        for (var i = 0; i < hooks.length; i++) {
          try {
            var capture = hooks[i].call(cur, err, vm, info) === false;
            if (capture) {
              return;
            }
          } catch (e) {
            globalHandleError(e, cur, 'errorCaptured hook');
          }
        }
      }
    }
  }
  globalHandleError(err, vm, info);
}

function globalHandleError(err, vm, info) {
  if (config.errorHandler) {
    try {
      return config.errorHandler.call(null, err, vm, info);
    } catch (e) {
      logError(e, null, 'config.errorHandler');
    }
  }
  logError(err, vm, info);
}

function logError(err, vm, info) {
  if (process.env.NODE_ENV !== 'production') {
    warn("Error in " + info + ": \"" + err.toString() + "\"", vm);
  }
  /* istanbul ignore else */
  if (inBrowser && typeof console !== 'undefined') {
    console.error(err);
  } else {
    throw err;
  }
}

/*  */
/* globals MessageChannel */

var callbacks = [];
var pending = false;

function flushCallbacks() {
  pending = false;
  var copies = callbacks.slice(0);
  callbacks.length = 0;
  for (var i = 0; i < copies.length; i++) {
    copies[i]();
  }
}

// Here we have async deferring wrappers using both micro and macro tasks.
// In < 2.4 we used micro tasks everywhere, but there are some scenarios where
// micro tasks have too high a priority and fires in between supposedly
// sequential events (e.g. #4521, #6690) or even between bubbling of the same
// event (#6566). However, using macro tasks everywhere also has subtle problems
// when state is changed right before repaint (e.g. #6813, out-in transitions).
// Here we use micro task by default, but expose a way to force macro task when
// needed (e.g. in event handlers attached by v-on).
var microTimerFunc;
var macroTimerFunc;
var useMacroTask = false;

// Determine (macro) Task defer implementation.
// Technically setImmediate should be the ideal choice, but it's only available
// in IE. The only polyfill that consistently queues the callback after all DOM
// events triggered in the same loop is by using MessageChannel.
/* istanbul ignore if */
if (typeof setImmediate !== 'undefined' && isNative(setImmediate)) {
  macroTimerFunc = function macroTimerFunc() {
    setImmediate(flushCallbacks);
  };
} else if (typeof MessageChannel !== 'undefined' && (isNative(MessageChannel) ||
// PhantomJS
MessageChannel.toString() === '[object MessageChannelConstructor]')) {
  var channel = new MessageChannel();
  var port = channel.port2;
  channel.port1.onmessage = flushCallbacks;
  macroTimerFunc = function macroTimerFunc() {
    port.postMessage(1);
  };
} else {
  /* istanbul ignore next */
  macroTimerFunc = function macroTimerFunc() {
    setTimeout(flushCallbacks, 0);
  };
}

// Determine MicroTask defer implementation.
/* istanbul ignore next, $flow-disable-line */
if (typeof Promise !== 'undefined' && isNative(Promise)) {
  var p = Promise.resolve();
  microTimerFunc = function microTimerFunc() {
    p.then(flushCallbacks);
    // in problematic UIWebViews, Promise.then doesn't completely break, but
    // it can get stuck in a weird state where callbacks are pushed into the
    // microtask queue but the queue isn't being flushed, until the browser
    // needs to do some other work, e.g. handle a timer. Therefore we can
    // "force" the microtask queue to be flushed by adding an empty timer.
    if (isIOS) {
      setTimeout(noop);
    }
  };
} else {
  // fallback to macro
  microTimerFunc = macroTimerFunc;
}

/**
 * Wrap a function so that if any code inside triggers state change,
 * the changes are queued using a Task instead of a MicroTask.
 */
function withMacroTask(fn) {
  return fn._withTask || (fn._withTask = function () {
    useMacroTask = true;
    var res = fn.apply(null, arguments);
    useMacroTask = false;
    return res;
  });
}

function nextTick(cb, ctx) {
  var _resolve;
  callbacks.push(function () {
    if (cb) {
      try {
        cb.call(ctx);
      } catch (e) {
        handleError(e, ctx, 'nextTick');
      }
    } else if (_resolve) {
      _resolve(ctx);
    }
  });
  if (!pending) {
    pending = true;
    if (useMacroTask) {
      macroTimerFunc();
    } else {
      microTimerFunc();
    }
  }
  // $flow-disable-line
  if (!cb && typeof Promise !== 'undefined') {
    return new Promise(function (resolve) {
      _resolve = resolve;
    });
  }
}

/*  */

/* not type checking this file because flow doesn't play well with Proxy */

var initProxy;

if (process.env.NODE_ENV !== 'production') {
  var allowedGlobals = makeMap('Infinity,undefined,NaN,isFinite,isNaN,' + 'parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,' + 'Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,' + 'require' // for Webpack/Browserify
  );

  var warnNonPresent = function warnNonPresent(target, key) {
    warn("Property or method \"" + key + "\" is not defined on the instance but " + 'referenced during render. Make sure that this property is reactive, ' + 'either in the data option, or for class-based components, by ' + 'initializing the property. ' + 'See: https://vuejs.org/v2/guide/reactivity.html#Declaring-Reactive-Properties.', target);
  };

  var hasProxy = typeof Proxy !== 'undefined' && Proxy.toString().match(/native code/);

  if (hasProxy) {
    var isBuiltInModifier = makeMap('stop,prevent,self,ctrl,shift,alt,meta,exact');
    config.keyCodes = new Proxy(config.keyCodes, {
      set: function set(target, key, value) {
        if (isBuiltInModifier(key)) {
          warn("Avoid overwriting built-in modifier in config.keyCodes: ." + key);
          return false;
        } else {
          target[key] = value;
          return true;
        }
      }
    });
  }

  var hasHandler = {
    has: function has(target, key) {
      var has = key in target;
      var isAllowed = allowedGlobals(key) || key.charAt(0) === '_';
      if (!has && !isAllowed) {
        warnNonPresent(target, key);
      }
      return has || !isAllowed;
    }
  };

  var getHandler = {
    get: function get(target, key) {
      if (typeof key === 'string' && !(key in target)) {
        warnNonPresent(target, key);
      }
      return target[key];
    }
  };

  initProxy = function initProxy(vm) {
    if (hasProxy) {
      // determine which proxy handler to use
      var options = vm.$options;
      var handlers = options.render && options.render._withStripped ? getHandler : hasHandler;
      vm._renderProxy = new Proxy(vm, handlers);
    } else {
      vm._renderProxy = vm;
    }
  };
}

var mark;
var measure;

if (process.env.NODE_ENV !== 'production') {
  var perf = inBrowser && window.performance;
  /* istanbul ignore if */
  if (perf && perf.mark && perf.measure && perf.clearMarks && perf.clearMeasures) {
    mark = function mark(tag) {
      return perf.mark(tag);
    };
    measure = function measure(name, startTag, endTag) {
      perf.measure(name, startTag, endTag);
      perf.clearMarks(startTag);
      perf.clearMarks(endTag);
      perf.clearMeasures(name);
    };
  }
}

/*  */

var normalizeEvent = cached(function (name) {
  var passive = name.charAt(0) === '&';
  name = passive ? name.slice(1) : name;
  var once$$1 = name.charAt(0) === '~'; // Prefixed last, checked first
  name = once$$1 ? name.slice(1) : name;
  var capture = name.charAt(0) === '!';
  name = capture ? name.slice(1) : name;
  return {
    name: name,
    once: once$$1,
    capture: capture,
    passive: passive
  };
});

function createFnInvoker(fns) {
  function invoker() {
    var arguments$1 = arguments;

    var fns = invoker.fns;
    if (Array.isArray(fns)) {
      var cloned = fns.slice();
      for (var i = 0; i < cloned.length; i++) {
        cloned[i].apply(null, arguments$1);
      }
    } else {
      // return handler return value for single handlers
      return fns.apply(null, arguments);
    }
  }
  invoker.fns = fns;
  return invoker;
}

function updateListeners(on, oldOn, add, remove$$1, vm) {
  var name, cur, old, event;
  for (name in on) {
    cur = on[name];
    old = oldOn[name];
    event = normalizeEvent(name);
    if (isUndef(cur)) {
      process.env.NODE_ENV !== 'production' && warn("Invalid handler for event \"" + event.name + "\": got " + String(cur), vm);
    } else if (isUndef(old)) {
      if (isUndef(cur.fns)) {
        cur = on[name] = createFnInvoker(cur);
      }
      add(event.name, cur, event.once, event.capture, event.passive);
    } else if (cur !== old) {
      old.fns = cur;
      on[name] = old;
    }
  }
  for (name in oldOn) {
    if (isUndef(on[name])) {
      event = normalizeEvent(name);
      remove$$1(event.name, oldOn[name], event.capture);
    }
  }
}

/*  */

function mergeVNodeHook(def, hookKey, hook) {
  var invoker;
  var oldHook = def[hookKey];

  function wrappedHook() {
    hook.apply(this, arguments);
    // important: remove merged hook to ensure it's called only once
    // and prevent memory leak
    remove(invoker.fns, wrappedHook);
  }

  if (isUndef(oldHook)) {
    // no existing hook
    invoker = createFnInvoker([wrappedHook]);
  } else {
    /* istanbul ignore if */
    if (isDef(oldHook.fns) && isTrue(oldHook.merged)) {
      // already a merged invoker
      invoker = oldHook;
      invoker.fns.push(wrappedHook);
    } else {
      // existing plain hook
      invoker = createFnInvoker([oldHook, wrappedHook]);
    }
  }

  invoker.merged = true;
  def[hookKey] = invoker;
}

/*  */

function extractPropsFromVNodeData(data, Ctor, tag) {
  // we are only extracting raw values here.
  // validation and default values are handled in the child
  // component itself.
  var propOptions = Ctor.options.props;
  if (isUndef(propOptions)) {
    return;
  }
  var res = {};
  var attrs = data.attrs;
  var props = data.props;
  if (isDef(attrs) || isDef(props)) {
    for (var key in propOptions) {
      var altKey = hyphenate(key);
      if (process.env.NODE_ENV !== 'production') {
        var keyInLowerCase = key.toLowerCase();
        if (key !== keyInLowerCase && attrs && hasOwn(attrs, keyInLowerCase)) {
          tip("Prop \"" + keyInLowerCase + "\" is passed to component " + formatComponentName(tag || Ctor) + ", but the declared prop name is" + " \"" + key + "\". " + "Note that HTML attributes are case-insensitive and camelCased " + "props need to use their kebab-case equivalents when using in-DOM " + "templates. You should probably use \"" + altKey + "\" instead of \"" + key + "\".");
        }
      }
      checkProp(res, props, key, altKey, true) || checkProp(res, attrs, key, altKey, false);
    }
  }
  return res;
}

function checkProp(res, hash, key, altKey, preserve) {
  if (isDef(hash)) {
    if (hasOwn(hash, key)) {
      res[key] = hash[key];
      if (!preserve) {
        delete hash[key];
      }
      return true;
    } else if (hasOwn(hash, altKey)) {
      res[key] = hash[altKey];
      if (!preserve) {
        delete hash[altKey];
      }
      return true;
    }
  }
  return false;
}

/*  */

// The template compiler attempts to minimize the need for normalization by
// statically analyzing the template at compile time.
//
// For plain HTML markup, normalization can be completely skipped because the
// generated render function is guaranteed to return Array<VNode>. There are
// two cases where extra normalization is needed:

// 1. When the children contains components - because a functional component
// may return an Array instead of a single root. In this case, just a simple
// normalization is needed - if any child is an Array, we flatten the whole
// thing with Array.prototype.concat. It is guaranteed to be only 1-level deep
// because functional components already normalize their own children.
function simpleNormalizeChildren(children) {
  for (var i = 0; i < children.length; i++) {
    if (Array.isArray(children[i])) {
      return Array.prototype.concat.apply([], children);
    }
  }
  return children;
}

// 2. When the children contains constructs that always generated nested Arrays,
// e.g. <template>, <slot>, v-for, or when the children is provided by user
// with hand-written render functions / JSX. In such cases a full normalization
// is needed to cater to all possible types of children values.
function normalizeChildren(children) {
  return isPrimitive(children) ? [createTextVNode(children)] : Array.isArray(children) ? normalizeArrayChildren(children) : undefined;
}

function isTextNode(node) {
  return isDef(node) && isDef(node.text) && isFalse(node.isComment);
}

function normalizeArrayChildren(children, nestedIndex) {
  var res = [];
  var i, c, lastIndex, last;
  for (i = 0; i < children.length; i++) {
    c = children[i];
    if (isUndef(c) || typeof c === 'boolean') {
      continue;
    }
    lastIndex = res.length - 1;
    last = res[lastIndex];
    //  nested
    if (Array.isArray(c)) {
      if (c.length > 0) {
        c = normalizeArrayChildren(c, (nestedIndex || '') + "_" + i);
        // merge adjacent text nodes
        if (isTextNode(c[0]) && isTextNode(last)) {
          res[lastIndex] = createTextVNode(last.text + c[0].text);
          c.shift();
        }
        res.push.apply(res, c);
      }
    } else if (isPrimitive(c)) {
      if (isTextNode(last)) {
        // merge adjacent text nodes
        // this is necessary for SSR hydration because text nodes are
        // essentially merged when rendered to HTML strings
        res[lastIndex] = createTextVNode(last.text + c);
      } else if (c !== '') {
        // convert primitive to vnode
        res.push(createTextVNode(c));
      }
    } else {
      if (isTextNode(c) && isTextNode(last)) {
        // merge adjacent text nodes
        res[lastIndex] = createTextVNode(last.text + c.text);
      } else {
        // default key for nested array children (likely generated by v-for)
        if (isTrue(children._isVList) && isDef(c.tag) && isUndef(c.key) && isDef(nestedIndex)) {
          c.key = "__vlist" + nestedIndex + "_" + i + "__";
        }
        res.push(c);
      }
    }
  }
  return res;
}

/*  */

function ensureCtor(comp, base) {
  if (comp.__esModule || hasSymbol && comp[Symbol.toStringTag] === 'Module') {
    comp = comp.default;
  }
  return isObject(comp) ? base.extend(comp) : comp;
}

function createAsyncPlaceholder(factory, data, context, children, tag) {
  var node = createEmptyVNode();
  node.asyncFactory = factory;
  node.asyncMeta = { data: data, context: context, children: children, tag: tag };
  return node;
}

function resolveAsyncComponent(factory, baseCtor, context) {
  if (isTrue(factory.error) && isDef(factory.errorComp)) {
    return factory.errorComp;
  }

  if (isDef(factory.resolved)) {
    return factory.resolved;
  }

  if (isTrue(factory.loading) && isDef(factory.loadingComp)) {
    return factory.loadingComp;
  }

  if (isDef(factory.contexts)) {
    // already pending
    factory.contexts.push(context);
  } else {
    var contexts = factory.contexts = [context];
    var sync = true;

    var forceRender = function forceRender() {
      for (var i = 0, l = contexts.length; i < l; i++) {
        contexts[i].$forceUpdate();
      }
    };

    var resolve = once(function (res) {
      // cache resolved
      factory.resolved = ensureCtor(res, baseCtor);
      // invoke callbacks only if this is not a synchronous resolve
      // (async resolves are shimmed as synchronous during SSR)
      if (!sync) {
        forceRender();
      }
    });

    var reject = once(function (reason) {
      process.env.NODE_ENV !== 'production' && warn("Failed to resolve async component: " + String(factory) + (reason ? "\nReason: " + reason : ''));
      if (isDef(factory.errorComp)) {
        factory.error = true;
        forceRender();
      }
    });

    var res = factory(resolve, reject);

    if (isObject(res)) {
      if (typeof res.then === 'function') {
        // () => Promise
        if (isUndef(factory.resolved)) {
          res.then(resolve, reject);
        }
      } else if (isDef(res.component) && typeof res.component.then === 'function') {
        res.component.then(resolve, reject);

        if (isDef(res.error)) {
          factory.errorComp = ensureCtor(res.error, baseCtor);
        }

        if (isDef(res.loading)) {
          factory.loadingComp = ensureCtor(res.loading, baseCtor);
          if (res.delay === 0) {
            factory.loading = true;
          } else {
            setTimeout(function () {
              if (isUndef(factory.resolved) && isUndef(factory.error)) {
                factory.loading = true;
                forceRender();
              }
            }, res.delay || 200);
          }
        }

        if (isDef(res.timeout)) {
          setTimeout(function () {
            if (isUndef(factory.resolved)) {
              reject(process.env.NODE_ENV !== 'production' ? "timeout (" + res.timeout + "ms)" : null);
            }
          }, res.timeout);
        }
      }
    }

    sync = false;
    // return in case resolved synchronously
    return factory.loading ? factory.loadingComp : factory.resolved;
  }
}

/*  */

function isAsyncPlaceholder(node) {
  return node.isComment && node.asyncFactory;
}

/*  */

function getFirstComponentChild(children) {
  if (Array.isArray(children)) {
    for (var i = 0; i < children.length; i++) {
      var c = children[i];
      if (isDef(c) && (isDef(c.componentOptions) || isAsyncPlaceholder(c))) {
        return c;
      }
    }
  }
}

/*  */

/*  */

function initEvents(vm) {
  vm._events = Object.create(null);
  vm._hasHookEvent = false;
  // init parent attached events
  var listeners = vm.$options._parentListeners;
  if (listeners) {
    updateComponentListeners(vm, listeners);
  }
}

var target;

function add(event, fn, once) {
  if (once) {
    target.$once(event, fn);
  } else {
    target.$on(event, fn);
  }
}

function remove$1(event, fn) {
  target.$off(event, fn);
}

function updateComponentListeners(vm, listeners, oldListeners) {
  target = vm;
  updateListeners(listeners, oldListeners || {}, add, remove$1, vm);
}

function eventsMixin(Vue) {
  var hookRE = /^hook:/;
  Vue.prototype.$on = function (event, fn) {
    var this$1 = this;

    var vm = this;
    if (Array.isArray(event)) {
      for (var i = 0, l = event.length; i < l; i++) {
        this$1.$on(event[i], fn);
      }
    } else {
      (vm._events[event] || (vm._events[event] = [])).push(fn);
      // optimize hook:event cost by using a boolean flag marked at registration
      // instead of a hash lookup
      if (hookRE.test(event)) {
        vm._hasHookEvent = true;
      }
    }
    return vm;
  };

  Vue.prototype.$once = function (event, fn) {
    var vm = this;
    function on() {
      vm.$off(event, on);
      fn.apply(vm, arguments);
    }
    on.fn = fn;
    vm.$on(event, on);
    return vm;
  };

  Vue.prototype.$off = function (event, fn) {
    var this$1 = this;

    var vm = this;
    // all
    if (!arguments.length) {
      vm._events = Object.create(null);
      return vm;
    }
    // array of events
    if (Array.isArray(event)) {
      for (var i = 0, l = event.length; i < l; i++) {
        this$1.$off(event[i], fn);
      }
      return vm;
    }
    // specific event
    var cbs = vm._events[event];
    if (!cbs) {
      return vm;
    }
    if (arguments.length === 1) {
      vm._events[event] = null;
      return vm;
    }
    if (fn) {
      // specific handler
      var cb;
      var i$1 = cbs.length;
      while (i$1--) {
        cb = cbs[i$1];
        if (cb === fn || cb.fn === fn) {
          cbs.splice(i$1, 1);
          break;
        }
      }
    }
    return vm;
  };

  Vue.prototype.$emit = function (event) {
    var vm = this;
    if (process.env.NODE_ENV !== 'production') {
      var lowerCaseEvent = event.toLowerCase();
      if (lowerCaseEvent !== event && vm._events[lowerCaseEvent]) {
        tip("Event \"" + lowerCaseEvent + "\" is emitted in component " + formatComponentName(vm) + " but the handler is registered for \"" + event + "\". " + "Note that HTML attributes are case-insensitive and you cannot use " + "v-on to listen to camelCase events when using in-DOM templates. " + "You should probably use \"" + hyphenate(event) + "\" instead of \"" + event + "\".");
      }
    }
    var cbs = vm._events[event];
    if (cbs) {
      cbs = cbs.length > 1 ? toArray(cbs) : cbs;
      var args = toArray(arguments, 1);
      for (var i = 0, l = cbs.length; i < l; i++) {
        try {
          cbs[i].apply(vm, args);
        } catch (e) {
          handleError(e, vm, "event handler for \"" + event + "\"");
        }
      }
    }
    return vm;
  };
}

/*  */

/**
 * Runtime helper for resolving raw children VNodes into a slot object.
 */
function resolveSlots(children, context) {
  var slots = {};
  if (!children) {
    return slots;
  }
  var defaultSlot = [];
  for (var i = 0, l = children.length; i < l; i++) {
    var child = children[i];
    var data = child.data;
    // remove slot attribute if the node is resolved as a Vue slot node
    if (data && data.attrs && data.attrs.slot) {
      delete data.attrs.slot;
    }
    // named slots should only be respected if the vnode was rendered in the
    // same context.
    if ((child.context === context || child.functionalContext === context) && data && data.slot != null) {
      var name = child.data.slot;
      var slot = slots[name] || (slots[name] = []);
      if (child.tag === 'template') {
        slot.push.apply(slot, child.children);
      } else {
        slot.push(child);
      }
    } else {
      defaultSlot.push(child);
    }
  }
  // ignore whitespace
  if (!defaultSlot.every(isWhitespace)) {
    slots.default = defaultSlot;
  }
  return slots;
}

function isWhitespace(node) {
  return node.isComment || node.text === ' ';
}

function resolveScopedSlots(fns, // see flow/vnode
res) {
  res = res || {};
  for (var i = 0; i < fns.length; i++) {
    if (Array.isArray(fns[i])) {
      resolveScopedSlots(fns[i], res);
    } else {
      res[fns[i].key] = fns[i].fn;
    }
  }
  return res;
}

/*  */

var activeInstance = null;
var isUpdatingChildComponent = false;

function initLifecycle(vm) {
  var options = vm.$options;

  // locate first non-abstract parent
  var parent = options.parent;
  if (parent && !options.abstract) {
    while (parent.$options.abstract && parent.$parent) {
      parent = parent.$parent;
    }
    parent.$children.push(vm);
  }

  vm.$parent = parent;
  vm.$root = parent ? parent.$root : vm;

  vm.$children = [];
  vm.$refs = {};

  vm._watcher = null;
  vm._inactive = null;
  vm._directInactive = false;
  vm._isMounted = false;
  vm._isDestroyed = false;
  vm._isBeingDestroyed = false;
}

function lifecycleMixin(Vue) {
  Vue.prototype._update = function (vnode, hydrating) {
    var vm = this;
    if (vm._isMounted) {
      callHook(vm, 'beforeUpdate');
    }
    var prevEl = vm.$el;
    var prevVnode = vm._vnode;
    var prevActiveInstance = activeInstance;
    activeInstance = vm;
    vm._vnode = vnode;
    // Vue.prototype.__patch__ is injected in entry points
    // based on the rendering backend used.
    if (!prevVnode) {
      // initial render
      vm.$el = vm.__patch__(vm.$el, vnode, hydrating, false /* removeOnly */
      , vm.$options._parentElm, vm.$options._refElm);
      // no need for the ref nodes after initial patch
      // this prevents keeping a detached DOM tree in memory (#5851)
      vm.$options._parentElm = vm.$options._refElm = null;
    } else {
      // updates
      vm.$el = vm.__patch__(prevVnode, vnode);
    }
    activeInstance = prevActiveInstance;
    // update __vue__ reference
    if (prevEl) {
      prevEl.__vue__ = null;
    }
    if (vm.$el) {
      vm.$el.__vue__ = vm;
    }
    // if parent is an HOC, update its $el as well
    if (vm.$vnode && vm.$parent && vm.$vnode === vm.$parent._vnode) {
      vm.$parent.$el = vm.$el;
    }
    // updated hook is called by the scheduler to ensure that children are
    // updated in a parent's updated hook.
  };

  Vue.prototype.$forceUpdate = function () {
    var vm = this;
    if (vm._watcher) {
      vm._watcher.update();
    }
  };

  Vue.prototype.$destroy = function () {
    var vm = this;
    if (vm._isBeingDestroyed) {
      return;
    }
    callHook(vm, 'beforeDestroy');
    vm._isBeingDestroyed = true;
    // remove self from parent
    var parent = vm.$parent;
    if (parent && !parent._isBeingDestroyed && !vm.$options.abstract) {
      remove(parent.$children, vm);
    }
    // teardown watchers
    if (vm._watcher) {
      vm._watcher.teardown();
    }
    var i = vm._watchers.length;
    while (i--) {
      vm._watchers[i].teardown();
    }
    // remove reference from data ob
    // frozen object may not have observer.
    if (vm._data.__ob__) {
      vm._data.__ob__.vmCount--;
    }
    // call the last hook...
    vm._isDestroyed = true;
    // invoke destroy hooks on current rendered tree
    vm.__patch__(vm._vnode, null);
    // fire destroyed hook
    callHook(vm, 'destroyed');
    // turn off all instance listeners.
    vm.$off();
    // remove __vue__ reference
    if (vm.$el) {
      vm.$el.__vue__ = null;
    }
    // release circular reference (#6759)
    if (vm.$vnode) {
      vm.$vnode.parent = null;
    }
  };
}

function mountComponent(vm, el, hydrating) {
  vm.$el = el;
  if (!vm.$options.render) {
    vm.$options.render = createEmptyVNode;
    if (process.env.NODE_ENV !== 'production') {
      /* istanbul ignore if */
      if (vm.$options.template && vm.$options.template.charAt(0) !== '#' || vm.$options.el || el) {
        warn('You are using the runtime-only build of Vue where the template ' + 'compiler is not available. Either pre-compile the templates into ' + 'render functions, or use the compiler-included build.', vm);
      } else {
        warn('Failed to mount component: template or render function not defined.', vm);
      }
    }
  }
  callHook(vm, 'beforeMount');

  var updateComponent;
  /* istanbul ignore if */
  if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
    updateComponent = function updateComponent() {
      var name = vm._name;
      var id = vm._uid;
      var startTag = "vue-perf-start:" + id;
      var endTag = "vue-perf-end:" + id;

      mark(startTag);
      var vnode = vm._render();
      mark(endTag);
      measure("vue " + name + " render", startTag, endTag);

      mark(startTag);
      vm._update(vnode, hydrating);
      mark(endTag);
      measure("vue " + name + " patch", startTag, endTag);
    };
  } else {
    updateComponent = function updateComponent() {
      vm._update(vm._render(), hydrating);
    };
  }

  vm._watcher = new Watcher(vm, updateComponent, noop);
  hydrating = false;

  // manually mounted instance, call mounted on self
  // mounted is called for render-created child components in its inserted hook
  if (vm.$vnode == null) {
    vm._isMounted = true;
    callHook(vm, 'mounted');
  }
  return vm;
}

function updateChildComponent(vm, propsData, listeners, parentVnode, renderChildren) {
  if (process.env.NODE_ENV !== 'production') {
    isUpdatingChildComponent = true;
  }

  // determine whether component has slot children
  // we need to do this before overwriting $options._renderChildren
  var hasChildren = !!(renderChildren || // has new static slots
  vm.$options._renderChildren || // has old static slots
  parentVnode.data.scopedSlots || // has new scoped slots
  vm.$scopedSlots !== emptyObject // has old scoped slots
  );

  vm.$options._parentVnode = parentVnode;
  vm.$vnode = parentVnode; // update vm's placeholder node without re-render

  if (vm._vnode) {
    // update child tree's parent
    vm._vnode.parent = parentVnode;
  }
  vm.$options._renderChildren = renderChildren;

  // update $attrs and $listeners hash
  // these are also reactive so they may trigger child update if the child
  // used them during render
  vm.$attrs = parentVnode.data && parentVnode.data.attrs || emptyObject;
  vm.$listeners = listeners || emptyObject;

  // update props
  if (propsData && vm.$options.props) {
    observerState.shouldConvert = false;
    var props = vm._props;
    var propKeys = vm.$options._propKeys || [];
    for (var i = 0; i < propKeys.length; i++) {
      var key = propKeys[i];
      props[key] = validateProp(key, vm.$options.props, propsData, vm);
    }
    observerState.shouldConvert = true;
    // keep a copy of raw propsData
    vm.$options.propsData = propsData;
  }

  // update listeners
  if (listeners) {
    var oldListeners = vm.$options._parentListeners;
    vm.$options._parentListeners = listeners;
    updateComponentListeners(vm, listeners, oldListeners);
  }
  // resolve slots + force update if has children
  if (hasChildren) {
    vm.$slots = resolveSlots(renderChildren, parentVnode.context);
    vm.$forceUpdate();
  }

  if (process.env.NODE_ENV !== 'production') {
    isUpdatingChildComponent = false;
  }
}

function isInInactiveTree(vm) {
  while (vm && (vm = vm.$parent)) {
    if (vm._inactive) {
      return true;
    }
  }
  return false;
}

function activateChildComponent(vm, direct) {
  if (direct) {
    vm._directInactive = false;
    if (isInInactiveTree(vm)) {
      return;
    }
  } else if (vm._directInactive) {
    return;
  }
  if (vm._inactive || vm._inactive === null) {
    vm._inactive = false;
    for (var i = 0; i < vm.$children.length; i++) {
      activateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'activated');
  }
}

function deactivateChildComponent(vm, direct) {
  if (direct) {
    vm._directInactive = true;
    if (isInInactiveTree(vm)) {
      return;
    }
  }
  if (!vm._inactive) {
    vm._inactive = true;
    for (var i = 0; i < vm.$children.length; i++) {
      deactivateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'deactivated');
  }
}

function callHook(vm, hook) {
  var handlers = vm.$options[hook];
  if (handlers) {
    for (var i = 0, j = handlers.length; i < j; i++) {
      try {
        handlers[i].call(vm);
      } catch (e) {
        handleError(e, vm, hook + " hook");
      }
    }
  }
  if (vm._hasHookEvent) {
    vm.$emit('hook:' + hook);
  }
}

/*  */

var MAX_UPDATE_COUNT = 100;

var queue = [];
var activatedChildren = [];
var has = {};
var circular = {};
var waiting = false;
var flushing = false;
var index = 0;

/**
 * Reset the scheduler's state.
 */
function resetSchedulerState() {
  index = queue.length = activatedChildren.length = 0;
  has = {};
  if (process.env.NODE_ENV !== 'production') {
    circular = {};
  }
  waiting = flushing = false;
}

/**
 * Flush both queues and run the watchers.
 */
function flushSchedulerQueue() {
  flushing = true;
  var watcher, id;

  // Sort queue before flush.
  // This ensures that:
  // 1. Components are updated from parent to child. (because parent is always
  //    created before the child)
  // 2. A component's user watchers are run before its render watcher (because
  //    user watchers are created before the render watcher)
  // 3. If a component is destroyed during a parent component's watcher run,
  //    its watchers can be skipped.
  queue.sort(function (a, b) {
    return a.id - b.id;
  });

  // do not cache length because more watchers might be pushed
  // as we run existing watchers
  for (index = 0; index < queue.length; index++) {
    watcher = queue[index];
    id = watcher.id;
    has[id] = null;
    watcher.run();
    // in dev build, check and stop circular updates.
    if (process.env.NODE_ENV !== 'production' && has[id] != null) {
      circular[id] = (circular[id] || 0) + 1;
      if (circular[id] > MAX_UPDATE_COUNT) {
        warn('You may have an infinite update loop ' + (watcher.user ? "in watcher with expression \"" + watcher.expression + "\"" : "in a component render function."), watcher.vm);
        break;
      }
    }
  }

  // keep copies of post queues before resetting state
  var activatedQueue = activatedChildren.slice();
  var updatedQueue = queue.slice();

  resetSchedulerState();

  // call component updated and activated hooks
  callActivatedHooks(activatedQueue);
  callUpdatedHooks(updatedQueue);

  // devtool hook
  /* istanbul ignore if */
  if (devtools && config.devtools) {
    devtools.emit('flush');
  }
}

function callUpdatedHooks(queue) {
  var i = queue.length;
  while (i--) {
    var watcher = queue[i];
    var vm = watcher.vm;
    if (vm._watcher === watcher && vm._isMounted) {
      callHook(vm, 'updated');
    }
  }
}

/**
 * Queue a kept-alive component that was activated during patch.
 * The queue will be processed after the entire tree has been patched.
 */
function queueActivatedComponent(vm) {
  // setting _inactive to false here so that a render function can
  // rely on checking whether it's in an inactive tree (e.g. router-view)
  vm._inactive = false;
  activatedChildren.push(vm);
}

function callActivatedHooks(queue) {
  for (var i = 0; i < queue.length; i++) {
    queue[i]._inactive = true;
    activateChildComponent(queue[i], true /* true */);
  }
}

/**
 * Push a watcher into the watcher queue.
 * Jobs with duplicate IDs will be skipped unless it's
 * pushed when the queue is being flushed.
 */
function queueWatcher(watcher) {
  var id = watcher.id;
  if (has[id] == null) {
    has[id] = true;
    if (!flushing) {
      queue.push(watcher);
    } else {
      // if already flushing, splice the watcher based on its id
      // if already past its id, it will be run next immediately.
      var i = queue.length - 1;
      while (i > index && queue[i].id > watcher.id) {
        i--;
      }
      queue.splice(i + 1, 0, watcher);
    }
    // queue the flush
    if (!waiting) {
      waiting = true;
      nextTick(flushSchedulerQueue);
    }
  }
}

/*  */

var uid$2 = 0;

/**
 * A watcher parses an expression, collects dependencies,
 * and fires callback when the expression value changes.
 * This is used for both the $watch() api and directives.
 */
var Watcher = function Watcher(vm, expOrFn, cb, options) {
  this.vm = vm;
  vm._watchers.push(this);
  // options
  if (options) {
    this.deep = !!options.deep;
    this.user = !!options.user;
    this.lazy = !!options.lazy;
    this.sync = !!options.sync;
  } else {
    this.deep = this.user = this.lazy = this.sync = false;
  }
  this.cb = cb;
  this.id = ++uid$2; // uid for batching
  this.active = true;
  this.dirty = this.lazy; // for lazy watchers
  this.deps = [];
  this.newDeps = [];
  this.depIds = new _Set();
  this.newDepIds = new _Set();
  this.expression = process.env.NODE_ENV !== 'production' ? expOrFn.toString() : '';
  // parse expression for getter
  if (typeof expOrFn === 'function') {
    this.getter = expOrFn;
  } else {
    this.getter = parsePath(expOrFn);
    if (!this.getter) {
      this.getter = function () {};
      process.env.NODE_ENV !== 'production' && warn("Failed watching path: \"" + expOrFn + "\" " + 'Watcher only accepts simple dot-delimited paths. ' + 'For full control, use a function instead.', vm);
    }
  }
  this.value = this.lazy ? undefined : this.get();
};

/**
 * Evaluate the getter, and re-collect dependencies.
 */
Watcher.prototype.get = function get() {
  pushTarget(this);
  var value;
  var vm = this.vm;
  try {
    value = this.getter.call(vm, vm);
  } catch (e) {
    if (this.user) {
      handleError(e, vm, "getter for watcher \"" + this.expression + "\"");
    } else {
      throw e;
    }
  } finally {
    // "touch" every property so they are all tracked as
    // dependencies for deep watching
    if (this.deep) {
      traverse(value);
    }
    popTarget();
    this.cleanupDeps();
  }
  return value;
};

/**
 * Add a dependency to this directive.
 */
Watcher.prototype.addDep = function addDep(dep) {
  var id = dep.id;
  if (!this.newDepIds.has(id)) {
    this.newDepIds.add(id);
    this.newDeps.push(dep);
    if (!this.depIds.has(id)) {
      dep.addSub(this);
    }
  }
};

/**
 * Clean up for dependency collection.
 */
Watcher.prototype.cleanupDeps = function cleanupDeps() {
  var this$1 = this;

  var i = this.deps.length;
  while (i--) {
    var dep = this$1.deps[i];
    if (!this$1.newDepIds.has(dep.id)) {
      dep.removeSub(this$1);
    }
  }
  var tmp = this.depIds;
  this.depIds = this.newDepIds;
  this.newDepIds = tmp;
  this.newDepIds.clear();
  tmp = this.deps;
  this.deps = this.newDeps;
  this.newDeps = tmp;
  this.newDeps.length = 0;
};

/**
 * Subscriber interface.
 * Will be called when a dependency changes.
 */
Watcher.prototype.update = function update() {
  /* istanbul ignore else */
  if (this.lazy) {
    this.dirty = true;
  } else if (this.sync) {
    this.run();
  } else {
    queueWatcher(this);
  }
};

/**
 * Scheduler job interface.
 * Will be called by the scheduler.
 */
Watcher.prototype.run = function run() {
  if (this.active) {
    var value = this.get();
    if (value !== this.value ||
    // Deep watchers and watchers on Object/Arrays should fire even
    // when the value is the same, because the value may
    // have mutated.
    isObject(value) || this.deep) {
      // set new value
      var oldValue = this.value;
      this.value = value;
      if (this.user) {
        try {
          this.cb.call(this.vm, value, oldValue);
        } catch (e) {
          handleError(e, this.vm, "callback for watcher \"" + this.expression + "\"");
        }
      } else {
        this.cb.call(this.vm, value, oldValue);
      }
    }
  }
};

/**
 * Evaluate the value of the watcher.
 * This only gets called for lazy watchers.
 */
Watcher.prototype.evaluate = function evaluate() {
  this.value = this.get();
  this.dirty = false;
};

/**
 * Depend on all deps collected by this watcher.
 */
Watcher.prototype.depend = function depend() {
  var this$1 = this;

  var i = this.deps.length;
  while (i--) {
    this$1.deps[i].depend();
  }
};

/**
 * Remove self from all dependencies' subscriber list.
 */
Watcher.prototype.teardown = function teardown() {
  var this$1 = this;

  if (this.active) {
    // remove self from vm's watcher list
    // this is a somewhat expensive operation so we skip it
    // if the vm is being destroyed.
    if (!this.vm._isBeingDestroyed) {
      remove(this.vm._watchers, this);
    }
    var i = this.deps.length;
    while (i--) {
      this$1.deps[i].removeSub(this$1);
    }
    this.active = false;
  }
};

/**
 * Recursively traverse an object to evoke all converted
 * getters, so that every nested property inside the object
 * is collected as a "deep" dependency.
 */
var seenObjects = new _Set();
function traverse(val) {
  seenObjects.clear();
  _traverse(val, seenObjects);
}

function _traverse(val, seen) {
  var i, keys;
  var isA = Array.isArray(val);
  if (!isA && !isObject(val) || !Object.isExtensible(val)) {
    return;
  }
  if (val.__ob__) {
    var depId = val.__ob__.dep.id;
    if (seen.has(depId)) {
      return;
    }
    seen.add(depId);
  }
  if (isA) {
    i = val.length;
    while (i--) {
      _traverse(val[i], seen);
    }
  } else {
    keys = Object.keys(val);
    i = keys.length;
    while (i--) {
      _traverse(val[keys[i]], seen);
    }
  }
}

/*  */

var sharedPropertyDefinition = {
  enumerable: true,
  configurable: true,
  get: noop,
  set: noop
};

function proxy(target, sourceKey, key) {
  sharedPropertyDefinition.get = function proxyGetter() {
    return this[sourceKey][key];
  };
  sharedPropertyDefinition.set = function proxySetter(val) {
    this[sourceKey][key] = val;
  };
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function initState(vm) {
  vm._watchers = [];
  var opts = vm.$options;
  if (opts.props) {
    initProps(vm, opts.props);
  }
  if (opts.methods) {
    initMethods(vm, opts.methods);
  }
  if (opts.data) {
    initData(vm);
  } else {
    observe(vm._data = {}, true /* asRootData */);
  }
  if (opts.computed) {
    initComputed(vm, opts.computed);
  }
  if (opts.watch && opts.watch !== nativeWatch) {
    initWatch(vm, opts.watch);
  }
}

function initProps(vm, propsOptions) {
  var propsData = vm.$options.propsData || {};
  var props = vm._props = {};
  // cache prop keys so that future props updates can iterate using Array
  // instead of dynamic object key enumeration.
  var keys = vm.$options._propKeys = [];
  var isRoot = !vm.$parent;
  // root instance props should be converted
  observerState.shouldConvert = isRoot;
  var loop = function loop(key) {
    keys.push(key);
    var value = validateProp(key, propsOptions, propsData, vm);
    /* istanbul ignore else */
    if (process.env.NODE_ENV !== 'production') {
      var hyphenatedKey = hyphenate(key);
      if (isReservedAttribute(hyphenatedKey) || config.isReservedAttr(hyphenatedKey)) {
        warn("\"" + hyphenatedKey + "\" is a reserved attribute and cannot be used as component prop.", vm);
      }
      defineReactive(props, key, value, function () {
        if (vm.$parent && !isUpdatingChildComponent) {
          warn("Avoid mutating a prop directly since the value will be " + "overwritten whenever the parent component re-renders. " + "Instead, use a data or computed property based on the prop's " + "value. Prop being mutated: \"" + key + "\"", vm);
        }
      });
    } else {
      defineReactive(props, key, value);
    }
    // static props are already proxied on the component's prototype
    // during Vue.extend(). We only need to proxy props defined at
    // instantiation here.
    if (!(key in vm)) {
      proxy(vm, "_props", key);
    }
  };

  for (var key in propsOptions) {
    loop(key);
  }observerState.shouldConvert = true;
}

function initData(vm) {
  var data = vm.$options.data;
  data = vm._data = typeof data === 'function' ? getData(data, vm) : data || {};
  if (!isPlainObject(data)) {
    data = {};
    process.env.NODE_ENV !== 'production' && warn('data functions should return an object:\n' + 'https://vuejs.org/v2/guide/components.html#data-Must-Be-a-Function', vm);
  }
  // proxy data on instance
  var keys = Object.keys(data);
  var props = vm.$options.props;
  var methods = vm.$options.methods;
  var i = keys.length;
  while (i--) {
    var key = keys[i];
    if (process.env.NODE_ENV !== 'production') {
      if (methods && hasOwn(methods, key)) {
        warn("Method \"" + key + "\" has already been defined as a data property.", vm);
      }
    }
    if (props && hasOwn(props, key)) {
      process.env.NODE_ENV !== 'production' && warn("The data property \"" + key + "\" is already declared as a prop. " + "Use prop default value instead.", vm);
    } else if (!isReserved(key)) {
      proxy(vm, "_data", key);
    }
  }
  // observe data
  observe(data, true /* asRootData */);
}

function getData(data, vm) {
  try {
    return data.call(vm, vm);
  } catch (e) {
    handleError(e, vm, "data()");
    return {};
  }
}

var computedWatcherOptions = { lazy: true };

function initComputed(vm, computed) {
  var watchers = vm._computedWatchers = Object.create(null);
  // computed properties are just getters during SSR
  var isSSR = isServerRendering();

  for (var key in computed) {
    var userDef = computed[key];
    var getter = typeof userDef === 'function' ? userDef : userDef.get;
    if (process.env.NODE_ENV !== 'production' && getter == null) {
      warn("Getter is missing for computed property \"" + key + "\".", vm);
    }

    if (!isSSR) {
      // create internal watcher for the computed property.
      watchers[key] = new Watcher(vm, getter || noop, noop, computedWatcherOptions);
    }

    // component-defined computed properties are already defined on the
    // component prototype. We only need to define computed properties defined
    // at instantiation here.
    if (!(key in vm)) {
      defineComputed(vm, key, userDef);
    } else if (process.env.NODE_ENV !== 'production') {
      if (key in vm.$data) {
        warn("The computed property \"" + key + "\" is already defined in data.", vm);
      } else if (vm.$options.props && key in vm.$options.props) {
        warn("The computed property \"" + key + "\" is already defined as a prop.", vm);
      }
    }
  }
}

function defineComputed(target, key, userDef) {
  var shouldCache = !isServerRendering();
  if (typeof userDef === 'function') {
    sharedPropertyDefinition.get = shouldCache ? createComputedGetter(key) : userDef;
    sharedPropertyDefinition.set = noop;
  } else {
    sharedPropertyDefinition.get = userDef.get ? shouldCache && userDef.cache !== false ? createComputedGetter(key) : userDef.get : noop;
    sharedPropertyDefinition.set = userDef.set ? userDef.set : noop;
  }
  if (process.env.NODE_ENV !== 'production' && sharedPropertyDefinition.set === noop) {
    sharedPropertyDefinition.set = function () {
      warn("Computed property \"" + key + "\" was assigned to but it has no setter.", this);
    };
  }
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function createComputedGetter(key) {
  return function computedGetter() {
    var watcher = this._computedWatchers && this._computedWatchers[key];
    if (watcher) {
      if (watcher.dirty) {
        watcher.evaluate();
      }
      if (Dep.target) {
        watcher.depend();
      }
      return watcher.value;
    }
  };
}

function initMethods(vm, methods) {
  var props = vm.$options.props;
  for (var key in methods) {
    if (process.env.NODE_ENV !== 'production') {
      if (methods[key] == null) {
        warn("Method \"" + key + "\" has an undefined value in the component definition. " + "Did you reference the function correctly?", vm);
      }
      if (props && hasOwn(props, key)) {
        warn("Method \"" + key + "\" has already been defined as a prop.", vm);
      }
      if (key in vm && isReserved(key)) {
        warn("Method \"" + key + "\" conflicts with an existing Vue instance method. " + "Avoid defining component methods that start with _ or $.");
      }
    }
    vm[key] = methods[key] == null ? noop : bind(methods[key], vm);
  }
}

function initWatch(vm, watch) {
  for (var key in watch) {
    var handler = watch[key];
    if (Array.isArray(handler)) {
      for (var i = 0; i < handler.length; i++) {
        createWatcher(vm, key, handler[i]);
      }
    } else {
      createWatcher(vm, key, handler);
    }
  }
}

function createWatcher(vm, keyOrFn, handler, options) {
  if (isPlainObject(handler)) {
    options = handler;
    handler = handler.handler;
  }
  if (typeof handler === 'string') {
    handler = vm[handler];
  }
  return vm.$watch(keyOrFn, handler, options);
}

function stateMixin(Vue) {
  // flow somehow has problems with directly declared definition object
  // when using Object.defineProperty, so we have to procedurally build up
  // the object here.
  var dataDef = {};
  dataDef.get = function () {
    return this._data;
  };
  var propsDef = {};
  propsDef.get = function () {
    return this._props;
  };
  if (process.env.NODE_ENV !== 'production') {
    dataDef.set = function (newData) {
      warn('Avoid replacing instance root $data. ' + 'Use nested data properties instead.', this);
    };
    propsDef.set = function () {
      warn("$props is readonly.", this);
    };
  }
  Object.defineProperty(Vue.prototype, '$data', dataDef);
  Object.defineProperty(Vue.prototype, '$props', propsDef);

  Vue.prototype.$set = set;
  Vue.prototype.$delete = del;

  Vue.prototype.$watch = function (expOrFn, cb, options) {
    var vm = this;
    if (isPlainObject(cb)) {
      return createWatcher(vm, expOrFn, cb, options);
    }
    options = options || {};
    options.user = true;
    var watcher = new Watcher(vm, expOrFn, cb, options);
    if (options.immediate) {
      cb.call(vm, watcher.value);
    }
    return function unwatchFn() {
      watcher.teardown();
    };
  };
}

/*  */

function initProvide(vm) {
  var provide = vm.$options.provide;
  if (provide) {
    vm._provided = typeof provide === 'function' ? provide.call(vm) : provide;
  }
}

function initInjections(vm) {
  var result = resolveInject(vm.$options.inject, vm);
  if (result) {
    observerState.shouldConvert = false;
    Object.keys(result).forEach(function (key) {
      /* istanbul ignore else */
      if (process.env.NODE_ENV !== 'production') {
        defineReactive(vm, key, result[key], function () {
          warn("Avoid mutating an injected value directly since the changes will be " + "overwritten whenever the provided component re-renders. " + "injection being mutated: \"" + key + "\"", vm);
        });
      } else {
        defineReactive(vm, key, result[key]);
      }
    });
    observerState.shouldConvert = true;
  }
}

function resolveInject(inject, vm) {
  if (inject) {
    // inject is :any because flow is not smart enough to figure out cached
    var result = Object.create(null);
    var keys = hasSymbol ? Reflect.ownKeys(inject).filter(function (key) {
      /* istanbul ignore next */
      return Object.getOwnPropertyDescriptor(inject, key).enumerable;
    }) : Object.keys(inject);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      var provideKey = inject[key].from;
      var source = vm;
      while (source) {
        if (source._provided && provideKey in source._provided) {
          result[key] = source._provided[provideKey];
          break;
        }
        source = source.$parent;
      }
      if (!source) {
        if ('default' in inject[key]) {
          var provideDefault = inject[key].default;
          result[key] = typeof provideDefault === 'function' ? provideDefault.call(vm) : provideDefault;
        } else if (process.env.NODE_ENV !== 'production') {
          warn("Injection \"" + key + "\" not found", vm);
        }
      }
    }
    return result;
  }
}

/*  */

/**
 * Runtime helper for rendering v-for lists.
 */
function renderList(val, render) {
  var ret, i, l, keys, key;
  if (Array.isArray(val) || typeof val === 'string') {
    ret = new Array(val.length);
    for (i = 0, l = val.length; i < l; i++) {
      ret[i] = render(val[i], i);
    }
  } else if (typeof val === 'number') {
    ret = new Array(val);
    for (i = 0; i < val; i++) {
      ret[i] = render(i + 1, i);
    }
  } else if (isObject(val)) {
    keys = Object.keys(val);
    ret = new Array(keys.length);
    for (i = 0, l = keys.length; i < l; i++) {
      key = keys[i];
      ret[i] = render(val[key], key, i);
    }
  }
  if (isDef(ret)) {
    ret._isVList = true;
  }
  return ret;
}

/*  */

/**
 * Runtime helper for rendering <slot>
 */
function renderSlot(name, fallback, props, bindObject) {
  var scopedSlotFn = this.$scopedSlots[name];
  if (scopedSlotFn) {
    // scoped slot
    props = props || {};
    if (bindObject) {
      if (process.env.NODE_ENV !== 'production' && !isObject(bindObject)) {
        warn('slot v-bind without argument expects an Object', this);
      }
      props = extend(extend({}, bindObject), props);
    }
    return scopedSlotFn(props) || fallback;
  } else {
    var slotNodes = this.$slots[name];
    // warn duplicate slot usage
    if (slotNodes && process.env.NODE_ENV !== 'production') {
      slotNodes._rendered && warn("Duplicate presence of slot \"" + name + "\" found in the same render tree " + "- this will likely cause render errors.", this);
      slotNodes._rendered = true;
    }
    return slotNodes || fallback;
  }
}

/*  */

/**
 * Runtime helper for resolving filters
 */
function resolveFilter(id) {
  return resolveAsset(this.$options, 'filters', id, true) || identity;
}

/*  */

/**
 * Runtime helper for checking keyCodes from config.
 * exposed as Vue.prototype._k
 * passing in eventKeyName as last argument separately for backwards compat
 */
function checkKeyCodes(eventKeyCode, key, builtInAlias, eventKeyName) {
  var keyCodes = config.keyCodes[key] || builtInAlias;
  if (keyCodes) {
    if (Array.isArray(keyCodes)) {
      return keyCodes.indexOf(eventKeyCode) === -1;
    } else {
      return keyCodes !== eventKeyCode;
    }
  } else if (eventKeyName) {
    return hyphenate(eventKeyName) !== key;
  }
}

/*  */

/**
 * Runtime helper for merging v-bind="object" into a VNode's data.
 */
function bindObjectProps(data, tag, value, asProp, isSync) {
  if (value) {
    if (!isObject(value)) {
      process.env.NODE_ENV !== 'production' && warn('v-bind without argument expects an Object or Array value', this);
    } else {
      if (Array.isArray(value)) {
        value = toObject(value);
      }
      var hash;
      var loop = function loop(key) {
        if (key === 'class' || key === 'style' || isReservedAttribute(key)) {
          hash = data;
        } else {
          var type = data.attrs && data.attrs.type;
          hash = asProp || config.mustUseProp(tag, type, key) ? data.domProps || (data.domProps = {}) : data.attrs || (data.attrs = {});
        }
        if (!(key in hash)) {
          hash[key] = value[key];

          if (isSync) {
            var on = data.on || (data.on = {});
            on["update:" + key] = function ($event) {
              value[key] = $event;
            };
          }
        }
      };

      for (var key in value) {
        loop(key);
      }
    }
  }
  return data;
}

/*  */

/**
 * Runtime helper for rendering static trees.
 */
function renderStatic(index, isInFor) {
  // static trees can be rendered once and cached on the contructor options
  // so every instance shares the same cached trees
  var renderFns = this.$options.staticRenderFns;
  var cached = renderFns.cached || (renderFns.cached = []);
  var tree = cached[index];
  // if has already-rendered static tree and not inside v-for,
  // we can reuse the same tree by doing a shallow clone.
  if (tree && !isInFor) {
    return Array.isArray(tree) ? cloneVNodes(tree) : cloneVNode(tree);
  }
  // otherwise, render a fresh tree.
  tree = cached[index] = renderFns[index].call(this._renderProxy, null, this);
  markStatic(tree, "__static__" + index, false);
  return tree;
}

/**
 * Runtime helper for v-once.
 * Effectively it means marking the node as static with a unique key.
 */
function markOnce(tree, index, key) {
  markStatic(tree, "__once__" + index + (key ? "_" + key : ""), true);
  return tree;
}

function markStatic(tree, key, isOnce) {
  if (Array.isArray(tree)) {
    for (var i = 0; i < tree.length; i++) {
      if (tree[i] && typeof tree[i] !== 'string') {
        markStaticNode(tree[i], key + "_" + i, isOnce);
      }
    }
  } else {
    markStaticNode(tree, key, isOnce);
  }
}

function markStaticNode(node, key, isOnce) {
  node.isStatic = true;
  node.key = key;
  node.isOnce = isOnce;
}

/*  */

function bindObjectListeners(data, value) {
  if (value) {
    if (!isPlainObject(value)) {
      process.env.NODE_ENV !== 'production' && warn('v-on without argument expects an Object value', this);
    } else {
      var on = data.on = data.on ? extend({}, data.on) : {};
      for (var key in value) {
        var existing = on[key];
        var ours = value[key];
        on[key] = existing ? [].concat(existing, ours) : ours;
      }
    }
  }
  return data;
}

/*  */

function installRenderHelpers(target) {
  target._o = markOnce;
  target._n = toNumber;
  target._s = toString;
  target._l = renderList;
  target._t = renderSlot;
  target._q = looseEqual;
  target._i = looseIndexOf;
  target._m = renderStatic;
  target._f = resolveFilter;
  target._k = checkKeyCodes;
  target._b = bindObjectProps;
  target._v = createTextVNode;
  target._e = createEmptyVNode;
  target._u = resolveScopedSlots;
  target._g = bindObjectListeners;
}

/*  */

function FunctionalRenderContext(data, props, children, parent, Ctor) {
  var options = Ctor.options;
  this.data = data;
  this.props = props;
  this.children = children;
  this.parent = parent;
  this.listeners = data.on || emptyObject;
  this.injections = resolveInject(options.inject, parent);
  this.slots = function () {
    return resolveSlots(children, parent);
  };

  // ensure the createElement function in functional components
  // gets a unique context - this is necessary for correct named slot check
  var contextVm = Object.create(parent);
  var isCompiled = isTrue(options._compiled);
  var needNormalization = !isCompiled;

  // support for compiled functional template
  if (isCompiled) {
    // exposing $options for renderStatic()
    this.$options = options;
    // pre-resolve slots for renderSlot()
    this.$slots = this.slots();
    this.$scopedSlots = data.scopedSlots || emptyObject;
  }

  if (options._scopeId) {
    this._c = function (a, b, c, d) {
      var vnode = createElement(contextVm, a, b, c, d, needNormalization);
      if (vnode) {
        vnode.functionalScopeId = options._scopeId;
        vnode.functionalContext = parent;
      }
      return vnode;
    };
  } else {
    this._c = function (a, b, c, d) {
      return createElement(contextVm, a, b, c, d, needNormalization);
    };
  }
}

installRenderHelpers(FunctionalRenderContext.prototype);

function createFunctionalComponent(Ctor, propsData, data, contextVm, children) {
  var options = Ctor.options;
  var props = {};
  var propOptions = options.props;
  if (isDef(propOptions)) {
    for (var key in propOptions) {
      props[key] = validateProp(key, propOptions, propsData || emptyObject);
    }
  } else {
    if (isDef(data.attrs)) {
      mergeProps(props, data.attrs);
    }
    if (isDef(data.props)) {
      mergeProps(props, data.props);
    }
  }

  var renderContext = new FunctionalRenderContext(data, props, children, contextVm, Ctor);

  var vnode = options.render.call(null, renderContext._c, renderContext);

  if (vnode instanceof VNode) {
    vnode.functionalContext = contextVm;
    vnode.functionalOptions = options;
    if (data.slot) {
      (vnode.data || (vnode.data = {})).slot = data.slot;
    }
  }

  return vnode;
}

function mergeProps(to, from) {
  for (var key in from) {
    to[camelize(key)] = from[key];
  }
}

/*  */

// hooks to be invoked on component VNodes during patch
var componentVNodeHooks = {
  init: function init(vnode, hydrating, parentElm, refElm) {
    if (!vnode.componentInstance || vnode.componentInstance._isDestroyed) {
      var child = vnode.componentInstance = createComponentInstanceForVnode(vnode, activeInstance, parentElm, refElm);
      child.$mount(hydrating ? vnode.elm : undefined, hydrating);
    } else if (vnode.data.keepAlive) {
      // kept-alive components, treat as a patch
      var mountedNode = vnode; // work around flow
      componentVNodeHooks.prepatch(mountedNode, mountedNode);
    }
  },

  prepatch: function prepatch(oldVnode, vnode) {
    var options = vnode.componentOptions;
    var child = vnode.componentInstance = oldVnode.componentInstance;
    updateChildComponent(child, options.propsData, // updated props
    options.listeners, // updated listeners
    vnode, // new parent vnode
    options.children // new children
    );
  },

  insert: function insert(vnode) {
    var context = vnode.context;
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isMounted) {
      componentInstance._isMounted = true;
      callHook(componentInstance, 'mounted');
    }
    if (vnode.data.keepAlive) {
      if (context._isMounted) {
        // vue-router#1212
        // During updates, a kept-alive component's child components may
        // change, so directly walking the tree here may call activated hooks
        // on incorrect children. Instead we push them into a queue which will
        // be processed after the whole patch process ended.
        queueActivatedComponent(componentInstance);
      } else {
        activateChildComponent(componentInstance, true /* direct */);
      }
    }
  },

  destroy: function destroy(vnode) {
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isDestroyed) {
      if (!vnode.data.keepAlive) {
        componentInstance.$destroy();
      } else {
        deactivateChildComponent(componentInstance, true /* direct */);
      }
    }
  }
};

var hooksToMerge = Object.keys(componentVNodeHooks);

function createComponent(Ctor, data, context, children, tag) {
  if (isUndef(Ctor)) {
    return;
  }

  var baseCtor = context.$options._base;

  // plain options object: turn it into a constructor
  if (isObject(Ctor)) {
    Ctor = baseCtor.extend(Ctor);
  }

  // if at this stage it's not a constructor or an async component factory,
  // reject.
  if (typeof Ctor !== 'function') {
    if (process.env.NODE_ENV !== 'production') {
      warn("Invalid Component definition: " + String(Ctor), context);
    }
    return;
  }

  // async component
  var asyncFactory;
  if (isUndef(Ctor.cid)) {
    asyncFactory = Ctor;
    Ctor = resolveAsyncComponent(asyncFactory, baseCtor, context);
    if (Ctor === undefined) {
      // return a placeholder node for async component, which is rendered
      // as a comment node but preserves all the raw information for the node.
      // the information will be used for async server-rendering and hydration.
      return createAsyncPlaceholder(asyncFactory, data, context, children, tag);
    }
  }

  data = data || {};

  // resolve constructor options in case global mixins are applied after
  // component constructor creation
  resolveConstructorOptions(Ctor);

  // transform component v-model data into props & events
  if (isDef(data.model)) {
    transformModel(Ctor.options, data);
  }

  // extract props
  var propsData = extractPropsFromVNodeData(data, Ctor, tag);

  // functional component
  if (isTrue(Ctor.options.functional)) {
    return createFunctionalComponent(Ctor, propsData, data, context, children);
  }

  // extract listeners, since these needs to be treated as
  // child component listeners instead of DOM listeners
  var listeners = data.on;
  // replace with listeners with .native modifier
  // so it gets processed during parent component patch.
  data.on = data.nativeOn;

  if (isTrue(Ctor.options.abstract)) {
    // abstract components do not keep anything
    // other than props & listeners & slot

    // work around flow
    var slot = data.slot;
    data = {};
    if (slot) {
      data.slot = slot;
    }
  }

  // merge component management hooks onto the placeholder node
  mergeHooks(data);

  // return a placeholder vnode
  var name = Ctor.options.name || tag;
  var vnode = new VNode("vue-component-" + Ctor.cid + (name ? "-" + name : ''), data, undefined, undefined, undefined, context, { Ctor: Ctor, propsData: propsData, listeners: listeners, tag: tag, children: children }, asyncFactory);
  return vnode;
}

function createComponentInstanceForVnode(vnode, // we know it's MountedComponentVNode but flow doesn't
parent, // activeInstance in lifecycle state
parentElm, refElm) {
  var vnodeComponentOptions = vnode.componentOptions;
  var options = {
    _isComponent: true,
    parent: parent,
    propsData: vnodeComponentOptions.propsData,
    _componentTag: vnodeComponentOptions.tag,
    _parentVnode: vnode,
    _parentListeners: vnodeComponentOptions.listeners,
    _renderChildren: vnodeComponentOptions.children,
    _parentElm: parentElm || null,
    _refElm: refElm || null
  };
  // check inline-template render functions
  var inlineTemplate = vnode.data.inlineTemplate;
  if (isDef(inlineTemplate)) {
    options.render = inlineTemplate.render;
    options.staticRenderFns = inlineTemplate.staticRenderFns;
  }
  return new vnodeComponentOptions.Ctor(options);
}

function mergeHooks(data) {
  if (!data.hook) {
    data.hook = {};
  }
  for (var i = 0; i < hooksToMerge.length; i++) {
    var key = hooksToMerge[i];
    var fromParent = data.hook[key];
    var ours = componentVNodeHooks[key];
    data.hook[key] = fromParent ? mergeHook$1(ours, fromParent) : ours;
  }
}

function mergeHook$1(one, two) {
  return function (a, b, c, d) {
    one(a, b, c, d);
    two(a, b, c, d);
  };
}

// transform component v-model info (value and callback) into
// prop and event handler respectively.
function transformModel(options, data) {
  var prop = options.model && options.model.prop || 'value';
  var event = options.model && options.model.event || 'input';(data.props || (data.props = {}))[prop] = data.model.value;
  var on = data.on || (data.on = {});
  if (isDef(on[event])) {
    on[event] = [data.model.callback].concat(on[event]);
  } else {
    on[event] = data.model.callback;
  }
}

/*  */

var SIMPLE_NORMALIZE = 1;
var ALWAYS_NORMALIZE = 2;

// wrapper function for providing a more flexible interface
// without getting yelled at by flow
function createElement(context, tag, data, children, normalizationType, alwaysNormalize) {
  if (Array.isArray(data) || isPrimitive(data)) {
    normalizationType = children;
    children = data;
    data = undefined;
  }
  if (isTrue(alwaysNormalize)) {
    normalizationType = ALWAYS_NORMALIZE;
  }
  return _createElement(context, tag, data, children, normalizationType);
}

function _createElement(context, tag, data, children, normalizationType) {
  if (isDef(data) && isDef(data.__ob__)) {
    process.env.NODE_ENV !== 'production' && warn("Avoid using observed data object as vnode data: " + JSON.stringify(data) + "\n" + 'Always create fresh vnode data objects in each render!', context);
    return createEmptyVNode();
  }
  // object syntax in v-bind
  if (isDef(data) && isDef(data.is)) {
    tag = data.is;
  }
  if (!tag) {
    // in case of component :is set to falsy value
    return createEmptyVNode();
  }
  // warn against non-primitive key
  if (process.env.NODE_ENV !== 'production' && isDef(data) && isDef(data.key) && !isPrimitive(data.key)) {
    warn('Avoid using non-primitive value as key, ' + 'use string/number value instead.', context);
  }
  // support single function children as default scoped slot
  if (Array.isArray(children) && typeof children[0] === 'function') {
    data = data || {};
    data.scopedSlots = { default: children[0] };
    children.length = 0;
  }
  if (normalizationType === ALWAYS_NORMALIZE) {
    children = normalizeChildren(children);
  } else if (normalizationType === SIMPLE_NORMALIZE) {
    children = simpleNormalizeChildren(children);
  }
  var vnode, ns;
  if (typeof tag === 'string') {
    var Ctor;
    ns = context.$vnode && context.$vnode.ns || config.getTagNamespace(tag);
    if (config.isReservedTag(tag)) {
      // platform built-in elements
      vnode = new VNode(config.parsePlatformTagName(tag), data, children, undefined, undefined, context);
    } else if (isDef(Ctor = resolveAsset(context.$options, 'components', tag))) {
      // component
      vnode = createComponent(Ctor, data, context, children, tag);
    } else {
      // unknown or unlisted namespaced elements
      // check at runtime because it may get assigned a namespace when its
      // parent normalizes children
      vnode = new VNode(tag, data, children, undefined, undefined, context);
    }
  } else {
    // direct component options / constructor
    vnode = createComponent(tag, data, context, children);
  }
  if (isDef(vnode)) {
    if (ns) {
      applyNS(vnode, ns);
    }
    return vnode;
  } else {
    return createEmptyVNode();
  }
}

function applyNS(vnode, ns, force) {
  vnode.ns = ns;
  if (vnode.tag === 'foreignObject') {
    // use default namespace inside foreignObject
    ns = undefined;
    force = true;
  }
  if (isDef(vnode.children)) {
    for (var i = 0, l = vnode.children.length; i < l; i++) {
      var child = vnode.children[i];
      if (isDef(child.tag) && (isUndef(child.ns) || isTrue(force))) {
        applyNS(child, ns, force);
      }
    }
  }
}

/*  */

function initRender(vm) {
  vm._vnode = null; // the root of the child tree
  var options = vm.$options;
  var parentVnode = vm.$vnode = options._parentVnode; // the placeholder node in parent tree
  var renderContext = parentVnode && parentVnode.context;
  vm.$slots = resolveSlots(options._renderChildren, renderContext);
  vm.$scopedSlots = emptyObject;
  // bind the createElement fn to this instance
  // so that we get proper render context inside it.
  // args order: tag, data, children, normalizationType, alwaysNormalize
  // internal version is used by render functions compiled from templates
  vm._c = function (a, b, c, d) {
    return createElement(vm, a, b, c, d, false);
  };
  // normalization is always applied for the public version, used in
  // user-written render functions.
  vm.$createElement = function (a, b, c, d) {
    return createElement(vm, a, b, c, d, true);
  };

  // $attrs & $listeners are exposed for easier HOC creation.
  // they need to be reactive so that HOCs using them are always updated
  var parentData = parentVnode && parentVnode.data;

  /* istanbul ignore else */
  if (process.env.NODE_ENV !== 'production') {
    defineReactive(vm, '$attrs', parentData && parentData.attrs || emptyObject, function () {
      !isUpdatingChildComponent && warn("$attrs is readonly.", vm);
    }, true);
    defineReactive(vm, '$listeners', options._parentListeners || emptyObject, function () {
      !isUpdatingChildComponent && warn("$listeners is readonly.", vm);
    }, true);
  } else {
    defineReactive(vm, '$attrs', parentData && parentData.attrs || emptyObject, null, true);
    defineReactive(vm, '$listeners', options._parentListeners || emptyObject, null, true);
  }
}

function renderMixin(Vue) {
  // install runtime convenience helpers
  installRenderHelpers(Vue.prototype);

  Vue.prototype.$nextTick = function (fn) {
    return nextTick(fn, this);
  };

  Vue.prototype._render = function () {
    var vm = this;
    var ref = vm.$options;
    var render = ref.render;
    var _parentVnode = ref._parentVnode;

    if (vm._isMounted) {
      // if the parent didn't update, the slot nodes will be the ones from
      // last render. They need to be cloned to ensure "freshness" for this render.
      for (var key in vm.$slots) {
        var slot = vm.$slots[key];
        if (slot._rendered) {
          vm.$slots[key] = cloneVNodes(slot, true /* deep */);
        }
      }
    }

    vm.$scopedSlots = _parentVnode && _parentVnode.data.scopedSlots || emptyObject;

    // set parent vnode. this allows render functions to have access
    // to the data on the placeholder node.
    vm.$vnode = _parentVnode;
    // render self
    var vnode;
    try {
      vnode = render.call(vm._renderProxy, vm.$createElement);
    } catch (e) {
      handleError(e, vm, "render");
      // return error render result,
      // or previous vnode to prevent render error causing blank component
      /* istanbul ignore else */
      if (process.env.NODE_ENV !== 'production') {
        if (vm.$options.renderError) {
          try {
            vnode = vm.$options.renderError.call(vm._renderProxy, vm.$createElement, e);
          } catch (e) {
            handleError(e, vm, "renderError");
            vnode = vm._vnode;
          }
        } else {
          vnode = vm._vnode;
        }
      } else {
        vnode = vm._vnode;
      }
    }
    // return empty vnode in case the render function errored out
    if (!(vnode instanceof VNode)) {
      if (process.env.NODE_ENV !== 'production' && Array.isArray(vnode)) {
        warn('Multiple root nodes returned from render function. Render function ' + 'should return a single root node.', vm);
      }
      vnode = createEmptyVNode();
    }
    // set parent
    vnode.parent = _parentVnode;
    return vnode;
  };
}

/*  */

var uid = 0;

function initMixin(Vue) {
  Vue.prototype._init = function (options) {
    var vm = this;
    // a uid
    vm._uid = uid++;

    var startTag, endTag;
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
      startTag = "vue-perf-start:" + vm._uid;
      endTag = "vue-perf-end:" + vm._uid;
      mark(startTag);
    }

    // a flag to avoid this being observed
    vm._isVue = true;
    // merge options
    if (options && options._isComponent) {
      // optimize internal component instantiation
      // since dynamic options merging is pretty slow, and none of the
      // internal component options needs special treatment.
      initInternalComponent(vm, options);
    } else {
      vm.$options = mergeOptions(resolveConstructorOptions(vm.constructor), options || {}, vm);
    }
    /* istanbul ignore else */
    if (process.env.NODE_ENV !== 'production') {
      initProxy(vm);
    } else {
      vm._renderProxy = vm;
    }
    // expose real self
    vm._self = vm;
    initLifecycle(vm);
    initEvents(vm);
    initRender(vm);
    callHook(vm, 'beforeCreate');
    initInjections(vm); // resolve injections before data/props
    initState(vm);
    initProvide(vm); // resolve provide after data/props
    callHook(vm, 'created');

    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
      vm._name = formatComponentName(vm, false);
      mark(endTag);
      measure("vue " + vm._name + " init", startTag, endTag);
    }

    if (vm.$options.el) {
      vm.$mount(vm.$options.el);
    }
  };
}

function initInternalComponent(vm, options) {
  var opts = vm.$options = Object.create(vm.constructor.options);
  // doing this because it's faster than dynamic enumeration.
  opts.parent = options.parent;
  opts.propsData = options.propsData;
  opts._parentVnode = options._parentVnode;
  opts._parentListeners = options._parentListeners;
  opts._renderChildren = options._renderChildren;
  opts._componentTag = options._componentTag;
  opts._parentElm = options._parentElm;
  opts._refElm = options._refElm;
  if (options.render) {
    opts.render = options.render;
    opts.staticRenderFns = options.staticRenderFns;
  }
}

function resolveConstructorOptions(Ctor) {
  var options = Ctor.options;
  if (Ctor.super) {
    var superOptions = resolveConstructorOptions(Ctor.super);
    var cachedSuperOptions = Ctor.superOptions;
    if (superOptions !== cachedSuperOptions) {
      // super option changed,
      // need to resolve new options.
      Ctor.superOptions = superOptions;
      // check if there are any late-modified/attached options (#4976)
      var modifiedOptions = resolveModifiedOptions(Ctor);
      // update base extend options
      if (modifiedOptions) {
        extend(Ctor.extendOptions, modifiedOptions);
      }
      options = Ctor.options = mergeOptions(superOptions, Ctor.extendOptions);
      if (options.name) {
        options.components[options.name] = Ctor;
      }
    }
  }
  return options;
}

function resolveModifiedOptions(Ctor) {
  var modified;
  var latest = Ctor.options;
  var extended = Ctor.extendOptions;
  var sealed = Ctor.sealedOptions;
  for (var key in latest) {
    if (latest[key] !== sealed[key]) {
      if (!modified) {
        modified = {};
      }
      modified[key] = dedupe(latest[key], extended[key], sealed[key]);
    }
  }
  return modified;
}

function dedupe(latest, extended, sealed) {
  // compare latest and sealed to ensure lifecycle hooks won't be duplicated
  // between merges
  if (Array.isArray(latest)) {
    var res = [];
    sealed = Array.isArray(sealed) ? sealed : [sealed];
    extended = Array.isArray(extended) ? extended : [extended];
    for (var i = 0; i < latest.length; i++) {
      // push original options and not sealed options to exclude duplicated options
      if (extended.indexOf(latest[i]) >= 0 || sealed.indexOf(latest[i]) < 0) {
        res.push(latest[i]);
      }
    }
    return res;
  } else {
    return latest;
  }
}

function Vue$3(options) {
  if (process.env.NODE_ENV !== 'production' && !(this instanceof Vue$3)) {
    warn('Vue is a constructor and should be called with the `new` keyword');
  }
  this._init(options);
}

initMixin(Vue$3);
stateMixin(Vue$3);
eventsMixin(Vue$3);
lifecycleMixin(Vue$3);
renderMixin(Vue$3);

/*  */

function initUse(Vue) {
  Vue.use = function (plugin) {
    var installedPlugins = this._installedPlugins || (this._installedPlugins = []);
    if (installedPlugins.indexOf(plugin) > -1) {
      return this;
    }

    // additional parameters
    var args = toArray(arguments, 1);
    args.unshift(this);
    if (typeof plugin.install === 'function') {
      plugin.install.apply(plugin, args);
    } else if (typeof plugin === 'function') {
      plugin.apply(null, args);
    }
    installedPlugins.push(plugin);
    return this;
  };
}

/*  */

function initMixin$1(Vue) {
  Vue.mixin = function (mixin) {
    this.options = mergeOptions(this.options, mixin);
    return this;
  };
}

/*  */

function initExtend(Vue) {
  /**
   * Each instance constructor, including Vue, has a unique
   * cid. This enables us to create wrapped "child
   * constructors" for prototypal inheritance and cache them.
   */
  Vue.cid = 0;
  var cid = 1;

  /**
   * Class inheritance
   */
  Vue.extend = function (extendOptions) {
    extendOptions = extendOptions || {};
    var Super = this;
    var SuperId = Super.cid;
    var cachedCtors = extendOptions._Ctor || (extendOptions._Ctor = {});
    if (cachedCtors[SuperId]) {
      return cachedCtors[SuperId];
    }

    var name = extendOptions.name || Super.options.name;
    if (process.env.NODE_ENV !== 'production') {
      if (!/^[a-zA-Z][\w-]*$/.test(name)) {
        warn('Invalid component name: "' + name + '". Component names ' + 'can only contain alphanumeric characters and the hyphen, ' + 'and must start with a letter.');
      }
    }

    var Sub = function VueComponent(options) {
      this._init(options);
    };
    Sub.prototype = Object.create(Super.prototype);
    Sub.prototype.constructor = Sub;
    Sub.cid = cid++;
    Sub.options = mergeOptions(Super.options, extendOptions);
    Sub['super'] = Super;

    // For props and computed properties, we define the proxy getters on
    // the Vue instances at extension time, on the extended prototype. This
    // avoids Object.defineProperty calls for each instance created.
    if (Sub.options.props) {
      initProps$1(Sub);
    }
    if (Sub.options.computed) {
      initComputed$1(Sub);
    }

    // allow further extension/mixin/plugin usage
    Sub.extend = Super.extend;
    Sub.mixin = Super.mixin;
    Sub.use = Super.use;

    // create asset registers, so extended classes
    // can have their private assets too.
    ASSET_TYPES.forEach(function (type) {
      Sub[type] = Super[type];
    });
    // enable recursive self-lookup
    if (name) {
      Sub.options.components[name] = Sub;
    }

    // keep a reference to the super options at extension time.
    // later at instantiation we can check if Super's options have
    // been updated.
    Sub.superOptions = Super.options;
    Sub.extendOptions = extendOptions;
    Sub.sealedOptions = extend({}, Sub.options);

    // cache constructor
    cachedCtors[SuperId] = Sub;
    return Sub;
  };
}

function initProps$1(Comp) {
  var props = Comp.options.props;
  for (var key in props) {
    proxy(Comp.prototype, "_props", key);
  }
}

function initComputed$1(Comp) {
  var computed = Comp.options.computed;
  for (var key in computed) {
    defineComputed(Comp.prototype, key, computed[key]);
  }
}

/*  */

function initAssetRegisters(Vue) {
  /**
   * Create asset registration methods.
   */
  ASSET_TYPES.forEach(function (type) {
    Vue[type] = function (id, definition) {
      if (!definition) {
        return this.options[type + 's'][id];
      } else {
        /* istanbul ignore if */
        if (process.env.NODE_ENV !== 'production') {
          if (type === 'component' && config.isReservedTag(id)) {
            warn('Do not use built-in or reserved HTML elements as component ' + 'id: ' + id);
          }
        }
        if (type === 'component' && isPlainObject(definition)) {
          definition.name = definition.name || id;
          definition = this.options._base.extend(definition);
        }
        if (type === 'directive' && typeof definition === 'function') {
          definition = { bind: definition, update: definition };
        }
        this.options[type + 's'][id] = definition;
        return definition;
      }
    };
  });
}

/*  */

function getComponentName(opts) {
  return opts && (opts.Ctor.options.name || opts.tag);
}

function matches(pattern, name) {
  if (Array.isArray(pattern)) {
    return pattern.indexOf(name) > -1;
  } else if (typeof pattern === 'string') {
    return pattern.split(',').indexOf(name) > -1;
  } else if (isRegExp(pattern)) {
    return pattern.test(name);
  }
  /* istanbul ignore next */
  return false;
}

function pruneCache(keepAliveInstance, filter) {
  var cache = keepAliveInstance.cache;
  var keys = keepAliveInstance.keys;
  var _vnode = keepAliveInstance._vnode;
  for (var key in cache) {
    var cachedNode = cache[key];
    if (cachedNode) {
      var name = getComponentName(cachedNode.componentOptions);
      if (name && !filter(name)) {
        pruneCacheEntry(cache, key, keys, _vnode);
      }
    }
  }
}

function pruneCacheEntry(cache, key, keys, current) {
  var cached$$1 = cache[key];
  if (cached$$1 && cached$$1 !== current) {
    cached$$1.componentInstance.$destroy();
  }
  cache[key] = null;
  remove(keys, key);
}

var patternTypes = [String, RegExp, Array];

var KeepAlive = {
  name: 'keep-alive',
  abstract: true,

  props: {
    include: patternTypes,
    exclude: patternTypes,
    max: [String, Number]
  },

  created: function created() {
    this.cache = Object.create(null);
    this.keys = [];
  },

  destroyed: function destroyed() {
    var this$1 = this;

    for (var key in this$1.cache) {
      pruneCacheEntry(this$1.cache, key, this$1.keys);
    }
  },

  watch: {
    include: function include(val) {
      pruneCache(this, function (name) {
        return matches(val, name);
      });
    },
    exclude: function exclude(val) {
      pruneCache(this, function (name) {
        return !matches(val, name);
      });
    }
  },

  render: function render() {
    var vnode = getFirstComponentChild(this.$slots.default);
    var componentOptions = vnode && vnode.componentOptions;
    if (componentOptions) {
      // check pattern
      var name = getComponentName(componentOptions);
      if (name && (this.include && !matches(this.include, name) || this.exclude && matches(this.exclude, name))) {
        return vnode;
      }

      var ref = this;
      var cache = ref.cache;
      var keys = ref.keys;
      var key = vnode.key == null
      // same constructor may get registered as different local components
      // so cid alone is not enough (#3269)
      ? componentOptions.Ctor.cid + (componentOptions.tag ? "::" + componentOptions.tag : '') : vnode.key;
      if (cache[key]) {
        vnode.componentInstance = cache[key].componentInstance;
        // make current key freshest
        remove(keys, key);
        keys.push(key);
      } else {
        cache[key] = vnode;
        keys.push(key);
        // prune oldest entry
        if (this.max && keys.length > parseInt(this.max)) {
          pruneCacheEntry(cache, keys[0], keys, this._vnode);
        }
      }

      vnode.data.keepAlive = true;
    }
    return vnode;
  }
};

var builtInComponents = {
  KeepAlive: KeepAlive
};

/*  */

function initGlobalAPI(Vue) {
  // config
  var configDef = {};
  configDef.get = function () {
    return config;
  };
  if (process.env.NODE_ENV !== 'production') {
    configDef.set = function () {
      warn('Do not replace the Vue.config object, set individual fields instead.');
    };
  }
  Object.defineProperty(Vue, 'config', configDef);

  // exposed util methods.
  // NOTE: these are not considered part of the public API - avoid relying on
  // them unless you are aware of the risk.
  Vue.util = {
    warn: warn,
    extend: extend,
    mergeOptions: mergeOptions,
    defineReactive: defineReactive
  };

  Vue.set = set;
  Vue.delete = del;
  Vue.nextTick = nextTick;

  Vue.options = Object.create(null);
  ASSET_TYPES.forEach(function (type) {
    Vue.options[type + 's'] = Object.create(null);
  });

  // this is used to identify the "base" constructor to extend all plain-object
  // components with in Weex's multi-instance scenarios.
  Vue.options._base = Vue;

  extend(Vue.options.components, builtInComponents);

  initUse(Vue);
  initMixin$1(Vue);
  initExtend(Vue);
  initAssetRegisters(Vue);
}

initGlobalAPI(Vue$3);

Object.defineProperty(Vue$3.prototype, '$isServer', {
  get: isServerRendering
});

Object.defineProperty(Vue$3.prototype, '$ssrContext', {
  get: function get() {
    /* istanbul ignore next */
    return this.$vnode && this.$vnode.ssrContext;
  }
});

Vue$3.version = '2.5.2';

/*  */

// these are reserved for web because they are directly compiled away
// during template compilation
var isReservedAttr = makeMap('style,class');

// attributes that should be using props for binding
var acceptValue = makeMap('input,textarea,option,select,progress');
var mustUseProp = function mustUseProp(tag, type, attr) {
  return attr === 'value' && acceptValue(tag) && type !== 'button' || attr === 'selected' && tag === 'option' || attr === 'checked' && tag === 'input' || attr === 'muted' && tag === 'video';
};

var isEnumeratedAttr = makeMap('contenteditable,draggable,spellcheck');

var isBooleanAttr = makeMap('allowfullscreen,async,autofocus,autoplay,checked,compact,controls,declare,' + 'default,defaultchecked,defaultmuted,defaultselected,defer,disabled,' + 'enabled,formnovalidate,hidden,indeterminate,inert,ismap,itemscope,loop,multiple,' + 'muted,nohref,noresize,noshade,novalidate,nowrap,open,pauseonexit,readonly,' + 'required,reversed,scoped,seamless,selected,sortable,translate,' + 'truespeed,typemustmatch,visible');

var xlinkNS = 'http://www.w3.org/1999/xlink';

var isXlink = function isXlink(name) {
  return name.charAt(5) === ':' && name.slice(0, 5) === 'xlink';
};

var getXlinkProp = function getXlinkProp(name) {
  return isXlink(name) ? name.slice(6, name.length) : '';
};

var isFalsyAttrValue = function isFalsyAttrValue(val) {
  return val == null || val === false;
};

/*  */

function genClassForVnode(vnode) {
  var data = vnode.data;
  var parentNode = vnode;
  var childNode = vnode;
  while (isDef(childNode.componentInstance)) {
    childNode = childNode.componentInstance._vnode;
    if (childNode.data) {
      data = mergeClassData(childNode.data, data);
    }
  }
  while (isDef(parentNode = parentNode.parent)) {
    if (parentNode.data) {
      data = mergeClassData(data, parentNode.data);
    }
  }
  return renderClass(data.staticClass, data.class);
}

function mergeClassData(child, parent) {
  return {
    staticClass: concat(child.staticClass, parent.staticClass),
    class: isDef(child.class) ? [child.class, parent.class] : parent.class
  };
}

function renderClass(staticClass, dynamicClass) {
  if (isDef(staticClass) || isDef(dynamicClass)) {
    return concat(staticClass, stringifyClass(dynamicClass));
  }
  /* istanbul ignore next */
  return '';
}

function concat(a, b) {
  return a ? b ? a + ' ' + b : a : b || '';
}

function stringifyClass(value) {
  if (Array.isArray(value)) {
    return stringifyArray(value);
  }
  if (isObject(value)) {
    return stringifyObject(value);
  }
  if (typeof value === 'string') {
    return value;
  }
  /* istanbul ignore next */
  return '';
}

function stringifyArray(value) {
  var res = '';
  var stringified;
  for (var i = 0, l = value.length; i < l; i++) {
    if (isDef(stringified = stringifyClass(value[i])) && stringified !== '') {
      if (res) {
        res += ' ';
      }
      res += stringified;
    }
  }
  return res;
}

function stringifyObject(value) {
  var res = '';
  for (var key in value) {
    if (value[key]) {
      if (res) {
        res += ' ';
      }
      res += key;
    }
  }
  return res;
}

/*  */

var namespaceMap = {
  svg: 'http://www.w3.org/2000/svg',
  math: 'http://www.w3.org/1998/Math/MathML'
};

var isHTMLTag = makeMap('html,body,base,head,link,meta,style,title,' + 'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' + 'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,' + 'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' + 's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' + 'embed,object,param,source,canvas,script,noscript,del,ins,' + 'caption,col,colgroup,table,thead,tbody,td,th,tr,' + 'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' + 'output,progress,select,textarea,' + 'details,dialog,menu,menuitem,summary,' + 'content,element,shadow,template,blockquote,iframe,tfoot');

// this map is intentionally selective, only covering SVG elements that may
// contain child elements.
var isSVG = makeMap('svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' + 'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' + 'polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view', true);

var isReservedTag = function isReservedTag(tag) {
  return isHTMLTag(tag) || isSVG(tag);
};

function getTagNamespace(tag) {
  if (isSVG(tag)) {
    return 'svg';
  }
  // basic support for MathML
  // note it doesn't support other MathML elements being component roots
  if (tag === 'math') {
    return 'math';
  }
}

var unknownElementCache = Object.create(null);
function isUnknownElement(tag) {
  /* istanbul ignore if */
  if (!inBrowser) {
    return true;
  }
  if (isReservedTag(tag)) {
    return false;
  }
  tag = tag.toLowerCase();
  /* istanbul ignore if */
  if (unknownElementCache[tag] != null) {
    return unknownElementCache[tag];
  }
  var el = document.createElement(tag);
  if (tag.indexOf('-') > -1) {
    // http://stackoverflow.com/a/28210364/1070244
    return unknownElementCache[tag] = el.constructor === window.HTMLUnknownElement || el.constructor === window.HTMLElement;
  } else {
    return unknownElementCache[tag] = /HTMLUnknownElement/.test(el.toString());
  }
}

var isTextInputType = makeMap('text,number,password,search,email,tel,url');

/*  */

/**
 * Query an element selector if it's not an element already.
 */
function query(el) {
  if (typeof el === 'string') {
    var selected = document.querySelector(el);
    if (!selected) {
      process.env.NODE_ENV !== 'production' && warn('Cannot find element: ' + el);
      return document.createElement('div');
    }
    return selected;
  } else {
    return el;
  }
}

/*  */

function createElement$1(tagName, vnode) {
  var elm = document.createElement(tagName);
  if (tagName !== 'select') {
    return elm;
  }
  // false or null will remove the attribute but undefined will not
  if (vnode.data && vnode.data.attrs && vnode.data.attrs.multiple !== undefined) {
    elm.setAttribute('multiple', 'multiple');
  }
  return elm;
}

function createElementNS(namespace, tagName) {
  return document.createElementNS(namespaceMap[namespace], tagName);
}

function createTextNode(text) {
  return document.createTextNode(text);
}

function createComment(text) {
  return document.createComment(text);
}

function insertBefore(parentNode, newNode, referenceNode) {
  parentNode.insertBefore(newNode, referenceNode);
}

function removeChild(node, child) {
  node.removeChild(child);
}

function appendChild(node, child) {
  node.appendChild(child);
}

function parentNode(node) {
  return node.parentNode;
}

function nextSibling(node) {
  return node.nextSibling;
}

function tagName(node) {
  return node.tagName;
}

function setTextContent(node, text) {
  node.textContent = text;
}

function setAttribute(node, key, val) {
  node.setAttribute(key, val);
}

var nodeOps = Object.freeze({
  createElement: createElement$1,
  createElementNS: createElementNS,
  createTextNode: createTextNode,
  createComment: createComment,
  insertBefore: insertBefore,
  removeChild: removeChild,
  appendChild: appendChild,
  parentNode: parentNode,
  nextSibling: nextSibling,
  tagName: tagName,
  setTextContent: setTextContent,
  setAttribute: setAttribute
});

/*  */

var ref = {
  create: function create(_, vnode) {
    registerRef(vnode);
  },
  update: function update(oldVnode, vnode) {
    if (oldVnode.data.ref !== vnode.data.ref) {
      registerRef(oldVnode, true);
      registerRef(vnode);
    }
  },
  destroy: function destroy(vnode) {
    registerRef(vnode, true);
  }
};

function registerRef(vnode, isRemoval) {
  var key = vnode.data.ref;
  if (!key) {
    return;
  }

  var vm = vnode.context;
  var ref = vnode.componentInstance || vnode.elm;
  var refs = vm.$refs;
  if (isRemoval) {
    if (Array.isArray(refs[key])) {
      remove(refs[key], ref);
    } else if (refs[key] === ref) {
      refs[key] = undefined;
    }
  } else {
    if (vnode.data.refInFor) {
      if (!Array.isArray(refs[key])) {
        refs[key] = [ref];
      } else if (refs[key].indexOf(ref) < 0) {
        // $flow-disable-line
        refs[key].push(ref);
      }
    } else {
      refs[key] = ref;
    }
  }
}

/**
 * Virtual DOM patching algorithm based on Snabbdom by
 * Simon Friis Vindum (@paldepind)
 * Licensed under the MIT License
 * https://github.com/paldepind/snabbdom/blob/master/LICENSE
 *
 * modified by Evan You (@yyx990803)
 *
 * Not type-checking this because this file is perf-critical and the cost
 * of making flow understand it is not worth it.
 */

var emptyNode = new VNode('', {}, []);

var hooks = ['create', 'activate', 'update', 'remove', 'destroy'];

function sameVnode(a, b) {
  return a.key === b.key && (a.tag === b.tag && a.isComment === b.isComment && isDef(a.data) === isDef(b.data) && sameInputType(a, b) || isTrue(a.isAsyncPlaceholder) && a.asyncFactory === b.asyncFactory && isUndef(b.asyncFactory.error));
}

function sameInputType(a, b) {
  if (a.tag !== 'input') {
    return true;
  }
  var i;
  var typeA = isDef(i = a.data) && isDef(i = i.attrs) && i.type;
  var typeB = isDef(i = b.data) && isDef(i = i.attrs) && i.type;
  return typeA === typeB || isTextInputType(typeA) && isTextInputType(typeB);
}

function createKeyToOldIdx(children, beginIdx, endIdx) {
  var i, key;
  var map = {};
  for (i = beginIdx; i <= endIdx; ++i) {
    key = children[i].key;
    if (isDef(key)) {
      map[key] = i;
    }
  }
  return map;
}

function createPatchFunction(backend) {
  var i, j;
  var cbs = {};

  var modules = backend.modules;
  var nodeOps = backend.nodeOps;

  for (i = 0; i < hooks.length; ++i) {
    cbs[hooks[i]] = [];
    for (j = 0; j < modules.length; ++j) {
      if (isDef(modules[j][hooks[i]])) {
        cbs[hooks[i]].push(modules[j][hooks[i]]);
      }
    }
  }

  function emptyNodeAt(elm) {
    return new VNode(nodeOps.tagName(elm).toLowerCase(), {}, [], undefined, elm);
  }

  function createRmCb(childElm, listeners) {
    function remove() {
      if (--remove.listeners === 0) {
        removeNode(childElm);
      }
    }
    remove.listeners = listeners;
    return remove;
  }

  function removeNode(el) {
    var parent = nodeOps.parentNode(el);
    // element may have already been removed due to v-html / v-text
    if (isDef(parent)) {
      nodeOps.removeChild(parent, el);
    }
  }

  var inPre = 0;
  function createElm(vnode, insertedVnodeQueue, parentElm, refElm, nested) {
    vnode.isRootInsert = !nested; // for transition enter check
    if (createComponent(vnode, insertedVnodeQueue, parentElm, refElm)) {
      return;
    }

    var data = vnode.data;
    var children = vnode.children;
    var tag = vnode.tag;
    if (isDef(tag)) {
      if (process.env.NODE_ENV !== 'production') {
        if (data && data.pre) {
          inPre++;
        }
        if (!inPre && !vnode.ns && !(config.ignoredElements.length && config.ignoredElements.some(function (ignore) {
          return isRegExp(ignore) ? ignore.test(tag) : ignore === tag;
        })) && config.isUnknownElement(tag)) {
          warn('Unknown custom element: <' + tag + '> - did you ' + 'register the component correctly? For recursive components, ' + 'make sure to provide the "name" option.', vnode.context);
        }
      }
      vnode.elm = vnode.ns ? nodeOps.createElementNS(vnode.ns, tag) : nodeOps.createElement(tag, vnode);
      setScope(vnode);

      /* istanbul ignore if */
      {
        createChildren(vnode, children, insertedVnodeQueue);
        if (isDef(data)) {
          invokeCreateHooks(vnode, insertedVnodeQueue);
        }
        insert(parentElm, vnode.elm, refElm);
      }

      if (process.env.NODE_ENV !== 'production' && data && data.pre) {
        inPre--;
      }
    } else if (isTrue(vnode.isComment)) {
      vnode.elm = nodeOps.createComment(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    } else {
      vnode.elm = nodeOps.createTextNode(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    }
  }

  function createComponent(vnode, insertedVnodeQueue, parentElm, refElm) {
    var i = vnode.data;
    if (isDef(i)) {
      var isReactivated = isDef(vnode.componentInstance) && i.keepAlive;
      if (isDef(i = i.hook) && isDef(i = i.init)) {
        i(vnode, false /* hydrating */, parentElm, refElm);
      }
      // after calling the init hook, if the vnode is a child component
      // it should've created a child instance and mounted it. the child
      // component also has set the placeholder vnode's elm.
      // in that case we can just return the element and be done.
      if (isDef(vnode.componentInstance)) {
        initComponent(vnode, insertedVnodeQueue);
        if (isTrue(isReactivated)) {
          reactivateComponent(vnode, insertedVnodeQueue, parentElm, refElm);
        }
        return true;
      }
    }
  }

  function initComponent(vnode, insertedVnodeQueue) {
    if (isDef(vnode.data.pendingInsert)) {
      insertedVnodeQueue.push.apply(insertedVnodeQueue, vnode.data.pendingInsert);
      vnode.data.pendingInsert = null;
    }
    vnode.elm = vnode.componentInstance.$el;
    if (isPatchable(vnode)) {
      invokeCreateHooks(vnode, insertedVnodeQueue);
      setScope(vnode);
    } else {
      // empty component root.
      // skip all element-related modules except for ref (#3455)
      registerRef(vnode);
      // make sure to invoke the insert hook
      insertedVnodeQueue.push(vnode);
    }
  }

  function reactivateComponent(vnode, insertedVnodeQueue, parentElm, refElm) {
    var i;
    // hack for #4339: a reactivated component with inner transition
    // does not trigger because the inner node's created hooks are not called
    // again. It's not ideal to involve module-specific logic in here but
    // there doesn't seem to be a better way to do it.
    var innerNode = vnode;
    while (innerNode.componentInstance) {
      innerNode = innerNode.componentInstance._vnode;
      if (isDef(i = innerNode.data) && isDef(i = i.transition)) {
        for (i = 0; i < cbs.activate.length; ++i) {
          cbs.activate[i](emptyNode, innerNode);
        }
        insertedVnodeQueue.push(innerNode);
        break;
      }
    }
    // unlike a newly created component,
    // a reactivated keep-alive component doesn't insert itself
    insert(parentElm, vnode.elm, refElm);
  }

  function insert(parent, elm, ref$$1) {
    if (isDef(parent)) {
      if (isDef(ref$$1)) {
        if (ref$$1.parentNode === parent) {
          nodeOps.insertBefore(parent, elm, ref$$1);
        }
      } else {
        nodeOps.appendChild(parent, elm);
      }
    }
  }

  function createChildren(vnode, children, insertedVnodeQueue) {
    if (Array.isArray(children)) {
      for (var i = 0; i < children.length; ++i) {
        createElm(children[i], insertedVnodeQueue, vnode.elm, null, true);
      }
    } else if (isPrimitive(vnode.text)) {
      nodeOps.appendChild(vnode.elm, nodeOps.createTextNode(vnode.text));
    }
  }

  function isPatchable(vnode) {
    while (vnode.componentInstance) {
      vnode = vnode.componentInstance._vnode;
    }
    return isDef(vnode.tag);
  }

  function invokeCreateHooks(vnode, insertedVnodeQueue) {
    for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
      cbs.create[i$1](emptyNode, vnode);
    }
    i = vnode.data.hook; // Reuse variable
    if (isDef(i)) {
      if (isDef(i.create)) {
        i.create(emptyNode, vnode);
      }
      if (isDef(i.insert)) {
        insertedVnodeQueue.push(vnode);
      }
    }
  }

  // set scope id attribute for scoped CSS.
  // this is implemented as a special case to avoid the overhead
  // of going through the normal attribute patching process.
  function setScope(vnode) {
    var i;
    if (isDef(i = vnode.functionalScopeId)) {
      nodeOps.setAttribute(vnode.elm, i, '');
    } else {
      var ancestor = vnode;
      while (ancestor) {
        if (isDef(i = ancestor.context) && isDef(i = i.$options._scopeId)) {
          nodeOps.setAttribute(vnode.elm, i, '');
        }
        ancestor = ancestor.parent;
      }
    }
    // for slot content they should also get the scopeId from the host instance.
    if (isDef(i = activeInstance) && i !== vnode.context && i !== vnode.functionalContext && isDef(i = i.$options._scopeId)) {
      nodeOps.setAttribute(vnode.elm, i, '');
    }
  }

  function addVnodes(parentElm, refElm, vnodes, startIdx, endIdx, insertedVnodeQueue) {
    for (; startIdx <= endIdx; ++startIdx) {
      createElm(vnodes[startIdx], insertedVnodeQueue, parentElm, refElm);
    }
  }

  function invokeDestroyHook(vnode) {
    var i, j;
    var data = vnode.data;
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.destroy)) {
        i(vnode);
      }
      for (i = 0; i < cbs.destroy.length; ++i) {
        cbs.destroy[i](vnode);
      }
    }
    if (isDef(i = vnode.children)) {
      for (j = 0; j < vnode.children.length; ++j) {
        invokeDestroyHook(vnode.children[j]);
      }
    }
  }

  function removeVnodes(parentElm, vnodes, startIdx, endIdx) {
    for (; startIdx <= endIdx; ++startIdx) {
      var ch = vnodes[startIdx];
      if (isDef(ch)) {
        if (isDef(ch.tag)) {
          removeAndInvokeRemoveHook(ch);
          invokeDestroyHook(ch);
        } else {
          // Text node
          removeNode(ch.elm);
        }
      }
    }
  }

  function removeAndInvokeRemoveHook(vnode, rm) {
    if (isDef(rm) || isDef(vnode.data)) {
      var i;
      var listeners = cbs.remove.length + 1;
      if (isDef(rm)) {
        // we have a recursively passed down rm callback
        // increase the listeners count
        rm.listeners += listeners;
      } else {
        // directly removing
        rm = createRmCb(vnode.elm, listeners);
      }
      // recursively invoke hooks on child component root node
      if (isDef(i = vnode.componentInstance) && isDef(i = i._vnode) && isDef(i.data)) {
        removeAndInvokeRemoveHook(i, rm);
      }
      for (i = 0; i < cbs.remove.length; ++i) {
        cbs.remove[i](vnode, rm);
      }
      if (isDef(i = vnode.data.hook) && isDef(i = i.remove)) {
        i(vnode, rm);
      } else {
        rm();
      }
    } else {
      removeNode(vnode.elm);
    }
  }

  function updateChildren(parentElm, oldCh, newCh, insertedVnodeQueue, removeOnly) {
    var oldStartIdx = 0;
    var newStartIdx = 0;
    var oldEndIdx = oldCh.length - 1;
    var oldStartVnode = oldCh[0];
    var oldEndVnode = oldCh[oldEndIdx];
    var newEndIdx = newCh.length - 1;
    var newStartVnode = newCh[0];
    var newEndVnode = newCh[newEndIdx];
    var oldKeyToIdx, idxInOld, vnodeToMove, refElm;

    // removeOnly is a special flag used only by <transition-group>
    // to ensure removed elements stay in correct relative positions
    // during leaving transitions
    var canMove = !removeOnly;

    while (oldStartIdx <= oldEndIdx && newStartIdx <= newEndIdx) {
      if (isUndef(oldStartVnode)) {
        oldStartVnode = oldCh[++oldStartIdx]; // Vnode has been moved left
      } else if (isUndef(oldEndVnode)) {
        oldEndVnode = oldCh[--oldEndIdx];
      } else if (sameVnode(oldStartVnode, newStartVnode)) {
        patchVnode(oldStartVnode, newStartVnode, insertedVnodeQueue);
        oldStartVnode = oldCh[++oldStartIdx];
        newStartVnode = newCh[++newStartIdx];
      } else if (sameVnode(oldEndVnode, newEndVnode)) {
        patchVnode(oldEndVnode, newEndVnode, insertedVnodeQueue);
        oldEndVnode = oldCh[--oldEndIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldStartVnode, newEndVnode)) {
        // Vnode moved right
        patchVnode(oldStartVnode, newEndVnode, insertedVnodeQueue);
        canMove && nodeOps.insertBefore(parentElm, oldStartVnode.elm, nodeOps.nextSibling(oldEndVnode.elm));
        oldStartVnode = oldCh[++oldStartIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldEndVnode, newStartVnode)) {
        // Vnode moved left
        patchVnode(oldEndVnode, newStartVnode, insertedVnodeQueue);
        canMove && nodeOps.insertBefore(parentElm, oldEndVnode.elm, oldStartVnode.elm);
        oldEndVnode = oldCh[--oldEndIdx];
        newStartVnode = newCh[++newStartIdx];
      } else {
        if (isUndef(oldKeyToIdx)) {
          oldKeyToIdx = createKeyToOldIdx(oldCh, oldStartIdx, oldEndIdx);
        }
        idxInOld = isDef(newStartVnode.key) ? oldKeyToIdx[newStartVnode.key] : findIdxInOld(newStartVnode, oldCh, oldStartIdx, oldEndIdx);
        if (isUndef(idxInOld)) {
          // New element
          createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm);
        } else {
          vnodeToMove = oldCh[idxInOld];
          /* istanbul ignore if */
          if (process.env.NODE_ENV !== 'production' && !vnodeToMove) {
            warn('It seems there are duplicate keys that is causing an update error. ' + 'Make sure each v-for item has a unique key.');
          }
          if (sameVnode(vnodeToMove, newStartVnode)) {
            patchVnode(vnodeToMove, newStartVnode, insertedVnodeQueue);
            oldCh[idxInOld] = undefined;
            canMove && nodeOps.insertBefore(parentElm, vnodeToMove.elm, oldStartVnode.elm);
          } else {
            // same key but different element. treat as new element
            createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm);
          }
        }
        newStartVnode = newCh[++newStartIdx];
      }
    }
    if (oldStartIdx > oldEndIdx) {
      refElm = isUndef(newCh[newEndIdx + 1]) ? null : newCh[newEndIdx + 1].elm;
      addVnodes(parentElm, refElm, newCh, newStartIdx, newEndIdx, insertedVnodeQueue);
    } else if (newStartIdx > newEndIdx) {
      removeVnodes(parentElm, oldCh, oldStartIdx, oldEndIdx);
    }
  }

  function findIdxInOld(node, oldCh, start, end) {
    for (var i = start; i < end; i++) {
      var c = oldCh[i];
      if (isDef(c) && sameVnode(node, c)) {
        return i;
      }
    }
  }

  function patchVnode(oldVnode, vnode, insertedVnodeQueue, removeOnly) {
    if (oldVnode === vnode) {
      return;
    }

    var elm = vnode.elm = oldVnode.elm;

    if (isTrue(oldVnode.isAsyncPlaceholder)) {
      if (isDef(vnode.asyncFactory.resolved)) {
        hydrate(oldVnode.elm, vnode, insertedVnodeQueue);
      } else {
        vnode.isAsyncPlaceholder = true;
      }
      return;
    }

    // reuse element for static trees.
    // note we only do this if the vnode is cloned -
    // if the new node is not cloned it means the render functions have been
    // reset by the hot-reload-api and we need to do a proper re-render.
    if (isTrue(vnode.isStatic) && isTrue(oldVnode.isStatic) && vnode.key === oldVnode.key && (isTrue(vnode.isCloned) || isTrue(vnode.isOnce))) {
      vnode.componentInstance = oldVnode.componentInstance;
      return;
    }

    var i;
    var data = vnode.data;
    if (isDef(data) && isDef(i = data.hook) && isDef(i = i.prepatch)) {
      i(oldVnode, vnode);
    }

    var oldCh = oldVnode.children;
    var ch = vnode.children;
    if (isDef(data) && isPatchable(vnode)) {
      for (i = 0; i < cbs.update.length; ++i) {
        cbs.update[i](oldVnode, vnode);
      }
      if (isDef(i = data.hook) && isDef(i = i.update)) {
        i(oldVnode, vnode);
      }
    }
    if (isUndef(vnode.text)) {
      if (isDef(oldCh) && isDef(ch)) {
        if (oldCh !== ch) {
          updateChildren(elm, oldCh, ch, insertedVnodeQueue, removeOnly);
        }
      } else if (isDef(ch)) {
        if (isDef(oldVnode.text)) {
          nodeOps.setTextContent(elm, '');
        }
        addVnodes(elm, null, ch, 0, ch.length - 1, insertedVnodeQueue);
      } else if (isDef(oldCh)) {
        removeVnodes(elm, oldCh, 0, oldCh.length - 1);
      } else if (isDef(oldVnode.text)) {
        nodeOps.setTextContent(elm, '');
      }
    } else if (oldVnode.text !== vnode.text) {
      nodeOps.setTextContent(elm, vnode.text);
    }
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.postpatch)) {
        i(oldVnode, vnode);
      }
    }
  }

  function invokeInsertHook(vnode, queue, initial) {
    // delay insert hooks for component root nodes, invoke them after the
    // element is really inserted
    if (isTrue(initial) && isDef(vnode.parent)) {
      vnode.parent.data.pendingInsert = queue;
    } else {
      for (var i = 0; i < queue.length; ++i) {
        queue[i].data.hook.insert(queue[i]);
      }
    }
  }

  var bailed = false;
  // list of modules that can skip create hook during hydration because they
  // are already rendered on the client or has no need for initialization
  var isRenderedModule = makeMap('attrs,style,class,staticClass,staticStyle,key');

  // Note: this is a browser-only function so we can assume elms are DOM nodes.
  function hydrate(elm, vnode, insertedVnodeQueue) {
    if (isTrue(vnode.isComment) && isDef(vnode.asyncFactory)) {
      vnode.elm = elm;
      vnode.isAsyncPlaceholder = true;
      return true;
    }
    if (process.env.NODE_ENV !== 'production') {
      if (!assertNodeMatch(elm, vnode)) {
        return false;
      }
    }
    vnode.elm = elm;
    var tag = vnode.tag;
    var data = vnode.data;
    var children = vnode.children;
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.init)) {
        i(vnode, true /* hydrating */);
      }
      if (isDef(i = vnode.componentInstance)) {
        // child component. it should have hydrated its own tree.
        initComponent(vnode, insertedVnodeQueue);
        return true;
      }
    }
    if (isDef(tag)) {
      if (isDef(children)) {
        // empty element, allow client to pick up and populate children
        if (!elm.hasChildNodes()) {
          createChildren(vnode, children, insertedVnodeQueue);
        } else {
          // v-html and domProps: innerHTML
          if (isDef(i = data) && isDef(i = i.domProps) && isDef(i = i.innerHTML)) {
            if (i !== elm.innerHTML) {
              /* istanbul ignore if */
              if (process.env.NODE_ENV !== 'production' && typeof console !== 'undefined' && !bailed) {
                bailed = true;
                console.warn('Parent: ', elm);
                console.warn('server innerHTML: ', i);
                console.warn('client innerHTML: ', elm.innerHTML);
              }
              return false;
            }
          } else {
            // iterate and compare children lists
            var childrenMatch = true;
            var childNode = elm.firstChild;
            for (var i$1 = 0; i$1 < children.length; i$1++) {
              if (!childNode || !hydrate(childNode, children[i$1], insertedVnodeQueue)) {
                childrenMatch = false;
                break;
              }
              childNode = childNode.nextSibling;
            }
            // if childNode is not null, it means the actual childNodes list is
            // longer than the virtual children list.
            if (!childrenMatch || childNode) {
              /* istanbul ignore if */
              if (process.env.NODE_ENV !== 'production' && typeof console !== 'undefined' && !bailed) {
                bailed = true;
                console.warn('Parent: ', elm);
                console.warn('Mismatching childNodes vs. VNodes: ', elm.childNodes, children);
              }
              return false;
            }
          }
        }
      }
      if (isDef(data)) {
        for (var key in data) {
          if (!isRenderedModule(key)) {
            invokeCreateHooks(vnode, insertedVnodeQueue);
            break;
          }
        }
      }
    } else if (elm.data !== vnode.text) {
      elm.data = vnode.text;
    }
    return true;
  }

  function assertNodeMatch(node, vnode) {
    if (isDef(vnode.tag)) {
      return vnode.tag.indexOf('vue-component') === 0 || vnode.tag.toLowerCase() === (node.tagName && node.tagName.toLowerCase());
    } else {
      return node.nodeType === (vnode.isComment ? 8 : 3);
    }
  }

  return function patch(oldVnode, vnode, hydrating, removeOnly, parentElm, refElm) {
    if (isUndef(vnode)) {
      if (isDef(oldVnode)) {
        invokeDestroyHook(oldVnode);
      }
      return;
    }

    var isInitialPatch = false;
    var insertedVnodeQueue = [];

    if (isUndef(oldVnode)) {
      // empty mount (likely as component), create new root element
      isInitialPatch = true;
      createElm(vnode, insertedVnodeQueue, parentElm, refElm);
    } else {
      var isRealElement = isDef(oldVnode.nodeType);
      if (!isRealElement && sameVnode(oldVnode, vnode)) {
        // patch existing root node
        patchVnode(oldVnode, vnode, insertedVnodeQueue, removeOnly);
      } else {
        if (isRealElement) {
          // mounting to a real element
          // check if this is server-rendered content and if we can perform
          // a successful hydration.
          if (oldVnode.nodeType === 1 && oldVnode.hasAttribute(SSR_ATTR)) {
            oldVnode.removeAttribute(SSR_ATTR);
            hydrating = true;
          }
          if (isTrue(hydrating)) {
            if (hydrate(oldVnode, vnode, insertedVnodeQueue)) {
              invokeInsertHook(vnode, insertedVnodeQueue, true);
              return oldVnode;
            } else if (process.env.NODE_ENV !== 'production') {
              warn('The client-side rendered virtual DOM tree is not matching ' + 'server-rendered content. This is likely caused by incorrect ' + 'HTML markup, for example nesting block-level elements inside ' + '<p>, or missing <tbody>. Bailing hydration and performing ' + 'full client-side render.');
            }
          }
          // either not server-rendered, or hydration failed.
          // create an empty node and replace it
          oldVnode = emptyNodeAt(oldVnode);
        }
        // replacing existing element
        var oldElm = oldVnode.elm;
        var parentElm$1 = nodeOps.parentNode(oldElm);
        createElm(vnode, insertedVnodeQueue,
        // extremely rare edge case: do not insert if old element is in a
        // leaving transition. Only happens when combining transition +
        // keep-alive + HOCs. (#4590)
        oldElm._leaveCb ? null : parentElm$1, nodeOps.nextSibling(oldElm));

        if (isDef(vnode.parent)) {
          // component root element replaced.
          // update parent placeholder node element, recursively
          var ancestor = vnode.parent;
          var patchable = isPatchable(vnode);
          while (ancestor) {
            for (var i = 0; i < cbs.destroy.length; ++i) {
              cbs.destroy[i](ancestor);
            }
            ancestor.elm = vnode.elm;
            if (patchable) {
              for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
                cbs.create[i$1](emptyNode, ancestor);
              }
              // #6513
              // invoke insert hooks that may have been merged by create hooks.
              // e.g. for directives that uses the "inserted" hook.
              var insert = ancestor.data.hook.insert;
              if (insert.merged) {
                // start at index 1 to avoid re-invoking component mounted hook
                for (var i$2 = 1; i$2 < insert.fns.length; i$2++) {
                  insert.fns[i$2]();
                }
              }
            } else {
              registerRef(ancestor);
            }
            ancestor = ancestor.parent;
          }
        }

        if (isDef(parentElm$1)) {
          removeVnodes(parentElm$1, [oldVnode], 0, 0);
        } else if (isDef(oldVnode.tag)) {
          invokeDestroyHook(oldVnode);
        }
      }
    }

    invokeInsertHook(vnode, insertedVnodeQueue, isInitialPatch);
    return vnode.elm;
  };
}

/*  */

var directives = {
  create: updateDirectives,
  update: updateDirectives,
  destroy: function unbindDirectives(vnode) {
    updateDirectives(vnode, emptyNode);
  }
};

function updateDirectives(oldVnode, vnode) {
  if (oldVnode.data.directives || vnode.data.directives) {
    _update(oldVnode, vnode);
  }
}

function _update(oldVnode, vnode) {
  var isCreate = oldVnode === emptyNode;
  var isDestroy = vnode === emptyNode;
  var oldDirs = normalizeDirectives$1(oldVnode.data.directives, oldVnode.context);
  var newDirs = normalizeDirectives$1(vnode.data.directives, vnode.context);

  var dirsWithInsert = [];
  var dirsWithPostpatch = [];

  var key, oldDir, dir;
  for (key in newDirs) {
    oldDir = oldDirs[key];
    dir = newDirs[key];
    if (!oldDir) {
      // new directive, bind
      callHook$1(dir, 'bind', vnode, oldVnode);
      if (dir.def && dir.def.inserted) {
        dirsWithInsert.push(dir);
      }
    } else {
      // existing directive, update
      dir.oldValue = oldDir.value;
      callHook$1(dir, 'update', vnode, oldVnode);
      if (dir.def && dir.def.componentUpdated) {
        dirsWithPostpatch.push(dir);
      }
    }
  }

  if (dirsWithInsert.length) {
    var callInsert = function callInsert() {
      for (var i = 0; i < dirsWithInsert.length; i++) {
        callHook$1(dirsWithInsert[i], 'inserted', vnode, oldVnode);
      }
    };
    if (isCreate) {
      mergeVNodeHook(vnode.data.hook || (vnode.data.hook = {}), 'insert', callInsert);
    } else {
      callInsert();
    }
  }

  if (dirsWithPostpatch.length) {
    mergeVNodeHook(vnode.data.hook || (vnode.data.hook = {}), 'postpatch', function () {
      for (var i = 0; i < dirsWithPostpatch.length; i++) {
        callHook$1(dirsWithPostpatch[i], 'componentUpdated', vnode, oldVnode);
      }
    });
  }

  if (!isCreate) {
    for (key in oldDirs) {
      if (!newDirs[key]) {
        // no longer present, unbind
        callHook$1(oldDirs[key], 'unbind', oldVnode, oldVnode, isDestroy);
      }
    }
  }
}

var emptyModifiers = Object.create(null);

function normalizeDirectives$1(dirs, vm) {
  var res = Object.create(null);
  if (!dirs) {
    return res;
  }
  var i, dir;
  for (i = 0; i < dirs.length; i++) {
    dir = dirs[i];
    if (!dir.modifiers) {
      dir.modifiers = emptyModifiers;
    }
    res[getRawDirName(dir)] = dir;
    dir.def = resolveAsset(vm.$options, 'directives', dir.name, true);
  }
  return res;
}

function getRawDirName(dir) {
  return dir.rawName || dir.name + "." + Object.keys(dir.modifiers || {}).join('.');
}

function callHook$1(dir, hook, vnode, oldVnode, isDestroy) {
  var fn = dir.def && dir.def[hook];
  if (fn) {
    try {
      fn(vnode.elm, dir, vnode, oldVnode, isDestroy);
    } catch (e) {
      handleError(e, vnode.context, "directive " + dir.name + " " + hook + " hook");
    }
  }
}

var baseModules = [ref, directives];

/*  */

function updateAttrs(oldVnode, vnode) {
  var opts = vnode.componentOptions;
  if (isDef(opts) && opts.Ctor.options.inheritAttrs === false) {
    return;
  }
  if (isUndef(oldVnode.data.attrs) && isUndef(vnode.data.attrs)) {
    return;
  }
  var key, cur, old;
  var elm = vnode.elm;
  var oldAttrs = oldVnode.data.attrs || {};
  var attrs = vnode.data.attrs || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(attrs.__ob__)) {
    attrs = vnode.data.attrs = extend({}, attrs);
  }

  for (key in attrs) {
    cur = attrs[key];
    old = oldAttrs[key];
    if (old !== cur) {
      setAttr(elm, key, cur);
    }
  }
  // #4391: in IE9, setting type can reset value for input[type=radio]
  // #6666: IE/Edge forces progress value down to 1 before setting a max
  /* istanbul ignore if */
  if ((isIE9 || isEdge) && attrs.value !== oldAttrs.value) {
    setAttr(elm, 'value', attrs.value);
  }
  for (key in oldAttrs) {
    if (isUndef(attrs[key])) {
      if (isXlink(key)) {
        elm.removeAttributeNS(xlinkNS, getXlinkProp(key));
      } else if (!isEnumeratedAttr(key)) {
        elm.removeAttribute(key);
      }
    }
  }
}

function setAttr(el, key, value) {
  if (isBooleanAttr(key)) {
    // set attribute for blank value
    // e.g. <option disabled>Select one</option>
    if (isFalsyAttrValue(value)) {
      el.removeAttribute(key);
    } else {
      // technically allowfullscreen is a boolean attribute for <iframe>,
      // but Flash expects a value of "true" when used on <embed> tag
      value = key === 'allowfullscreen' && el.tagName === 'EMBED' ? 'true' : key;
      el.setAttribute(key, value);
    }
  } else if (isEnumeratedAttr(key)) {
    el.setAttribute(key, isFalsyAttrValue(value) || value === 'false' ? 'false' : 'true');
  } else if (isXlink(key)) {
    if (isFalsyAttrValue(value)) {
      el.removeAttributeNS(xlinkNS, getXlinkProp(key));
    } else {
      el.setAttributeNS(xlinkNS, key, value);
    }
  } else {
    if (isFalsyAttrValue(value)) {
      el.removeAttribute(key);
    } else {
      el.setAttribute(key, value);
    }
  }
}

var attrs = {
  create: updateAttrs,
  update: updateAttrs
};

/*  */

function updateClass(oldVnode, vnode) {
  var el = vnode.elm;
  var data = vnode.data;
  var oldData = oldVnode.data;
  if (isUndef(data.staticClass) && isUndef(data.class) && (isUndef(oldData) || isUndef(oldData.staticClass) && isUndef(oldData.class))) {
    return;
  }

  var cls = genClassForVnode(vnode);

  // handle transition classes
  var transitionClass = el._transitionClasses;
  if (isDef(transitionClass)) {
    cls = concat(cls, stringifyClass(transitionClass));
  }

  // set the class
  if (cls !== el._prevClass) {
    el.setAttribute('class', cls);
    el._prevClass = cls;
  }
}

var klass = {
  create: updateClass,
  update: updateClass
};

/*  */

/*  */

// note: this only removes the attr from the Array (attrsList) so that it
// doesn't get processed by processAttrs.
// By default it does NOT remove it from the map (attrsMap) because the map is
// needed during codegen.

/*  */

/**
 * Cross-platform code generation for component v-model
 */

/**
 * Cross-platform codegen helper for generating v-model value assignment code.
 */

/*  */

// in some cases, the event used has to be determined at runtime
// so we used some reserved tokens during compile.
var RANGE_TOKEN = '__r';
var CHECKBOX_RADIO_TOKEN = '__c';

/*  */

// normalize v-model event tokens that can only be determined at runtime.
// it's important to place the event as the first in the array because
// the whole point is ensuring the v-model callback gets called before
// user-attached handlers.
function normalizeEvents(on) {
  /* istanbul ignore if */
  if (isDef(on[RANGE_TOKEN])) {
    // IE input[type=range] only supports `change` event
    var event = isIE ? 'change' : 'input';
    on[event] = [].concat(on[RANGE_TOKEN], on[event] || []);
    delete on[RANGE_TOKEN];
  }
  // This was originally intended to fix #4521 but no longer necessary
  // after 2.5. Keeping it for backwards compat with generated code from < 2.4
  /* istanbul ignore if */
  if (isDef(on[CHECKBOX_RADIO_TOKEN])) {
    on.change = [].concat(on[CHECKBOX_RADIO_TOKEN], on.change || []);
    delete on[CHECKBOX_RADIO_TOKEN];
  }
}

var target$1;

function createOnceHandler(handler, event, capture) {
  var _target = target$1; // save current target element in closure
  return function onceHandler() {
    var res = handler.apply(null, arguments);
    if (res !== null) {
      remove$2(event, onceHandler, capture, _target);
    }
  };
}

function add$1(event, handler, once$$1, capture, passive) {
  handler = withMacroTask(handler);
  if (once$$1) {
    handler = createOnceHandler(handler, event, capture);
  }
  target$1.addEventListener(event, handler, supportsPassive ? { capture: capture, passive: passive } : capture);
}

function remove$2(event, handler, capture, _target) {
  (_target || target$1).removeEventListener(event, handler._withTask || handler, capture);
}

function updateDOMListeners(oldVnode, vnode) {
  if (isUndef(oldVnode.data.on) && isUndef(vnode.data.on)) {
    return;
  }
  var on = vnode.data.on || {};
  var oldOn = oldVnode.data.on || {};
  target$1 = vnode.elm;
  normalizeEvents(on);
  updateListeners(on, oldOn, add$1, remove$2, vnode.context);
}

var events = {
  create: updateDOMListeners,
  update: updateDOMListeners
};

/*  */

function updateDOMProps(oldVnode, vnode) {
  if (isUndef(oldVnode.data.domProps) && isUndef(vnode.data.domProps)) {
    return;
  }
  var key, cur;
  var elm = vnode.elm;
  var oldProps = oldVnode.data.domProps || {};
  var props = vnode.data.domProps || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(props.__ob__)) {
    props = vnode.data.domProps = extend({}, props);
  }

  for (key in oldProps) {
    if (isUndef(props[key])) {
      elm[key] = '';
    }
  }
  for (key in props) {
    cur = props[key];
    // ignore children if the node has textContent or innerHTML,
    // as these will throw away existing DOM nodes and cause removal errors
    // on subsequent patches (#3360)
    if (key === 'textContent' || key === 'innerHTML') {
      if (vnode.children) {
        vnode.children.length = 0;
      }
      if (cur === oldProps[key]) {
        continue;
      }
      // #6601 work around Chrome version <= 55 bug where single textNode
      // replaced by innerHTML/textContent retains its parentNode property
      if (elm.childNodes.length === 1) {
        elm.removeChild(elm.childNodes[0]);
      }
    }

    if (key === 'value') {
      // store value as _value as well since
      // non-string values will be stringified
      elm._value = cur;
      // avoid resetting cursor position when value is the same
      var strCur = isUndef(cur) ? '' : String(cur);
      if (shouldUpdateValue(elm, strCur)) {
        elm.value = strCur;
      }
    } else {
      elm[key] = cur;
    }
  }
}

// check platforms/web/util/attrs.js acceptValue


function shouldUpdateValue(elm, checkVal) {
  return !elm.composing && (elm.tagName === 'OPTION' || isDirty(elm, checkVal) || isInputChanged(elm, checkVal));
}

function isDirty(elm, checkVal) {
  // return true when textbox (.number and .trim) loses focus and its value is
  // not equal to the updated value
  var notInFocus = true;
  // #6157
  // work around IE bug when accessing document.activeElement in an iframe
  try {
    notInFocus = document.activeElement !== elm;
  } catch (e) {}
  return notInFocus && elm.value !== checkVal;
}

function isInputChanged(elm, newVal) {
  var value = elm.value;
  var modifiers = elm._vModifiers; // injected by v-model runtime
  if (isDef(modifiers) && modifiers.number) {
    return toNumber(value) !== toNumber(newVal);
  }
  if (isDef(modifiers) && modifiers.trim) {
    return value.trim() !== newVal.trim();
  }
  return value !== newVal;
}

var domProps = {
  create: updateDOMProps,
  update: updateDOMProps
};

/*  */

var parseStyleText = cached(function (cssText) {
  var res = {};
  var listDelimiter = /;(?![^(]*\))/g;
  var propertyDelimiter = /:(.+)/;
  cssText.split(listDelimiter).forEach(function (item) {
    if (item) {
      var tmp = item.split(propertyDelimiter);
      tmp.length > 1 && (res[tmp[0].trim()] = tmp[1].trim());
    }
  });
  return res;
});

// merge static and dynamic style data on the same vnode
function normalizeStyleData(data) {
  var style = normalizeStyleBinding(data.style);
  // static style is pre-processed into an object during compilation
  // and is always a fresh object, so it's safe to merge into it
  return data.staticStyle ? extend(data.staticStyle, style) : style;
}

// normalize possible array / string values into Object
function normalizeStyleBinding(bindingStyle) {
  if (Array.isArray(bindingStyle)) {
    return toObject(bindingStyle);
  }
  if (typeof bindingStyle === 'string') {
    return parseStyleText(bindingStyle);
  }
  return bindingStyle;
}

/**
 * parent component style should be after child's
 * so that parent component's style could override it
 */
function getStyle(vnode, checkChild) {
  var res = {};
  var styleData;

  if (checkChild) {
    var childNode = vnode;
    while (childNode.componentInstance) {
      childNode = childNode.componentInstance._vnode;
      if (childNode.data && (styleData = normalizeStyleData(childNode.data))) {
        extend(res, styleData);
      }
    }
  }

  if (styleData = normalizeStyleData(vnode.data)) {
    extend(res, styleData);
  }

  var parentNode = vnode;
  while (parentNode = parentNode.parent) {
    if (parentNode.data && (styleData = normalizeStyleData(parentNode.data))) {
      extend(res, styleData);
    }
  }
  return res;
}

/*  */

var cssVarRE = /^--/;
var importantRE = /\s*!important$/;
var setProp = function setProp(el, name, val) {
  /* istanbul ignore if */
  if (cssVarRE.test(name)) {
    el.style.setProperty(name, val);
  } else if (importantRE.test(val)) {
    el.style.setProperty(name, val.replace(importantRE, ''), 'important');
  } else {
    var normalizedName = normalize(name);
    if (Array.isArray(val)) {
      // Support values array created by autoprefixer, e.g.
      // {display: ["-webkit-box", "-ms-flexbox", "flex"]}
      // Set them one by one, and the browser will only set those it can recognize
      for (var i = 0, len = val.length; i < len; i++) {
        el.style[normalizedName] = val[i];
      }
    } else {
      el.style[normalizedName] = val;
    }
  }
};

var vendorNames = ['Webkit', 'Moz', 'ms'];

var emptyStyle;
var normalize = cached(function (prop) {
  emptyStyle = emptyStyle || document.createElement('div').style;
  prop = camelize(prop);
  if (prop !== 'filter' && prop in emptyStyle) {
    return prop;
  }
  var capName = prop.charAt(0).toUpperCase() + prop.slice(1);
  for (var i = 0; i < vendorNames.length; i++) {
    var name = vendorNames[i] + capName;
    if (name in emptyStyle) {
      return name;
    }
  }
});

function updateStyle(oldVnode, vnode) {
  var data = vnode.data;
  var oldData = oldVnode.data;

  if (isUndef(data.staticStyle) && isUndef(data.style) && isUndef(oldData.staticStyle) && isUndef(oldData.style)) {
    return;
  }

  var cur, name;
  var el = vnode.elm;
  var oldStaticStyle = oldData.staticStyle;
  var oldStyleBinding = oldData.normalizedStyle || oldData.style || {};

  // if static style exists, stylebinding already merged into it when doing normalizeStyleData
  var oldStyle = oldStaticStyle || oldStyleBinding;

  var style = normalizeStyleBinding(vnode.data.style) || {};

  // store normalized style under a different key for next diff
  // make sure to clone it if it's reactive, since the user likely wants
  // to mutate it.
  vnode.data.normalizedStyle = isDef(style.__ob__) ? extend({}, style) : style;

  var newStyle = getStyle(vnode, true);

  for (name in oldStyle) {
    if (isUndef(newStyle[name])) {
      setProp(el, name, '');
    }
  }
  for (name in newStyle) {
    cur = newStyle[name];
    if (cur !== oldStyle[name]) {
      // ie9 setting to null has no effect, must use empty string
      setProp(el, name, cur == null ? '' : cur);
    }
  }
}

var style = {
  create: updateStyle,
  update: updateStyle
};

/*  */

/**
 * Add class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function addClass(el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return;
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(/\s+/).forEach(function (c) {
        return el.classList.add(c);
      });
    } else {
      el.classList.add(cls);
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    if (cur.indexOf(' ' + cls + ' ') < 0) {
      el.setAttribute('class', (cur + cls).trim());
    }
  }
}

/**
 * Remove class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function removeClass(el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return;
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(/\s+/).forEach(function (c) {
        return el.classList.remove(c);
      });
    } else {
      el.classList.remove(cls);
    }
    if (!el.classList.length) {
      el.removeAttribute('class');
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    var tar = ' ' + cls + ' ';
    while (cur.indexOf(tar) >= 0) {
      cur = cur.replace(tar, ' ');
    }
    cur = cur.trim();
    if (cur) {
      el.setAttribute('class', cur);
    } else {
      el.removeAttribute('class');
    }
  }
}

/*  */

function resolveTransition(def) {
  if (!def) {
    return;
  }
  /* istanbul ignore else */
  if ((typeof def === 'undefined' ? 'undefined' : _typeof(def)) === 'object') {
    var res = {};
    if (def.css !== false) {
      extend(res, autoCssTransition(def.name || 'v'));
    }
    extend(res, def);
    return res;
  } else if (typeof def === 'string') {
    return autoCssTransition(def);
  }
}

var autoCssTransition = cached(function (name) {
  return {
    enterClass: name + "-enter",
    enterToClass: name + "-enter-to",
    enterActiveClass: name + "-enter-active",
    leaveClass: name + "-leave",
    leaveToClass: name + "-leave-to",
    leaveActiveClass: name + "-leave-active"
  };
});

var hasTransition = inBrowser && !isIE9;
var TRANSITION = 'transition';
var ANIMATION = 'animation';

// Transition property/event sniffing
var transitionProp = 'transition';
var transitionEndEvent = 'transitionend';
var animationProp = 'animation';
var animationEndEvent = 'animationend';
if (hasTransition) {
  /* istanbul ignore if */
  if (window.ontransitionend === undefined && window.onwebkittransitionend !== undefined) {
    transitionProp = 'WebkitTransition';
    transitionEndEvent = 'webkitTransitionEnd';
  }
  if (window.onanimationend === undefined && window.onwebkitanimationend !== undefined) {
    animationProp = 'WebkitAnimation';
    animationEndEvent = 'webkitAnimationEnd';
  }
}

// binding to window is necessary to make hot reload work in IE in strict mode
var raf = inBrowser ? window.requestAnimationFrame ? window.requestAnimationFrame.bind(window) : setTimeout : /* istanbul ignore next */function (fn) {
  return fn();
};

function nextFrame(fn) {
  raf(function () {
    raf(fn);
  });
}

function addTransitionClass(el, cls) {
  var transitionClasses = el._transitionClasses || (el._transitionClasses = []);
  if (transitionClasses.indexOf(cls) < 0) {
    transitionClasses.push(cls);
    addClass(el, cls);
  }
}

function removeTransitionClass(el, cls) {
  if (el._transitionClasses) {
    remove(el._transitionClasses, cls);
  }
  removeClass(el, cls);
}

function whenTransitionEnds(el, expectedType, cb) {
  var ref = getTransitionInfo(el, expectedType);
  var type = ref.type;
  var timeout = ref.timeout;
  var propCount = ref.propCount;
  if (!type) {
    return cb();
  }
  var event = type === TRANSITION ? transitionEndEvent : animationEndEvent;
  var ended = 0;
  var end = function end() {
    el.removeEventListener(event, onEnd);
    cb();
  };
  var onEnd = function onEnd(e) {
    if (e.target === el) {
      if (++ended >= propCount) {
        end();
      }
    }
  };
  setTimeout(function () {
    if (ended < propCount) {
      end();
    }
  }, timeout + 1);
  el.addEventListener(event, onEnd);
}

var transformRE = /\b(transform|all)(,|$)/;

function getTransitionInfo(el, expectedType) {
  var styles = window.getComputedStyle(el);
  var transitionDelays = styles[transitionProp + 'Delay'].split(', ');
  var transitionDurations = styles[transitionProp + 'Duration'].split(', ');
  var transitionTimeout = getTimeout(transitionDelays, transitionDurations);
  var animationDelays = styles[animationProp + 'Delay'].split(', ');
  var animationDurations = styles[animationProp + 'Duration'].split(', ');
  var animationTimeout = getTimeout(animationDelays, animationDurations);

  var type;
  var timeout = 0;
  var propCount = 0;
  /* istanbul ignore if */
  if (expectedType === TRANSITION) {
    if (transitionTimeout > 0) {
      type = TRANSITION;
      timeout = transitionTimeout;
      propCount = transitionDurations.length;
    }
  } else if (expectedType === ANIMATION) {
    if (animationTimeout > 0) {
      type = ANIMATION;
      timeout = animationTimeout;
      propCount = animationDurations.length;
    }
  } else {
    timeout = Math.max(transitionTimeout, animationTimeout);
    type = timeout > 0 ? transitionTimeout > animationTimeout ? TRANSITION : ANIMATION : null;
    propCount = type ? type === TRANSITION ? transitionDurations.length : animationDurations.length : 0;
  }
  var hasTransform = type === TRANSITION && transformRE.test(styles[transitionProp + 'Property']);
  return {
    type: type,
    timeout: timeout,
    propCount: propCount,
    hasTransform: hasTransform
  };
}

function getTimeout(delays, durations) {
  /* istanbul ignore next */
  while (delays.length < durations.length) {
    delays = delays.concat(delays);
  }

  return Math.max.apply(null, durations.map(function (d, i) {
    return toMs(d) + toMs(delays[i]);
  }));
}

function toMs(s) {
  return Number(s.slice(0, -1)) * 1000;
}

/*  */

function enter(vnode, toggleDisplay) {
  var el = vnode.elm;

  // call leave callback now
  if (isDef(el._leaveCb)) {
    el._leaveCb.cancelled = true;
    el._leaveCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data)) {
    return;
  }

  /* istanbul ignore if */
  if (isDef(el._enterCb) || el.nodeType !== 1) {
    return;
  }

  var css = data.css;
  var type = data.type;
  var enterClass = data.enterClass;
  var enterToClass = data.enterToClass;
  var enterActiveClass = data.enterActiveClass;
  var appearClass = data.appearClass;
  var appearToClass = data.appearToClass;
  var appearActiveClass = data.appearActiveClass;
  var beforeEnter = data.beforeEnter;
  var enter = data.enter;
  var afterEnter = data.afterEnter;
  var enterCancelled = data.enterCancelled;
  var beforeAppear = data.beforeAppear;
  var appear = data.appear;
  var afterAppear = data.afterAppear;
  var appearCancelled = data.appearCancelled;
  var duration = data.duration;

  // activeInstance will always be the <transition> component managing this
  // transition. One edge case to check is when the <transition> is placed
  // as the root node of a child component. In that case we need to check
  // <transition>'s parent for appear check.
  var context = activeInstance;
  var transitionNode = activeInstance.$vnode;
  while (transitionNode && transitionNode.parent) {
    transitionNode = transitionNode.parent;
    context = transitionNode.context;
  }

  var isAppear = !context._isMounted || !vnode.isRootInsert;

  if (isAppear && !appear && appear !== '') {
    return;
  }

  var startClass = isAppear && appearClass ? appearClass : enterClass;
  var activeClass = isAppear && appearActiveClass ? appearActiveClass : enterActiveClass;
  var toClass = isAppear && appearToClass ? appearToClass : enterToClass;

  var beforeEnterHook = isAppear ? beforeAppear || beforeEnter : beforeEnter;
  var enterHook = isAppear ? typeof appear === 'function' ? appear : enter : enter;
  var afterEnterHook = isAppear ? afterAppear || afterEnter : afterEnter;
  var enterCancelledHook = isAppear ? appearCancelled || enterCancelled : enterCancelled;

  var explicitEnterDuration = toNumber(isObject(duration) ? duration.enter : duration);

  if (process.env.NODE_ENV !== 'production' && explicitEnterDuration != null) {
    checkDuration(explicitEnterDuration, 'enter', vnode);
  }

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(enterHook);

  var cb = el._enterCb = once(function () {
    if (expectsCSS) {
      removeTransitionClass(el, toClass);
      removeTransitionClass(el, activeClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, startClass);
      }
      enterCancelledHook && enterCancelledHook(el);
    } else {
      afterEnterHook && afterEnterHook(el);
    }
    el._enterCb = null;
  });

  if (!vnode.data.show) {
    // remove pending leave element on enter by injecting an insert hook
    mergeVNodeHook(vnode.data.hook || (vnode.data.hook = {}), 'insert', function () {
      var parent = el.parentNode;
      var pendingNode = parent && parent._pending && parent._pending[vnode.key];
      if (pendingNode && pendingNode.tag === vnode.tag && pendingNode.elm._leaveCb) {
        pendingNode.elm._leaveCb();
      }
      enterHook && enterHook(el, cb);
    });
  }

  // start enter transition
  beforeEnterHook && beforeEnterHook(el);
  if (expectsCSS) {
    addTransitionClass(el, startClass);
    addTransitionClass(el, activeClass);
    nextFrame(function () {
      addTransitionClass(el, toClass);
      removeTransitionClass(el, startClass);
      if (!cb.cancelled && !userWantsControl) {
        if (isValidDuration(explicitEnterDuration)) {
          setTimeout(cb, explicitEnterDuration);
        } else {
          whenTransitionEnds(el, type, cb);
        }
      }
    });
  }

  if (vnode.data.show) {
    toggleDisplay && toggleDisplay();
    enterHook && enterHook(el, cb);
  }

  if (!expectsCSS && !userWantsControl) {
    cb();
  }
}

function leave(vnode, rm) {
  var el = vnode.elm;

  // call enter callback now
  if (isDef(el._enterCb)) {
    el._enterCb.cancelled = true;
    el._enterCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data)) {
    return rm();
  }

  /* istanbul ignore if */
  if (isDef(el._leaveCb) || el.nodeType !== 1) {
    return;
  }

  var css = data.css;
  var type = data.type;
  var leaveClass = data.leaveClass;
  var leaveToClass = data.leaveToClass;
  var leaveActiveClass = data.leaveActiveClass;
  var beforeLeave = data.beforeLeave;
  var leave = data.leave;
  var afterLeave = data.afterLeave;
  var leaveCancelled = data.leaveCancelled;
  var delayLeave = data.delayLeave;
  var duration = data.duration;

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(leave);

  var explicitLeaveDuration = toNumber(isObject(duration) ? duration.leave : duration);

  if (process.env.NODE_ENV !== 'production' && isDef(explicitLeaveDuration)) {
    checkDuration(explicitLeaveDuration, 'leave', vnode);
  }

  var cb = el._leaveCb = once(function () {
    if (el.parentNode && el.parentNode._pending) {
      el.parentNode._pending[vnode.key] = null;
    }
    if (expectsCSS) {
      removeTransitionClass(el, leaveToClass);
      removeTransitionClass(el, leaveActiveClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, leaveClass);
      }
      leaveCancelled && leaveCancelled(el);
    } else {
      rm();
      afterLeave && afterLeave(el);
    }
    el._leaveCb = null;
  });

  if (delayLeave) {
    delayLeave(performLeave);
  } else {
    performLeave();
  }

  function performLeave() {
    // the delayed leave may have already been cancelled
    if (cb.cancelled) {
      return;
    }
    // record leaving element
    if (!vnode.data.show) {
      (el.parentNode._pending || (el.parentNode._pending = {}))[vnode.key] = vnode;
    }
    beforeLeave && beforeLeave(el);
    if (expectsCSS) {
      addTransitionClass(el, leaveClass);
      addTransitionClass(el, leaveActiveClass);
      nextFrame(function () {
        addTransitionClass(el, leaveToClass);
        removeTransitionClass(el, leaveClass);
        if (!cb.cancelled && !userWantsControl) {
          if (isValidDuration(explicitLeaveDuration)) {
            setTimeout(cb, explicitLeaveDuration);
          } else {
            whenTransitionEnds(el, type, cb);
          }
        }
      });
    }
    leave && leave(el, cb);
    if (!expectsCSS && !userWantsControl) {
      cb();
    }
  }
}

// only used in dev mode
function checkDuration(val, name, vnode) {
  if (typeof val !== 'number') {
    warn("<transition> explicit " + name + " duration is not a valid number - " + "got " + JSON.stringify(val) + ".", vnode.context);
  } else if (isNaN(val)) {
    warn("<transition> explicit " + name + " duration is NaN - " + 'the duration expression might be incorrect.', vnode.context);
  }
}

function isValidDuration(val) {
  return typeof val === 'number' && !isNaN(val);
}

/**
 * Normalize a transition hook's argument length. The hook may be:
 * - a merged hook (invoker) with the original in .fns
 * - a wrapped component method (check ._length)
 * - a plain function (.length)
 */
function getHookArgumentsLength(fn) {
  if (isUndef(fn)) {
    return false;
  }
  var invokerFns = fn.fns;
  if (isDef(invokerFns)) {
    // invoker
    return getHookArgumentsLength(Array.isArray(invokerFns) ? invokerFns[0] : invokerFns);
  } else {
    return (fn._length || fn.length) > 1;
  }
}

function _enter(_, vnode) {
  if (vnode.data.show !== true) {
    enter(vnode);
  }
}

var transition = inBrowser ? {
  create: _enter,
  activate: _enter,
  remove: function remove$$1(vnode, rm) {
    /* istanbul ignore else */
    if (vnode.data.show !== true) {
      leave(vnode, rm);
    } else {
      rm();
    }
  }
} : {};

var platformModules = [attrs, klass, events, domProps, style, transition];

/*  */

// the directive module should be applied last, after all
// built-in modules have been applied.
var modules = platformModules.concat(baseModules);

var patch = createPatchFunction({ nodeOps: nodeOps, modules: modules });

/**
 * Not type checking this file because flow doesn't like attaching
 * properties to Elements.
 */

/* istanbul ignore if */
if (isIE9) {
  // http://www.matts411.com/post/internet-explorer-9-oninput/
  document.addEventListener('selectionchange', function () {
    var el = document.activeElement;
    if (el && el.vmodel) {
      trigger(el, 'input');
    }
  });
}

var model$1 = {
  inserted: function inserted(el, binding, vnode) {
    if (vnode.tag === 'select') {
      setSelected(el, binding, vnode.context);
      el._vOptions = [].map.call(el.options, getValue);
    } else if (vnode.tag === 'textarea' || isTextInputType(el.type)) {
      el._vModifiers = binding.modifiers;
      if (!binding.modifiers.lazy) {
        // Safari < 10.2 & UIWebView doesn't fire compositionend when
        // switching focus before confirming composition choice
        // this also fixes the issue where some browsers e.g. iOS Chrome
        // fires "change" instead of "input" on autocomplete.
        el.addEventListener('change', onCompositionEnd);
        if (!isAndroid) {
          el.addEventListener('compositionstart', onCompositionStart);
          el.addEventListener('compositionend', onCompositionEnd);
        }
        /* istanbul ignore if */
        if (isIE9) {
          el.vmodel = true;
        }
      }
    }
  },
  componentUpdated: function componentUpdated(el, binding, vnode) {
    if (vnode.tag === 'select') {
      setSelected(el, binding, vnode.context);
      // in case the options rendered by v-for have changed,
      // it's possible that the value is out-of-sync with the rendered options.
      // detect such cases and filter out values that no longer has a matching
      // option in the DOM.
      var prevOptions = el._vOptions;
      var curOptions = el._vOptions = [].map.call(el.options, getValue);
      if (curOptions.some(function (o, i) {
        return !looseEqual(o, prevOptions[i]);
      })) {
        // trigger change event if
        // no matching option found for at least one value
        var needReset = el.multiple ? binding.value.some(function (v) {
          return hasNoMatchingOption(v, curOptions);
        }) : binding.value !== binding.oldValue && hasNoMatchingOption(binding.value, curOptions);
        if (needReset) {
          trigger(el, 'change');
        }
      }
    }
  }
};

function setSelected(el, binding, vm) {
  actuallySetSelected(el, binding, vm);
  /* istanbul ignore if */
  if (isIE || isEdge) {
    setTimeout(function () {
      actuallySetSelected(el, binding, vm);
    }, 0);
  }
}

function actuallySetSelected(el, binding, vm) {
  var value = binding.value;
  var isMultiple = el.multiple;
  if (isMultiple && !Array.isArray(value)) {
    process.env.NODE_ENV !== 'production' && warn("<select multiple v-model=\"" + binding.expression + "\"> " + "expects an Array value for its binding, but got " + Object.prototype.toString.call(value).slice(8, -1), vm);
    return;
  }
  var selected, option;
  for (var i = 0, l = el.options.length; i < l; i++) {
    option = el.options[i];
    if (isMultiple) {
      selected = looseIndexOf(value, getValue(option)) > -1;
      if (option.selected !== selected) {
        option.selected = selected;
      }
    } else {
      if (looseEqual(getValue(option), value)) {
        if (el.selectedIndex !== i) {
          el.selectedIndex = i;
        }
        return;
      }
    }
  }
  if (!isMultiple) {
    el.selectedIndex = -1;
  }
}

function hasNoMatchingOption(value, options) {
  return options.every(function (o) {
    return !looseEqual(o, value);
  });
}

function getValue(option) {
  return '_value' in option ? option._value : option.value;
}

function onCompositionStart(e) {
  e.target.composing = true;
}

function onCompositionEnd(e) {
  // prevent triggering an input event for no reason
  if (!e.target.composing) {
    return;
  }
  e.target.composing = false;
  trigger(e.target, 'input');
}

function trigger(el, type) {
  var e = document.createEvent('HTMLEvents');
  e.initEvent(type, true, true);
  el.dispatchEvent(e);
}

/*  */

// recursively search for possible transition defined inside the component root
function locateNode(vnode) {
  return vnode.componentInstance && (!vnode.data || !vnode.data.transition) ? locateNode(vnode.componentInstance._vnode) : vnode;
}

var show = {
  bind: function bind(el, ref, vnode) {
    var value = ref.value;

    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    var originalDisplay = el.__vOriginalDisplay = el.style.display === 'none' ? '' : el.style.display;
    if (value && transition$$1) {
      vnode.data.show = true;
      enter(vnode, function () {
        el.style.display = originalDisplay;
      });
    } else {
      el.style.display = value ? originalDisplay : 'none';
    }
  },

  update: function update(el, ref, vnode) {
    var value = ref.value;
    var oldValue = ref.oldValue;

    /* istanbul ignore if */
    if (value === oldValue) {
      return;
    }
    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    if (transition$$1) {
      vnode.data.show = true;
      if (value) {
        enter(vnode, function () {
          el.style.display = el.__vOriginalDisplay;
        });
      } else {
        leave(vnode, function () {
          el.style.display = 'none';
        });
      }
    } else {
      el.style.display = value ? el.__vOriginalDisplay : 'none';
    }
  },

  unbind: function unbind(el, binding, vnode, oldVnode, isDestroy) {
    if (!isDestroy) {
      el.style.display = el.__vOriginalDisplay;
    }
  }
};

var platformDirectives = {
  model: model$1,
  show: show
};

/*  */

// Provides transition support for a single element/component.
// supports transition mode (out-in / in-out)

var transitionProps = {
  name: String,
  appear: Boolean,
  css: Boolean,
  mode: String,
  type: String,
  enterClass: String,
  leaveClass: String,
  enterToClass: String,
  leaveToClass: String,
  enterActiveClass: String,
  leaveActiveClass: String,
  appearClass: String,
  appearActiveClass: String,
  appearToClass: String,
  duration: [Number, String, Object]
};

// in case the child is also an abstract component, e.g. <keep-alive>
// we want to recursively retrieve the real component to be rendered
function getRealChild(vnode) {
  var compOptions = vnode && vnode.componentOptions;
  if (compOptions && compOptions.Ctor.options.abstract) {
    return getRealChild(getFirstComponentChild(compOptions.children));
  } else {
    return vnode;
  }
}

function extractTransitionData(comp) {
  var data = {};
  var options = comp.$options;
  // props
  for (var key in options.propsData) {
    data[key] = comp[key];
  }
  // events.
  // extract listeners and pass them directly to the transition methods
  var listeners = options._parentListeners;
  for (var key$1 in listeners) {
    data[camelize(key$1)] = listeners[key$1];
  }
  return data;
}

function placeholder(h, rawChild) {
  if (/\d-keep-alive$/.test(rawChild.tag)) {
    return h('keep-alive', {
      props: rawChild.componentOptions.propsData
    });
  }
}

function hasParentTransition(vnode) {
  while (vnode = vnode.parent) {
    if (vnode.data.transition) {
      return true;
    }
  }
}

function isSameChild(child, oldChild) {
  return oldChild.key === child.key && oldChild.tag === child.tag;
}

var Transition = {
  name: 'transition',
  props: transitionProps,
  abstract: true,

  render: function render(h) {
    var this$1 = this;

    var children = this.$options._renderChildren;
    if (!children) {
      return;
    }

    // filter out text nodes (possible whitespaces)
    children = children.filter(function (c) {
      return c.tag || isAsyncPlaceholder(c);
    });
    /* istanbul ignore if */
    if (!children.length) {
      return;
    }

    // warn multiple elements
    if (process.env.NODE_ENV !== 'production' && children.length > 1) {
      warn('<transition> can only be used on a single element. Use ' + '<transition-group> for lists.', this.$parent);
    }

    var mode = this.mode;

    // warn invalid mode
    if (process.env.NODE_ENV !== 'production' && mode && mode !== 'in-out' && mode !== 'out-in') {
      warn('invalid <transition> mode: ' + mode, this.$parent);
    }

    var rawChild = children[0];

    // if this is a component root node and the component's
    // parent container node also has transition, skip.
    if (hasParentTransition(this.$vnode)) {
      return rawChild;
    }

    // apply transition data to child
    // use getRealChild() to ignore abstract components e.g. keep-alive
    var child = getRealChild(rawChild);
    /* istanbul ignore if */
    if (!child) {
      return rawChild;
    }

    if (this._leaving) {
      return placeholder(h, rawChild);
    }

    // ensure a key that is unique to the vnode type and to this transition
    // component instance. This key will be used to remove pending leaving nodes
    // during entering.
    var id = "__transition-" + this._uid + "-";
    child.key = child.key == null ? child.isComment ? id + 'comment' : id + child.tag : isPrimitive(child.key) ? String(child.key).indexOf(id) === 0 ? child.key : id + child.key : child.key;

    var data = (child.data || (child.data = {})).transition = extractTransitionData(this);
    var oldRawChild = this._vnode;
    var oldChild = getRealChild(oldRawChild);

    // mark v-show
    // so that the transition module can hand over the control to the directive
    if (child.data.directives && child.data.directives.some(function (d) {
      return d.name === 'show';
    })) {
      child.data.show = true;
    }

    if (oldChild && oldChild.data && !isSameChild(child, oldChild) && !isAsyncPlaceholder(oldChild)) {
      // replace old child transition data with fresh one
      // important for dynamic transitions!
      var oldData = oldChild.data.transition = extend({}, data);
      // handle transition mode
      if (mode === 'out-in') {
        // return placeholder node and queue update when leave finishes
        this._leaving = true;
        mergeVNodeHook(oldData, 'afterLeave', function () {
          this$1._leaving = false;
          this$1.$forceUpdate();
        });
        return placeholder(h, rawChild);
      } else if (mode === 'in-out') {
        if (isAsyncPlaceholder(child)) {
          return oldRawChild;
        }
        var delayedLeave;
        var performLeave = function performLeave() {
          delayedLeave();
        };
        mergeVNodeHook(data, 'afterEnter', performLeave);
        mergeVNodeHook(data, 'enterCancelled', performLeave);
        mergeVNodeHook(oldData, 'delayLeave', function (leave) {
          delayedLeave = leave;
        });
      }
    }

    return rawChild;
  }
};

/*  */

// Provides transition support for list items.
// supports move transitions using the FLIP technique.

// Because the vdom's children update algorithm is "unstable" - i.e.
// it doesn't guarantee the relative positioning of removed elements,
// we force transition-group to update its children into two passes:
// in the first pass, we remove all nodes that need to be removed,
// triggering their leaving transition; in the second pass, we insert/move
// into the final desired state. This way in the second pass removed
// nodes will remain where they should be.

var props = extend({
  tag: String,
  moveClass: String
}, transitionProps);

delete props.mode;

var TransitionGroup = {
  props: props,

  render: function render(h) {
    var tag = this.tag || this.$vnode.data.tag || 'span';
    var map = Object.create(null);
    var prevChildren = this.prevChildren = this.children;
    var rawChildren = this.$slots.default || [];
    var children = this.children = [];
    var transitionData = extractTransitionData(this);

    for (var i = 0; i < rawChildren.length; i++) {
      var c = rawChildren[i];
      if (c.tag) {
        if (c.key != null && String(c.key).indexOf('__vlist') !== 0) {
          children.push(c);
          map[c.key] = c;(c.data || (c.data = {})).transition = transitionData;
        } else if (process.env.NODE_ENV !== 'production') {
          var opts = c.componentOptions;
          var name = opts ? opts.Ctor.options.name || opts.tag || '' : c.tag;
          warn("<transition-group> children must be keyed: <" + name + ">");
        }
      }
    }

    if (prevChildren) {
      var kept = [];
      var removed = [];
      for (var i$1 = 0; i$1 < prevChildren.length; i$1++) {
        var c$1 = prevChildren[i$1];
        c$1.data.transition = transitionData;
        c$1.data.pos = c$1.elm.getBoundingClientRect();
        if (map[c$1.key]) {
          kept.push(c$1);
        } else {
          removed.push(c$1);
        }
      }
      this.kept = h(tag, null, kept);
      this.removed = removed;
    }

    return h(tag, null, children);
  },

  beforeUpdate: function beforeUpdate() {
    // force removing pass
    this.__patch__(this._vnode, this.kept, false, // hydrating
    true // removeOnly (!important, avoids unnecessary moves)
    );
    this._vnode = this.kept;
  },

  updated: function updated() {
    var children = this.prevChildren;
    var moveClass = this.moveClass || (this.name || 'v') + '-move';
    if (!children.length || !this.hasMove(children[0].elm, moveClass)) {
      return;
    }

    // we divide the work into three loops to avoid mixing DOM reads and writes
    // in each iteration - which helps prevent layout thrashing.
    children.forEach(callPendingCbs);
    children.forEach(recordPosition);
    children.forEach(applyTranslation);

    // force reflow to put everything in position
    // assign to this to avoid being removed in tree-shaking
    // $flow-disable-line
    this._reflow = document.body.offsetHeight;

    children.forEach(function (c) {
      if (c.data.moved) {
        var el = c.elm;
        var s = el.style;
        addTransitionClass(el, moveClass);
        s.transform = s.WebkitTransform = s.transitionDuration = '';
        el.addEventListener(transitionEndEvent, el._moveCb = function cb(e) {
          if (!e || /transform$/.test(e.propertyName)) {
            el.removeEventListener(transitionEndEvent, cb);
            el._moveCb = null;
            removeTransitionClass(el, moveClass);
          }
        });
      }
    });
  },

  methods: {
    hasMove: function hasMove(el, moveClass) {
      /* istanbul ignore if */
      if (!hasTransition) {
        return false;
      }
      /* istanbul ignore if */
      if (this._hasMove) {
        return this._hasMove;
      }
      // Detect whether an element with the move class applied has
      // CSS transitions. Since the element may be inside an entering
      // transition at this very moment, we make a clone of it and remove
      // all other transition classes applied to ensure only the move class
      // is applied.
      var clone = el.cloneNode();
      if (el._transitionClasses) {
        el._transitionClasses.forEach(function (cls) {
          removeClass(clone, cls);
        });
      }
      addClass(clone, moveClass);
      clone.style.display = 'none';
      this.$el.appendChild(clone);
      var info = getTransitionInfo(clone);
      this.$el.removeChild(clone);
      return this._hasMove = info.hasTransform;
    }
  }
};

function callPendingCbs(c) {
  /* istanbul ignore if */
  if (c.elm._moveCb) {
    c.elm._moveCb();
  }
  /* istanbul ignore if */
  if (c.elm._enterCb) {
    c.elm._enterCb();
  }
}

function recordPosition(c) {
  c.data.newPos = c.elm.getBoundingClientRect();
}

function applyTranslation(c) {
  var oldPos = c.data.pos;
  var newPos = c.data.newPos;
  var dx = oldPos.left - newPos.left;
  var dy = oldPos.top - newPos.top;
  if (dx || dy) {
    c.data.moved = true;
    var s = c.elm.style;
    s.transform = s.WebkitTransform = "translate(" + dx + "px," + dy + "px)";
    s.transitionDuration = '0s';
  }
}

var platformComponents = {
  Transition: Transition,
  TransitionGroup: TransitionGroup
};

/*  */

// install platform specific utils
Vue$3.config.mustUseProp = mustUseProp;
Vue$3.config.isReservedTag = isReservedTag;
Vue$3.config.isReservedAttr = isReservedAttr;
Vue$3.config.getTagNamespace = getTagNamespace;
Vue$3.config.isUnknownElement = isUnknownElement;

// install platform runtime directives & components
extend(Vue$3.options.directives, platformDirectives);
extend(Vue$3.options.components, platformComponents);

// install platform patch function
Vue$3.prototype.__patch__ = inBrowser ? patch : noop;

// public mount method
Vue$3.prototype.$mount = function (el, hydrating) {
  el = el && inBrowser ? query(el) : undefined;
  return mountComponent(this, el, hydrating);
};

// devtools global hook
/* istanbul ignore next */
Vue$3.nextTick(function () {
  if (config.devtools) {
    if (devtools) {
      devtools.emit('init', Vue$3);
    } else if (process.env.NODE_ENV !== 'production' && isChrome) {
      console[console.info ? 'info' : 'log']('Download the Vue Devtools extension for a better development experience:\n' + 'https://github.com/vuejs/vue-devtools');
    }
  }
  if (process.env.NODE_ENV !== 'production' && config.productionTip !== false && inBrowser && typeof console !== 'undefined') {
    console[console.info ? 'info' : 'log']("You are running Vue in development mode.\n" + "Make sure to turn on production mode when deploying for production.\n" + "See more tips at https://vuejs.org/guide/deployment.html");
  }
}, 0);

/*  */

exports.default = Vue$3;
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4), __webpack_require__(14), __webpack_require__(49).setImmediate))

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(process) {

var utils = __webpack_require__(0);
var normalizeHeaderName = __webpack_require__(44);

var DEFAULT_CONTENT_TYPE = {
  'Content-Type': 'application/x-www-form-urlencoded'
};

function setContentTypeIfUnset(headers, value) {
  if (!utils.isUndefined(headers) && utils.isUndefined(headers['Content-Type'])) {
    headers['Content-Type'] = value;
  }
}

function getDefaultAdapter() {
  var adapter;
  if (typeof XMLHttpRequest !== 'undefined') {
    // For browsers use XHR adapter
    adapter = __webpack_require__(9);
  } else if (typeof process !== 'undefined') {
    // For node use HTTP adapter
    adapter = __webpack_require__(9);
  }
  return adapter;
}

var defaults = {
  adapter: getDefaultAdapter(),

  transformRequest: [function transformRequest(data, headers) {
    normalizeHeaderName(headers, 'Content-Type');
    if (utils.isFormData(data) || utils.isArrayBuffer(data) || utils.isBuffer(data) || utils.isStream(data) || utils.isFile(data) || utils.isBlob(data)) {
      return data;
    }
    if (utils.isArrayBufferView(data)) {
      return data.buffer;
    }
    if (utils.isURLSearchParams(data)) {
      setContentTypeIfUnset(headers, 'application/x-www-form-urlencoded;charset=utf-8');
      return data.toString();
    }
    if (utils.isObject(data)) {
      setContentTypeIfUnset(headers, 'application/json;charset=utf-8');
      return JSON.stringify(data);
    }
    return data;
  }],

  transformResponse: [function transformResponse(data) {
    /*eslint no-param-reassign:0*/
    if (typeof data === 'string') {
      try {
        data = JSON.parse(data);
      } catch (e) {/* Ignore */}
    }
    return data;
  }],

  timeout: 0,

  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',

  maxContentLength: -1,

  validateStatus: function validateStatus(status) {
    return status >= 200 && status < 300;
  }
};

defaults.headers = {
  common: {
    'Accept': 'application/json, text/plain, */*'
  }
};

utils.forEach(['delete', 'get', 'head'], function forEachMethodNoData(method) {
  defaults.headers[method] = {};
});

utils.forEach(['post', 'put', 'patch'], function forEachMethodWithData(method) {
  defaults.headers[method] = utils.merge(DEFAULT_CONTENT_TYPE);
});

module.exports = defaults;
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

// jscs:disable disallowFunctionDeclarations
exports.default = {

    props: {
        bindData: {
            type: String,
            default: ''
        },
        bindEvent: {
            type: String,
            default: ''
        }
    },
    created: function created() {
        var _this2 = this;

        this._protected = {
            parseProps: function parseProps(propName, data) {
                switch (propName) {
                    case 'bindEvent':
                        var request = data.replace(/\s/g, '');
                        var bindings = request.split(',');
                        bindings.forEach(function (binding) {
                            var words = binding.split(':');
                            if (words.length === 2) {
                                var eventName = words[0];
                                var rule = words[1];
                                var match = rule.match(/(.+?)\.(.+)/);
                                if (match && match.length > 2) {
                                    var id = match[1];
                                    var action = match[2];
                                    try {
                                        var elements = _this2.$(id);
                                        if (elements) {
                                            _this2._protected.bindedEvents.push(eventName);
                                            _this2.$on(eventName, function () {
                                                for (var _len = arguments.length, arg = Array(_len), _key = 0; _key < _len; _key++) {
                                                    arg[_key] = arguments[_key];
                                                }

                                                elements.each(function (index, elem) {
                                                    var value = action.split('.').reduce(function (a, b) {
                                                        return a[b];
                                                    }, elem);
                                                    if (typeof value === 'function') {
                                                        value.apply(undefined, arg);
                                                    }
                                                });
                                            });
                                        }
                                    } catch (e) {
                                        console.error('bind-event props error: ' + 'The element specified is not a valid jQuery selector');
                                    }
                                }
                            }
                        });
                        break;
                }
            },

            // Expose public methods (from method sections) in DOM props
            bindPublicMethods: function bindPublicMethods() {
                // Bind exposed methods to events
                var _this = _this2;
                Object.keys(_this2.$options.methods).forEach(function (methodName) {
                    var method = _defineProperty({}, methodName, function () {
                        for (var _len2 = arguments.length, args = Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
                            args[_key2] = arguments[_key2];
                        }

                        try {
                            var _this$methodName;

                            var ret = (_this$methodName = _this[methodName]).call.apply(_this$methodName, [_this].concat(args));
                            return ret;
                        } catch (e) {}
                    });
                    if (methodName !== '$emit') {
                        _this2.$(_this2.$el).parent().prop('publicMethods', function (index, oldPropVal) {
                            if (!oldPropVal) {
                                return method;
                            } else {
                                return Object.assign({}, oldPropVal, method);
                            }
                        });
                    }
                });
            },

            bindedEvents: []
        };
    },
    mounted: function mounted() {
        var _this3 = this;

        var ready = function ready() {
            if (_this3.bindData) {
                _this3._protected.parseProps('bindData', _this3.bindData);
            }

            if (_this3.bindEvent) {
                _this3._protected.parseProps('bindEvent', _this3.bindEvent);
            }

            _this3._protected.bindPublicMethods();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },
    destroyed: function destroyed() {
        var _this4 = this;

        this._protected.bindedEvents.forEach(function (e) {
            _this4.$off(e);
        });
    }
};

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = __webpack_require__(30);

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(process) {

var utils = __webpack_require__(0);
var settle = __webpack_require__(36);
var buildURL = __webpack_require__(39);
var parseHeaders = __webpack_require__(45);
var isURLSameOrigin = __webpack_require__(43);
var createError = __webpack_require__(12);
var btoa = typeof window !== 'undefined' && window.btoa && window.btoa.bind(window) || __webpack_require__(38);

module.exports = function xhrAdapter(config) {
  return new Promise(function dispatchXhrRequest(resolve, reject) {
    var requestData = config.data;
    var requestHeaders = config.headers;

    if (utils.isFormData(requestData)) {
      delete requestHeaders['Content-Type']; // Let the browser set it
    }

    var request = new XMLHttpRequest();
    var loadEvent = 'onreadystatechange';
    var xDomain = false;

    // For IE 8/9 CORS support
    // Only supports POST and GET calls and doesn't returns the response headers.
    // DON'T do this for testing b/c XMLHttpRequest is mocked, not XDomainRequest.
    if (process.env.NODE_ENV !== 'test' && typeof window !== 'undefined' && window.XDomainRequest && !('withCredentials' in request) && !isURLSameOrigin(config.url)) {
      request = new window.XDomainRequest();
      loadEvent = 'onload';
      xDomain = true;
      request.onprogress = function handleProgress() {};
      request.ontimeout = function handleTimeout() {};
    }

    // HTTP basic authentication
    if (config.auth) {
      var username = config.auth.username || '';
      var password = config.auth.password || '';
      requestHeaders.Authorization = 'Basic ' + btoa(username + ':' + password);
    }

    request.open(config.method.toUpperCase(), buildURL(config.url, config.params, config.paramsSerializer), true);

    // Set the request timeout in MS
    request.timeout = config.timeout;

    // Listen for ready state
    request[loadEvent] = function handleLoad() {
      if (!request || request.readyState !== 4 && !xDomain) {
        return;
      }

      // The request errored out and we didn't get a response, this will be
      // handled by onerror instead
      // With one exception: request that using file: protocol, most browsers
      // will return status as 0 even though it's a successful request
      if (request.status === 0 && !(request.responseURL && request.responseURL.indexOf('file:') === 0)) {
        return;
      }

      // Prepare the response
      var responseHeaders = 'getAllResponseHeaders' in request ? parseHeaders(request.getAllResponseHeaders()) : null;
      var responseData = !config.responseType || config.responseType === 'text' ? request.responseText : request.response;
      var response = {
        data: responseData,
        // IE sends 1223 instead of 204 (https://github.com/mzabriskie/axios/issues/201)
        status: request.status === 1223 ? 204 : request.status,
        statusText: request.status === 1223 ? 'No Content' : request.statusText,
        headers: responseHeaders,
        config: config,
        request: request
      };

      settle(resolve, reject, response);

      // Clean up request
      request = null;
    };

    // Handle low level network errors
    request.onerror = function handleError() {
      // Real errors are hidden from us by the browser
      // onerror should only fire if it's a network error
      reject(createError('Network Error', config, null, request));

      // Clean up request
      request = null;
    };

    // Handle timeout
    request.ontimeout = function handleTimeout() {
      reject(createError('timeout of ' + config.timeout + 'ms exceeded', config, 'ECONNABORTED', request));

      // Clean up request
      request = null;
    };

    // Add xsrf header
    // This is only done if running in a standard browser environment.
    // Specifically not if we're in a web worker, or react-native.
    if (utils.isStandardBrowserEnv()) {
      var cookies = __webpack_require__(41);

      // Add xsrf header
      var xsrfValue = (config.withCredentials || isURLSameOrigin(config.url)) && config.xsrfCookieName ? cookies.read(config.xsrfCookieName) : undefined;

      if (xsrfValue) {
        requestHeaders[config.xsrfHeaderName] = xsrfValue;
      }
    }

    // Add headers to the request
    if ('setRequestHeader' in request) {
      utils.forEach(requestHeaders, function setRequestHeader(val, key) {
        if (typeof requestData === 'undefined' && key.toLowerCase() === 'content-type') {
          // Remove Content-Type if data is undefined
          delete requestHeaders[key];
        } else {
          // Otherwise add header to the request
          request.setRequestHeader(key, val);
        }
      });
    }

    // Add withCredentials to request if needed
    if (config.withCredentials) {
      request.withCredentials = true;
    }

    // Add responseType to request if needed
    if (config.responseType) {
      try {
        request.responseType = config.responseType;
      } catch (e) {
        // Expected DOMException thrown by browsers not compatible XMLHttpRequest Level 2.
        // But, this can be suppressed for 'json' type as it can be parsed by default 'transformResponse' function.
        if (config.responseType !== 'json') {
          throw e;
        }
      }
    }

    // Handle progress if needed
    if (typeof config.onDownloadProgress === 'function') {
      request.addEventListener('progress', config.onDownloadProgress);
    }

    // Not all browsers support upload events
    if (typeof config.onUploadProgress === 'function' && request.upload) {
      request.upload.addEventListener('progress', config.onUploadProgress);
    }

    if (config.cancelToken) {
      // Handle cancellation
      config.cancelToken.promise.then(function onCanceled(cancel) {
        if (!request) {
          return;
        }

        request.abort();
        reject(cancel);
        // Clean up request
        request = null;
      });
    }

    if (requestData === undefined) {
      requestData = null;
    }

    // Send the request
    request.send(requestData);
  });
};
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * A `Cancel` is an object that is thrown when an operation is canceled.
 *
 * @class
 * @param {string=} message The message.
 */

function Cancel(message) {
  this.message = message;
}

Cancel.prototype.toString = function toString() {
  return 'Cancel' + (this.message ? ': ' + this.message : '');
};

Cancel.prototype.__CANCEL__ = true;

module.exports = Cancel;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function isCancel(value) {
  return !!(value && value.__CANCEL__);
};

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var enhanceError = __webpack_require__(35);

/**
 * Create an Error with the specified message, config, error code, request and response.
 *
 * @param {string} message The error message.
 * @param {Object} config The config.
 * @param {string} [code] The error code (for example, 'ECONNABORTED').
 * @param {Object} [request] The request.
 * @param {Object} [response] The response.
 * @returns {Error} The created error.
 */
module.exports = function createError(message, config, code, request, response) {
  var error = new Error(message);
  return enhanceError(error, config, code, request, response);
};

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function bind(fn, thisArg) {
  return function wrap() {
    var args = new Array(arguments.length);
    for (var i = 0; i < args.length; i++) {
      args[i] = arguments[i];
    }
    return fn.apply(thisArg, args);
  };
};

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var g;

// This works in non-strict mode
g = function () {
	return this;
}();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1, eval)("this");
} catch (e) {
	// This works if the window reference is available
	if ((typeof window === "undefined" ? "undefined" : _typeof(window)) === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/*!

Copyright (C) 2014-2016 by Andrea Giammarchi - @WebReflection

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
// global window Object
// optional polyfill info
//    'auto' used by default, everything is feature detected
//    'force' use the polyfill even if not fully needed
function installCustomElements(window, polyfill) {
  'use strict';

  // DO NOT USE THIS FILE DIRECTLY, IT WON'T WORK
  // THIS IS A PROJECT BASED ON A BUILD SYSTEM
  // THIS FILE IS JUST WRAPPED UP RESULTING IN
  // build/document-register-element.node.js

  var document = window.document,
      Object = window.Object;

  var htmlClass = function (info) {
    // (C) Andrea Giammarchi - @WebReflection - MIT Style
    var catchClass = /^[A-Z]+[a-z]/,
        filterBy = function filterBy(re) {
      var arr = [],
          tag;
      for (tag in register) {
        if (re.test(tag)) arr.push(tag);
      }
      return arr;
    },
        add = function add(Class, tag) {
      tag = tag.toLowerCase();
      if (!(tag in register)) {
        register[Class] = (register[Class] || []).concat(tag);
        register[tag] = register[tag.toUpperCase()] = Class;
      }
    },
        register = (Object.create || Object)(null),
        htmlClass = {},
        i,
        section,
        tags,
        Class;
    for (section in info) {
      for (Class in info[section]) {
        tags = info[section][Class];
        register[Class] = tags;
        for (i = 0; i < tags.length; i++) {
          register[tags[i].toLowerCase()] = register[tags[i].toUpperCase()] = Class;
        }
      }
    }
    htmlClass.get = function get(tagOrClass) {
      return typeof tagOrClass === 'string' ? register[tagOrClass] || (catchClass.test(tagOrClass) ? [] : '') : filterBy(tagOrClass);
    };
    htmlClass.set = function set(tag, Class) {
      return catchClass.test(tag) ? add(tag, Class) : add(Class, tag), htmlClass;
    };
    return htmlClass;
  }({
    "collections": {
      "HTMLAllCollection": ["all"],
      "HTMLCollection": ["forms"],
      "HTMLFormControlsCollection": ["elements"],
      "HTMLOptionsCollection": ["options"]
    },
    "elements": {
      "Element": ["element"],
      "HTMLAnchorElement": ["a"],
      "HTMLAppletElement": ["applet"],
      "HTMLAreaElement": ["area"],
      "HTMLAttachmentElement": ["attachment"],
      "HTMLAudioElement": ["audio"],
      "HTMLBRElement": ["br"],
      "HTMLBaseElement": ["base"],
      "HTMLBodyElement": ["body"],
      "HTMLButtonElement": ["button"],
      "HTMLCanvasElement": ["canvas"],
      "HTMLContentElement": ["content"],
      "HTMLDListElement": ["dl"],
      "HTMLDataElement": ["data"],
      "HTMLDataListElement": ["datalist"],
      "HTMLDetailsElement": ["details"],
      "HTMLDialogElement": ["dialog"],
      "HTMLDirectoryElement": ["dir"],
      "HTMLDivElement": ["div"],
      "HTMLDocument": ["document"],
      "HTMLElement": ["element", "abbr", "address", "article", "aside", "b", "bdi", "bdo", "cite", "code", "command", "dd", "dfn", "dt", "em", "figcaption", "figure", "footer", "header", "i", "kbd", "mark", "nav", "noscript", "rp", "rt", "ruby", "s", "samp", "section", "small", "strong", "sub", "summary", "sup", "u", "var", "wbr"],
      "HTMLEmbedElement": ["embed"],
      "HTMLFieldSetElement": ["fieldset"],
      "HTMLFontElement": ["font"],
      "HTMLFormElement": ["form"],
      "HTMLFrameElement": ["frame"],
      "HTMLFrameSetElement": ["frameset"],
      "HTMLHRElement": ["hr"],
      "HTMLHeadElement": ["head"],
      "HTMLHeadingElement": ["h1", "h2", "h3", "h4", "h5", "h6"],
      "HTMLHtmlElement": ["html"],
      "HTMLIFrameElement": ["iframe"],
      "HTMLImageElement": ["img"],
      "HTMLInputElement": ["input"],
      "HTMLKeygenElement": ["keygen"],
      "HTMLLIElement": ["li"],
      "HTMLLabelElement": ["label"],
      "HTMLLegendElement": ["legend"],
      "HTMLLinkElement": ["link"],
      "HTMLMapElement": ["map"],
      "HTMLMarqueeElement": ["marquee"],
      "HTMLMediaElement": ["media"],
      "HTMLMenuElement": ["menu"],
      "HTMLMenuItemElement": ["menuitem"],
      "HTMLMetaElement": ["meta"],
      "HTMLMeterElement": ["meter"],
      "HTMLModElement": ["del", "ins"],
      "HTMLOListElement": ["ol"],
      "HTMLObjectElement": ["object"],
      "HTMLOptGroupElement": ["optgroup"],
      "HTMLOptionElement": ["option"],
      "HTMLOutputElement": ["output"],
      "HTMLParagraphElement": ["p"],
      "HTMLParamElement": ["param"],
      "HTMLPictureElement": ["picture"],
      "HTMLPreElement": ["pre"],
      "HTMLProgressElement": ["progress"],
      "HTMLQuoteElement": ["blockquote", "q", "quote"],
      "HTMLScriptElement": ["script"],
      "HTMLSelectElement": ["select"],
      "HTMLShadowElement": ["shadow"],
      "HTMLSlotElement": ["slot"],
      "HTMLSourceElement": ["source"],
      "HTMLSpanElement": ["span"],
      "HTMLStyleElement": ["style"],
      "HTMLTableCaptionElement": ["caption"],
      "HTMLTableCellElement": ["td", "th"],
      "HTMLTableColElement": ["col", "colgroup"],
      "HTMLTableElement": ["table"],
      "HTMLTableRowElement": ["tr"],
      "HTMLTableSectionElement": ["thead", "tbody", "tfoot"],
      "HTMLTemplateElement": ["template"],
      "HTMLTextAreaElement": ["textarea"],
      "HTMLTimeElement": ["time"],
      "HTMLTitleElement": ["title"],
      "HTMLTrackElement": ["track"],
      "HTMLUListElement": ["ul"],
      "HTMLUnknownElement": ["unknown", "vhgroupv", "vkeygen"],
      "HTMLVideoElement": ["video"]
    },
    "nodes": {
      "Attr": ["node"],
      "Audio": ["audio"],
      "CDATASection": ["node"],
      "CharacterData": ["node"],
      "Comment": ["#comment"],
      "Document": ["#document"],
      "DocumentFragment": ["#document-fragment"],
      "DocumentType": ["node"],
      "HTMLDocument": ["#document"],
      "Image": ["img"],
      "Option": ["option"],
      "ProcessingInstruction": ["node"],
      "ShadowRoot": ["#shadow-root"],
      "Text": ["#text"],
      "XMLDocument": ["xml"]
    }
  });

  // passed at runtime, configurable via nodejs module
  if ((typeof polyfill === 'undefined' ? 'undefined' : _typeof(polyfill)) !== 'object') polyfill = { type: polyfill || 'auto' };

  var
  // V0 polyfill entry
  REGISTER_ELEMENT = 'registerElement',


  // IE < 11 only + old WebKit for attributes + feature detection
  EXPANDO_UID = '__' + REGISTER_ELEMENT + (window.Math.random() * 10e4 >> 0),


  // shortcuts and costants
  ADD_EVENT_LISTENER = 'addEventListener',
      ATTACHED = 'attached',
      CALLBACK = 'Callback',
      DETACHED = 'detached',
      EXTENDS = 'extends',
      ATTRIBUTE_CHANGED_CALLBACK = 'attributeChanged' + CALLBACK,
      ATTACHED_CALLBACK = ATTACHED + CALLBACK,
      CONNECTED_CALLBACK = 'connected' + CALLBACK,
      DISCONNECTED_CALLBACK = 'disconnected' + CALLBACK,
      CREATED_CALLBACK = 'created' + CALLBACK,
      DETACHED_CALLBACK = DETACHED + CALLBACK,
      ADDITION = 'ADDITION',
      MODIFICATION = 'MODIFICATION',
      REMOVAL = 'REMOVAL',
      DOM_ATTR_MODIFIED = 'DOMAttrModified',
      DOM_CONTENT_LOADED = 'DOMContentLoaded',
      DOM_SUBTREE_MODIFIED = 'DOMSubtreeModified',
      PREFIX_TAG = '<',
      PREFIX_IS = '=',


  // valid and invalid node names
  validName = /^[A-Z][A-Z0-9]*(?:-[A-Z0-9]+)+$/,
      invalidNames = ['ANNOTATION-XML', 'COLOR-PROFILE', 'FONT-FACE', 'FONT-FACE-SRC', 'FONT-FACE-URI', 'FONT-FACE-FORMAT', 'FONT-FACE-NAME', 'MISSING-GLYPH'],


  // registered types and their prototypes
  types = [],
      protos = [],


  // to query subnodes
  query = '',


  // html shortcut used to feature detect
  documentElement = document.documentElement,


  // ES5 inline helpers || basic patches
  indexOf = types.indexOf || function (v) {
    for (var i = this.length; i-- && this[i] !== v;) {}
    return i;
  },


  // other helpers / shortcuts
  OP = Object.prototype,
      hOP = OP.hasOwnProperty,
      iPO = OP.isPrototypeOf,
      defineProperty = Object.defineProperty,
      empty = [],
      gOPD = Object.getOwnPropertyDescriptor,
      gOPN = Object.getOwnPropertyNames,
      gPO = Object.getPrototypeOf,
      sPO = Object.setPrototypeOf,


  // jshint proto: true
  hasProto = !!Object.__proto__,


  // V1 helpers
  fixGetClass = false,
      DRECEV1 = '__dreCEv1',
      customElements = window.customElements,
      usableCustomElements = !/^force/.test(polyfill.type) && !!(customElements && customElements.define && customElements.get && customElements.whenDefined),
      Dict = Object.create || Object,
      Map = window.Map || function Map() {
    var K = [],
        V = [],
        i;
    return {
      get: function get(k) {
        return V[indexOf.call(K, k)];
      },
      set: function set(k, v) {
        i = indexOf.call(K, k);
        if (i < 0) V[K.push(k) - 1] = v;else V[i] = v;
      }
    };
  },
      Promise = window.Promise || function (fn) {
    var notify = [],
        done = false,
        p = {
      'catch': function _catch() {
        return p;
      },
      'then': function then(cb) {
        notify.push(cb);
        if (done) setTimeout(resolve, 1);
        return p;
      }
    };
    function resolve(value) {
      done = true;
      while (notify.length) {
        notify.shift()(value);
      }
    }
    fn(resolve);
    return p;
  },
      justCreated = false,
      constructors = Dict(null),
      waitingList = Dict(null),
      nodeNames = new Map(),
      secondArgument = function secondArgument(is) {
    return is.toLowerCase();
  },


  // used to create unique instances
  create = Object.create || function Bridge(proto) {
    // silly broken polyfill probably ever used but short enough to work
    return proto ? (Bridge.prototype = proto, new Bridge()) : this;
  },


  // will set the prototype if possible
  // or copy over all properties
  setPrototype = sPO || (hasProto ? function (o, p) {
    o.__proto__ = p;
    return o;
  } : gOPN && gOPD ? function () {
    function setProperties(o, p) {
      for (var key, names = gOPN(p), i = 0, length = names.length; i < length; i++) {
        key = names[i];
        if (!hOP.call(o, key)) {
          defineProperty(o, key, gOPD(p, key));
        }
      }
    }
    return function (o, p) {
      do {
        setProperties(o, p);
      } while ((p = gPO(p)) && !iPO.call(p, o));
      return o;
    };
  }() : function (o, p) {
    for (var key in p) {
      o[key] = p[key];
    }
    return o;
  }),


  // DOM shortcuts and helpers, if any

  MutationObserver = window.MutationObserver || window.WebKitMutationObserver,
      HTMLElementPrototype = (window.HTMLElement || window.Element || window.Node).prototype,
      IE8 = !iPO.call(HTMLElementPrototype, documentElement),
      safeProperty = IE8 ? function (o, k, d) {
    o[k] = d.value;
    return o;
  } : defineProperty,
      isValidNode = IE8 ? function (node) {
    return node.nodeType === 1;
  } : function (node) {
    return iPO.call(HTMLElementPrototype, node);
  },
      targets = IE8 && [],
      attachShadow = HTMLElementPrototype.attachShadow,
      cloneNode = HTMLElementPrototype.cloneNode,
      dispatchEvent = HTMLElementPrototype.dispatchEvent,
      getAttribute = HTMLElementPrototype.getAttribute,
      hasAttribute = HTMLElementPrototype.hasAttribute,
      removeAttribute = HTMLElementPrototype.removeAttribute,
      setAttribute = HTMLElementPrototype.setAttribute,


  // replaced later on
  createElement = document.createElement,
      patchedCreateElement = createElement,


  // shared observer for all attributes
  attributesObserver = MutationObserver && {
    attributes: true,
    characterData: true,
    attributeOldValue: true
  },


  // useful to detect only if there's no MutationObserver
  DOMAttrModified = MutationObserver || function (e) {
    doesNotSupportDOMAttrModified = false;
    documentElement.removeEventListener(DOM_ATTR_MODIFIED, DOMAttrModified);
  },


  // will both be used to make DOMNodeInserted asynchronous
  asapQueue,
      asapTimer = 0,


  // internal flags
  V0 = REGISTER_ELEMENT in document && !/^force-all/.test(polyfill.type),
      setListener = true,
      justSetup = false,
      doesNotSupportDOMAttrModified = true,
      dropDomContentLoaded = true,


  // needed for the innerHTML helper
  notFromInnerHTMLHelper = true,


  // optionally defined later on
  onSubtreeModified,
      callDOMAttrModified,
      getAttributesMirror,
      observer,
      observe,


  // based on setting prototype capability
  // will check proto or the expando attribute
  // in order to setup the node once
  patchIfNotAlready,
      patch;

  // only if needed
  if (!V0) {

    if (sPO || hasProto) {
      patchIfNotAlready = function patchIfNotAlready(node, proto) {
        if (!iPO.call(proto, node)) {
          setupNode(node, proto);
        }
      };
      patch = setupNode;
    } else {
      patchIfNotAlready = function patchIfNotAlready(node, proto) {
        if (!node[EXPANDO_UID]) {
          node[EXPANDO_UID] = Object(true);
          setupNode(node, proto);
        }
      };
      patch = patchIfNotAlready;
    }

    if (IE8) {
      doesNotSupportDOMAttrModified = false;
      (function () {
        var descriptor = gOPD(HTMLElementPrototype, ADD_EVENT_LISTENER),
            addEventListener = descriptor.value,
            patchedRemoveAttribute = function patchedRemoveAttribute(name) {
          var e = new CustomEvent(DOM_ATTR_MODIFIED, { bubbles: true });
          e.attrName = name;
          e.prevValue = getAttribute.call(this, name);
          e.newValue = null;
          e[REMOVAL] = e.attrChange = 2;
          removeAttribute.call(this, name);
          dispatchEvent.call(this, e);
        },
            patchedSetAttribute = function patchedSetAttribute(name, value) {
          var had = hasAttribute.call(this, name),
              old = had && getAttribute.call(this, name),
              e = new CustomEvent(DOM_ATTR_MODIFIED, { bubbles: true });
          setAttribute.call(this, name, value);
          e.attrName = name;
          e.prevValue = had ? old : null;
          e.newValue = value;
          if (had) {
            e[MODIFICATION] = e.attrChange = 1;
          } else {
            e[ADDITION] = e.attrChange = 0;
          }
          dispatchEvent.call(this, e);
        },
            onPropertyChange = function onPropertyChange(e) {
          // jshint eqnull:true
          var node = e.currentTarget,
              superSecret = node[EXPANDO_UID],
              propertyName = e.propertyName,
              event;
          if (superSecret.hasOwnProperty(propertyName)) {
            superSecret = superSecret[propertyName];
            event = new CustomEvent(DOM_ATTR_MODIFIED, { bubbles: true });
            event.attrName = superSecret.name;
            event.prevValue = superSecret.value || null;
            event.newValue = superSecret.value = node[propertyName] || null;
            if (event.prevValue == null) {
              event[ADDITION] = event.attrChange = 0;
            } else {
              event[MODIFICATION] = event.attrChange = 1;
            }
            dispatchEvent.call(node, event);
          }
        };
        descriptor.value = function (type, handler, capture) {
          if (type === DOM_ATTR_MODIFIED && this[ATTRIBUTE_CHANGED_CALLBACK] && this.setAttribute !== patchedSetAttribute) {
            this[EXPANDO_UID] = {
              className: {
                name: 'class',
                value: this.className
              }
            };
            this.setAttribute = patchedSetAttribute;
            this.removeAttribute = patchedRemoveAttribute;
            addEventListener.call(this, 'propertychange', onPropertyChange);
          }
          addEventListener.call(this, type, handler, capture);
        };
        defineProperty(HTMLElementPrototype, ADD_EVENT_LISTENER, descriptor);
      })();
    } else if (!MutationObserver) {
      documentElement[ADD_EVENT_LISTENER](DOM_ATTR_MODIFIED, DOMAttrModified);
      documentElement.setAttribute(EXPANDO_UID, 1);
      documentElement.removeAttribute(EXPANDO_UID);
      if (doesNotSupportDOMAttrModified) {
        onSubtreeModified = function onSubtreeModified(e) {
          var node = this,
              oldAttributes,
              newAttributes,
              key;
          if (node === e.target) {
            oldAttributes = node[EXPANDO_UID];
            node[EXPANDO_UID] = newAttributes = getAttributesMirror(node);
            for (key in newAttributes) {
              if (!(key in oldAttributes)) {
                // attribute was added
                return callDOMAttrModified(0, node, key, oldAttributes[key], newAttributes[key], ADDITION);
              } else if (newAttributes[key] !== oldAttributes[key]) {
                // attribute was changed
                return callDOMAttrModified(1, node, key, oldAttributes[key], newAttributes[key], MODIFICATION);
              }
            }
            // checking if it has been removed
            for (key in oldAttributes) {
              if (!(key in newAttributes)) {
                // attribute removed
                return callDOMAttrModified(2, node, key, oldAttributes[key], newAttributes[key], REMOVAL);
              }
            }
          }
        };
        callDOMAttrModified = function callDOMAttrModified(attrChange, currentTarget, attrName, prevValue, newValue, action) {
          var e = {
            attrChange: attrChange,
            currentTarget: currentTarget,
            attrName: attrName,
            prevValue: prevValue,
            newValue: newValue
          };
          e[action] = attrChange;
          onDOMAttrModified(e);
        };
        getAttributesMirror = function getAttributesMirror(node) {
          for (var attr, name, result = {}, attributes = node.attributes, i = 0, length = attributes.length; i < length; i++) {
            attr = attributes[i];
            name = attr.name;
            if (name !== 'setAttribute') {
              result[name] = attr.value;
            }
          }
          return result;
        };
      }
    }

    // set as enumerable, writable and configurable
    document[REGISTER_ELEMENT] = function registerElement(type, options) {
      upperType = type.toUpperCase();
      if (setListener) {
        // only first time document.registerElement is used
        // we need to set this listener
        // setting it by default might slow down for no reason
        setListener = false;
        if (MutationObserver) {
          observer = function (attached, detached) {
            function checkEmAll(list, callback) {
              for (var i = 0, length = list.length; i < length; callback(list[i++])) {}
            }
            return new MutationObserver(function (records) {
              for (var current, node, newValue, i = 0, length = records.length; i < length; i++) {
                current = records[i];
                if (current.type === 'childList') {
                  checkEmAll(current.addedNodes, attached);
                  checkEmAll(current.removedNodes, detached);
                } else {
                  node = current.target;
                  if (notFromInnerHTMLHelper && node[ATTRIBUTE_CHANGED_CALLBACK] && current.attributeName !== 'style') {
                    newValue = getAttribute.call(node, current.attributeName);
                    if (newValue !== current.oldValue) {
                      node[ATTRIBUTE_CHANGED_CALLBACK](current.attributeName, current.oldValue, newValue);
                    }
                  }
                }
              }
            });
          }(executeAction(ATTACHED), executeAction(DETACHED));
          observe = function observe(node) {
            observer.observe(node, {
              childList: true,
              subtree: true
            });
            return node;
          };
          observe(document);
          if (attachShadow) {
            HTMLElementPrototype.attachShadow = function () {
              return observe(attachShadow.apply(this, arguments));
            };
          }
        } else {
          asapQueue = [];
          document[ADD_EVENT_LISTENER]('DOMNodeInserted', onDOMNode(ATTACHED));
          document[ADD_EVENT_LISTENER]('DOMNodeRemoved', onDOMNode(DETACHED));
        }

        document[ADD_EVENT_LISTENER](DOM_CONTENT_LOADED, onReadyStateChange);
        document[ADD_EVENT_LISTENER]('readystatechange', onReadyStateChange);

        HTMLElementPrototype.cloneNode = function (deep) {
          var node = cloneNode.call(this, !!deep),
              i = getTypeIndex(node);
          if (-1 < i) patch(node, protos[i]);
          if (deep && query.length) loopAndSetup(node.querySelectorAll(query));
          return node;
        };
      }

      if (justSetup) return justSetup = false;

      if (-2 < indexOf.call(types, PREFIX_IS + upperType) + indexOf.call(types, PREFIX_TAG + upperType)) {
        throwTypeError(type);
      }

      if (!validName.test(upperType) || -1 < indexOf.call(invalidNames, upperType)) {
        throw new Error('The type ' + type + ' is invalid');
      }

      var constructor = function constructor() {
        return extending ? document.createElement(nodeName, upperType) : document.createElement(nodeName);
      },
          opt = options || OP,
          extending = hOP.call(opt, EXTENDS),
          nodeName = extending ? options[EXTENDS].toUpperCase() : upperType,
          upperType,
          i;

      if (extending && -1 < indexOf.call(types, PREFIX_TAG + nodeName)) {
        throwTypeError(nodeName);
      }

      i = types.push((extending ? PREFIX_IS : PREFIX_TAG) + upperType) - 1;

      query = query.concat(query.length ? ',' : '', extending ? nodeName + '[is="' + type.toLowerCase() + '"]' : nodeName);

      constructor.prototype = protos[i] = hOP.call(opt, 'prototype') ? opt.prototype : create(HTMLElementPrototype);

      if (query.length) loopAndVerify(document.querySelectorAll(query), ATTACHED);

      return constructor;
    };

    document.createElement = patchedCreateElement = function patchedCreateElement(localName, typeExtension) {
      var is = getIs(typeExtension),
          node = is ? createElement.call(document, localName, secondArgument(is)) : createElement.call(document, localName),
          name = '' + localName,
          i = indexOf.call(types, (is ? PREFIX_IS : PREFIX_TAG) + (is || name).toUpperCase()),
          setup = -1 < i;
      if (is) {
        node.setAttribute('is', is = is.toLowerCase());
        if (setup) {
          setup = isInQSA(name.toUpperCase(), is);
        }
      }
      notFromInnerHTMLHelper = !document.createElement.innerHTMLHelper;
      if (setup) patch(node, protos[i]);
      return node;
    };
  }

  function ASAP() {
    var queue = asapQueue.splice(0, asapQueue.length);
    asapTimer = 0;
    while (queue.length) {
      queue.shift().call(null, queue.shift());
    }
  }

  function loopAndVerify(list, action) {
    for (var i = 0, length = list.length; i < length; i++) {
      verifyAndSetupAndAction(list[i], action);
    }
  }

  function loopAndSetup(list) {
    for (var i = 0, length = list.length, node; i < length; i++) {
      node = list[i];
      patch(node, protos[getTypeIndex(node)]);
    }
  }

  function executeAction(action) {
    return function (node) {
      if (isValidNode(node)) {
        verifyAndSetupAndAction(node, action);
        if (query.length) loopAndVerify(node.querySelectorAll(query), action);
      }
    };
  }

  function getTypeIndex(target) {
    var is = getAttribute.call(target, 'is'),
        nodeName = target.nodeName.toUpperCase(),
        i = indexOf.call(types, is ? PREFIX_IS + is.toUpperCase() : PREFIX_TAG + nodeName);
    return is && -1 < i && !isInQSA(nodeName, is) ? -1 : i;
  }

  function isInQSA(name, type) {
    return -1 < query.indexOf(name + '[is="' + type + '"]');
  }

  function onDOMAttrModified(e) {
    var node = e.currentTarget,
        attrChange = e.attrChange,
        attrName = e.attrName,
        target = e.target,
        addition = e[ADDITION] || 2,
        removal = e[REMOVAL] || 3;
    if (notFromInnerHTMLHelper && (!target || target === node) && node[ATTRIBUTE_CHANGED_CALLBACK] && attrName !== 'style' && (e.prevValue !== e.newValue ||
    // IE9, IE10, and Opera 12 gotcha
    e.newValue === '' && (attrChange === addition || attrChange === removal))) {
      node[ATTRIBUTE_CHANGED_CALLBACK](attrName, attrChange === addition ? null : e.prevValue, attrChange === removal ? null : e.newValue);
    }
  }

  function onDOMNode(action) {
    var executor = executeAction(action);
    return function (e) {
      asapQueue.push(executor, e.target);
      if (asapTimer) clearTimeout(asapTimer);
      asapTimer = setTimeout(ASAP, 1);
    };
  }

  function onReadyStateChange(e) {
    if (dropDomContentLoaded) {
      dropDomContentLoaded = false;
      e.currentTarget.removeEventListener(DOM_CONTENT_LOADED, onReadyStateChange);
    }
    if (query.length) loopAndVerify((e.target || document).querySelectorAll(query), e.detail === DETACHED ? DETACHED : ATTACHED);
    if (IE8) purge();
  }

  function patchedSetAttribute(name, value) {
    // jshint validthis:true
    var self = this;
    setAttribute.call(self, name, value);
    onSubtreeModified.call(self, { target: self });
  }

  function setupNode(node, proto) {
    setPrototype(node, proto);
    if (observer) {
      observer.observe(node, attributesObserver);
    } else {
      if (doesNotSupportDOMAttrModified) {
        node.setAttribute = patchedSetAttribute;
        node[EXPANDO_UID] = getAttributesMirror(node);
        node[ADD_EVENT_LISTENER](DOM_SUBTREE_MODIFIED, onSubtreeModified);
      }
      node[ADD_EVENT_LISTENER](DOM_ATTR_MODIFIED, onDOMAttrModified);
    }
    if (node[CREATED_CALLBACK] && notFromInnerHTMLHelper) {
      node.created = true;
      node[CREATED_CALLBACK]();
      node.created = false;
    }
  }

  function purge() {
    for (var node, i = 0, length = targets.length; i < length; i++) {
      node = targets[i];
      if (!documentElement.contains(node)) {
        length--;
        targets.splice(i--, 1);
        verifyAndSetupAndAction(node, DETACHED);
      }
    }
  }

  function throwTypeError(type) {
    throw new Error('A ' + type + ' type is already registered');
  }

  function verifyAndSetupAndAction(node, action) {
    var fn,
        i = getTypeIndex(node),
        counterAction;
    if (-1 < i) {
      patchIfNotAlready(node, protos[i]);
      i = 0;
      if (action === ATTACHED && !node[ATTACHED]) {
        node[DETACHED] = false;
        node[ATTACHED] = true;
        counterAction = 'connected';
        i = 1;
        if (IE8 && indexOf.call(targets, node) < 0) {
          targets.push(node);
        }
      } else if (action === DETACHED && !node[DETACHED]) {
        node[ATTACHED] = false;
        node[DETACHED] = true;
        counterAction = 'disconnected';
        i = 1;
      }
      if (i && (fn = node[action + CALLBACK] || node[counterAction + CALLBACK])) fn.call(node);
    }
  }

  // V1 in da House!
  function CustomElementRegistry() {}

  CustomElementRegistry.prototype = {
    constructor: CustomElementRegistry,
    // a workaround for the stubborn WebKit
    define: usableCustomElements ? function (name, Class, options) {
      if (options) {
        CERDefine(name, Class, options);
      } else {
        var NAME = name.toUpperCase();
        constructors[NAME] = {
          constructor: Class,
          create: [NAME]
        };
        nodeNames.set(Class, NAME);
        customElements.define(name, Class);
      }
    } : CERDefine,
    get: usableCustomElements ? function (name) {
      return customElements.get(name) || get(name);
    } : get,
    whenDefined: usableCustomElements ? function (name) {
      return Promise.race([customElements.whenDefined(name), whenDefined(name)]);
    } : whenDefined
  };

  function CERDefine(name, Class, options) {
    var is = options && options[EXTENDS] || '',
        CProto = Class.prototype,
        proto = create(CProto),
        attributes = Class.observedAttributes || empty,
        definition = { prototype: proto };
    // TODO: is this needed at all since it's inherited?
    // defineProperty(proto, 'constructor', {value: Class});
    safeProperty(proto, CREATED_CALLBACK, {
      value: function value() {
        if (justCreated) justCreated = false;else if (!this[DRECEV1]) {
          this[DRECEV1] = true;
          new Class(this);
          if (CProto[CREATED_CALLBACK]) CProto[CREATED_CALLBACK].call(this);
          var info = constructors[nodeNames.get(Class)];
          if (!usableCustomElements || info.create.length > 1) {
            notifyAttributes(this);
          }
        }
      }
    });
    safeProperty(proto, ATTRIBUTE_CHANGED_CALLBACK, {
      value: function value(name) {
        if (-1 < indexOf.call(attributes, name)) CProto[ATTRIBUTE_CHANGED_CALLBACK].apply(this, arguments);
      }
    });
    if (CProto[CONNECTED_CALLBACK]) {
      safeProperty(proto, ATTACHED_CALLBACK, {
        value: CProto[CONNECTED_CALLBACK]
      });
    }
    if (CProto[DISCONNECTED_CALLBACK]) {
      safeProperty(proto, DETACHED_CALLBACK, {
        value: CProto[DISCONNECTED_CALLBACK]
      });
    }
    if (is) definition[EXTENDS] = is;
    name = name.toUpperCase();
    constructors[name] = {
      constructor: Class,
      create: is ? [is, secondArgument(name)] : [name]
    };
    nodeNames.set(Class, name);
    document[REGISTER_ELEMENT](name.toLowerCase(), definition);
    whenDefined(name);
    waitingList[name].r();
  }

  function get(name) {
    var info = constructors[name.toUpperCase()];
    return info && info.constructor;
  }

  function getIs(options) {
    return typeof options === 'string' ? options : options && options.is || '';
  }

  function notifyAttributes(self) {
    var callback = self[ATTRIBUTE_CHANGED_CALLBACK],
        attributes = callback ? self.attributes : empty,
        i = attributes.length,
        attribute;
    while (i--) {
      attribute = attributes[i]; // || attributes.item(i);
      callback.call(self, attribute.name || attribute.nodeName, null, attribute.value || attribute.nodeValue);
    }
  }

  function whenDefined(name) {
    name = name.toUpperCase();
    if (!(name in waitingList)) {
      waitingList[name] = {};
      waitingList[name].p = new Promise(function (resolve) {
        waitingList[name].r = resolve;
      });
    }
    return waitingList[name].p;
  }

  function polyfillV1() {
    if (customElements) delete window.customElements;
    defineProperty(window, 'customElements', {
      configurable: true,
      value: new CustomElementRegistry()
    });
    defineProperty(window, 'CustomElementRegistry', {
      configurable: true,
      value: CustomElementRegistry
    });
    for (var patchClass = function patchClass(name) {
      var Class = window[name];
      if (Class) {
        window[name] = function CustomElementsV1(self) {
          var info, isNative;
          if (!self) self = this;
          if (!self[DRECEV1]) {
            justCreated = true;
            info = constructors[nodeNames.get(self.constructor)];
            isNative = usableCustomElements && info.create.length === 1;
            self = isNative ? Reflect.construct(Class, empty, info.constructor) : document.createElement.apply(document, info.create);
            self[DRECEV1] = true;
            justCreated = false;
            if (!isNative) notifyAttributes(self);
          }
          return self;
        };
        window[name].prototype = Class.prototype;
        try {
          Class.prototype.constructor = window[name];
        } catch (WebKit) {
          fixGetClass = true;
          defineProperty(Class, DRECEV1, { value: window[name] });
        }
      }
    }, Classes = htmlClass.get(/^HTML[A-Z]*[a-z]/), i = Classes.length; i--; patchClass(Classes[i])) {}
    document.createElement = function (name, options) {
      var is = getIs(options);
      return is ? patchedCreateElement.call(this, name, secondArgument(is)) : patchedCreateElement.call(this, name);
    };
    if (!V0) {
      justSetup = true;
      document[REGISTER_ELEMENT]('');
    }
  }

  // if customElements is not there at all
  if (!customElements || /^force/.test(polyfill.type)) polyfillV1();else if (!polyfill.noBuiltIn) {
    // if available test extends work as expected
    try {
      (function (DRE, options, name) {
        options[EXTENDS] = 'a';
        DRE.prototype = create(HTMLAnchorElement.prototype);
        DRE.prototype.constructor = DRE;
        window.customElements.define(name, DRE, options);
        if (getAttribute.call(document.createElement('a', { is: name }), 'is') !== name || usableCustomElements && getAttribute.call(new DRE(), 'is') !== name) {
          throw options;
        }
      })(function DRE() {
        return Reflect.construct(HTMLAnchorElement, [], DRE);
      }, {}, 'document-register-element-a');
    } catch (o_O) {
      // or force the polyfill if not
      // and keep internal original reference
      polyfillV1();
    }
  }

  // FireFox only issue
  if (!polyfill.noBuiltIn) {
    try {
      createElement.call(document, 'a', 'a');
    } catch (FireFox) {
      secondArgument = function secondArgument(is) {
        return { is: is.toLowerCase() };
      };
    }
  }
}

module.exports = installCustomElements;

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _typeof2 = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/**
  * vue-custom-element v1.4.1
  * (c) 2017 Karol Fabjańczuk
  * @license MIT
  */
/**
 * ES6 Object.getPrototypeOf Polyfill
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/setPrototypeOf
 */

Object.setPrototypeOf = Object.setPrototypeOf || setPrototypeOf;

function setPrototypeOf(obj, proto) {
  obj.__proto__ = proto;
  return obj;
}

var setPrototypeOf_1 = setPrototypeOf.bind(Object);

function isES2015() {
  if (typeof Symbol === 'undefined' || typeof Reflect === 'undefined') return false;

  return true;
}

var isES2015$1 = isES2015();

var _createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];descriptor.enumerable = descriptor.enumerable || false;descriptor.configurable = true;if ("value" in descriptor) descriptor.writable = true;Object.defineProperty(target, descriptor.key, descriptor);
    }
  }return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);if (staticProps) defineProperties(Constructor, staticProps);return Constructor;
  };
}();

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _possibleConstructorReturn(self, call) {
  if (!self) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }return call && ((typeof call === 'undefined' ? 'undefined' : _typeof2(call)) === "object" || typeof call === "function") ? call : self;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function, not " + (typeof superClass === 'undefined' ? 'undefined' : _typeof2(superClass)));
  }subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } });if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
}

function _CustomElement() {
  return Reflect.construct(HTMLElement, [], this.__proto__.constructor);
}

Object.setPrototypeOf(_CustomElement.prototype, HTMLElement.prototype);
Object.setPrototypeOf(_CustomElement, HTMLElement);
function registerCustomElement(tag) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

  if (typeof customElements === 'undefined') {
    return;
  }

  function constructorCallback() {
    if (options.shadow === true && HTMLElement.prototype.attachShadow) {
      this.attachShadow({ mode: 'open' });
    }
    typeof options.constructorCallback === 'function' && options.constructorCallback.call(this);
  }
  function connectedCallback() {
    typeof options.connectedCallback === 'function' && options.connectedCallback.call(this);
  }

  function disconnectedCallback() {
    typeof options.disconnectedCallback === 'function' && options.disconnectedCallback.call(this);
  }

  function attributeChangedCallback(name, oldValue, value) {
    typeof options.attributeChangedCallback === 'function' && options.attributeChangedCallback.call(this, name, oldValue, value);
  }

  if (isES2015$1) {
    var CustomElement = function (_CustomElement2) {
      _inherits(CustomElement, _CustomElement2);

      function CustomElement(self) {
        var _ret;

        _classCallCheck(this, CustomElement);

        var _this = _possibleConstructorReturn(this, (CustomElement.__proto__ || Object.getPrototypeOf(CustomElement)).call(this));

        var me = self ? HTMLElement.call(self) : _this;

        constructorCallback.call(me);
        return _ret = me, _possibleConstructorReturn(_this, _ret);
      }

      _createClass(CustomElement, null, [{
        key: 'observedAttributes',
        get: function get() {
          return options.observedAttributes || [];
        }
      }]);

      return CustomElement;
    }(_CustomElement);

    CustomElement.prototype.connectedCallback = connectedCallback;
    CustomElement.prototype.disconnectedCallback = disconnectedCallback;
    CustomElement.prototype.attributeChangedCallback = attributeChangedCallback;

    customElements.define(tag, CustomElement);
    return CustomElement;
  } else {
    var _CustomElement3 = function _CustomElement3(self) {
      var me = self ? HTMLElement.call(self) : this;

      constructorCallback.call(me);
      return me;
    };

    _CustomElement3.observedAttributes = options.observedAttributes || [];

    _CustomElement3.prototype = Object.create(HTMLElement.prototype, {
      constructor: {
        configurable: true,
        writable: true,
        value: _CustomElement3
      }
    });

    _CustomElement3.prototype.connectedCallback = connectedCallback;
    _CustomElement3.prototype.disconnectedCallback = disconnectedCallback;
    _CustomElement3.prototype.attributeChangedCallback = attributeChangedCallback;

    customElements.define(tag, _CustomElement3);
    return _CustomElement3;
  }
}

var camelizeRE = /-(\w)/g;
var camelize = function camelize(str) {
  return str.replace(camelizeRE, function (_, c) {
    return c ? c.toUpperCase() : '';
  });
};
var hyphenateRE = /([^-])([A-Z])/g;
var hyphenate = function hyphenate(str) {
  return str.replace(hyphenateRE, '$1-$2').replace(hyphenateRE, '$1-$2').toLowerCase();
};

function toArray(list) {
  var start = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;

  var i = list.length - start;
  var ret = new Array(i);
  while (i--) {
    ret[i] = list[i + start];
  }
  return ret;
}

var _typeof = typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol" ? function (obj) {
  return typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
} : function (obj) {
  return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj === 'undefined' ? 'undefined' : _typeof2(obj);
};

function convertAttributeValue(value) {
  var propsValue = value;
  var isBoolean = ['true', 'false'].indexOf(value) > -1;
  var valueParsed = parseFloat(propsValue, 10);
  var isNumber = !isNaN(valueParsed) && isFinite(propsValue);

  if (isBoolean) {
    propsValue = propsValue === 'true';
  } else if (isNumber) {
    propsValue = valueParsed;
  }

  return propsValue;
}

function extractProps(collection, props) {
  if (collection && collection.length) {
    collection.forEach(function (prop) {
      var camelCaseProp = camelize(prop);
      props.camelCase.indexOf(camelCaseProp) === -1 && props.camelCase.push(camelCaseProp);
    });
  } else if (collection && (typeof collection === 'undefined' ? 'undefined' : _typeof(collection)) === 'object') {
    for (var prop in collection) {
      var camelCaseProp = camelize(prop);
      props.camelCase.indexOf(camelCaseProp) === -1 && props.camelCase.push(camelCaseProp);
    }
  }
}

function getProps() {
  var componentDefinition = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

  var props = {
    camelCase: [],
    hyphenate: []
  };

  if (componentDefinition.mixins) {
    componentDefinition.mixins.forEach(function (mixin) {
      extractProps(mixin.props, props);
    });
  }

  if (componentDefinition.extends && componentDefinition.extends.props) {
    var parentProps = componentDefinition.extends.props;

    extractProps(parentProps, props);
  }

  extractProps(componentDefinition.props, props);

  props.camelCase.forEach(function (prop) {
    props.hyphenate.push(hyphenate(prop));
  });

  return props;
}

function reactiveProps(element, props) {
  props.camelCase.forEach(function (name, index) {
    Object.defineProperty(element, name, {
      get: function get() {
        return this.__vue_custom_element__[name];
      },
      set: function set(value) {
        if (((typeof value === 'undefined' ? 'undefined' : _typeof(value)) === 'object' || typeof value === 'function') && this.__vue_custom_element__) {
          var propName = props.camelCase[index];
          this.__vue_custom_element__[propName] = value;
        } else {
          this.setAttribute(props.hyphenate[index], convertAttributeValue(value));
        }
      }
    });
  });
}

function getPropsData(element, componentDefinition, props) {
  var propsData = componentDefinition.propsData || {};

  props.hyphenate.forEach(function (name, index) {
    var elementAttribute = element.attributes[name];
    var propCamelCase = props.camelCase[index];

    if ((typeof elementAttribute === 'undefined' ? 'undefined' : _typeof(elementAttribute)) === 'object' && !(elementAttribute instanceof Attr)) {
      propsData[propCamelCase] = elementAttribute;
    } else if (elementAttribute instanceof Attr && elementAttribute.value) {
      propsData[propCamelCase] = convertAttributeValue(elementAttribute.value);
    }
  });

  return propsData;
}

function getAttributes(children) {
  var attributes = {};

  toArray(children.attributes).forEach(function (attribute) {
    attributes[attribute.nodeName === 'vue-slot' ? 'slot' : attribute.nodeName] = attribute.nodeValue;
  });

  return attributes;
}

function getSlots() {
  var children = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
  var createElement = arguments[1];

  var slots = [];
  toArray(children).forEach(function (child) {
    if (child.nodeName === '#text') {
      if (child.nodeValue.trim()) {
        slots.push(createElement('span', child.nodeValue));
      }
    } else {
      var attributes = getAttributes(child);
      var elementOptions = {
        attrs: attributes,
        domProps: {
          innerHTML: child.innerHTML
        }
      };

      if (attributes.slot) {
        elementOptions.slot = attributes.slot;
        attributes.slot = undefined;
      }

      slots.push(createElement(child.tagName, elementOptions));
    }
  });

  return slots;
}

function customEvent(eventName, detail) {
  var params = { bubbles: false, cancelable: false, detail: detail };
  var event = void 0;
  if (typeof window.CustomEvent === 'function') {
    event = new CustomEvent(eventName, params);
  } else {
    event = document.createEvent('CustomEvent');
    event.initCustomEvent(eventName, params.bubbles, params.cancelable, params.detail);
  }
  return event;
}

function customEmit(element, eventName) {
  for (var _len = arguments.length, args = Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
    args[_key - 2] = arguments[_key];
  }

  var event = customEvent(eventName, [].concat(args));
  element.dispatchEvent(event);
}

function createVueInstance(element, Vue, componentDefinition, props, options) {
  if (!element.__vue_custom_element__) {
    var beforeCreate = function beforeCreate() {
      this.$emit = function emit() {
        var _proto__$$emit;

        for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }

        customEmit.apply(undefined, [element].concat(args));
        this.__proto__ && (_proto__$$emit = this.__proto__.$emit).call.apply(_proto__$$emit, [this].concat(args));
      };
    };

    var ComponentDefinition = Vue.util.extend({}, componentDefinition);
    var propsData = getPropsData(element, ComponentDefinition, props);
    var vueVersion = Vue.version && parseInt(Vue.version.split('.')[0], 10) || 0;

    if (ComponentDefinition._compiled) {
      var ctorOptions = {};
      if (ComponentDefinition._Ctor) {
        ctorOptions = ComponentDefinition._Ctor[0].options;
      }
      ComponentDefinition.beforeCreate = ComponentDefinition.beforeCreate || [];
      ComponentDefinition.beforeCreate.push(beforeCreate);
      ctorOptions.beforeCreate = ComponentDefinition.beforeCreate;
    } else {
      ComponentDefinition.beforeCreate = beforeCreate;
    }

    var rootElement = void 0;

    if (vueVersion >= 2) {
      var elementOriginalChildren = element.cloneNode(true).childNodes;
      rootElement = {
        propsData: propsData,
        props: props.camelCase,
        computed: {
          reactiveProps: function reactiveProps$$1() {
            var _this = this;

            var reactivePropsList = {};
            props.camelCase.forEach(function (prop) {
              reactivePropsList[prop] = _this[prop];
            });

            return reactivePropsList;
          }
        },
        render: function render(createElement) {
          var data = {
            props: this.reactiveProps
          };

          return createElement(ComponentDefinition, data, getSlots(elementOriginalChildren, createElement));
        }
      };
    } else if (vueVersion === 1) {
      rootElement = ComponentDefinition;
      rootElement.propsData = propsData;
    } else {
      rootElement = ComponentDefinition;
      var propsWithDefault = {};
      Object.keys(propsData).forEach(function (prop) {
        propsWithDefault[prop] = { default: propsData[prop] };
      });
      rootElement.props = propsWithDefault;
    }

    var elementInnerHtml = vueVersion >= 2 ? '<div></div>' : ('<div>' + element.innerHTML + '</div>').replace(/vue-slot=/g, 'slot=');
    if (options.shadow && element.shadowRoot) {
      element.shadowRoot.innerHTML = elementInnerHtml;
      rootElement.el = element.shadowRoot.children[0];
    } else {
      element.innerHTML = elementInnerHtml;
      rootElement.el = element.children[0];
    }

    reactiveProps(element, props);

    element.__vue_custom_element__ = new Vue(rootElement);
    if (options.shadow && options.shadowCss && element.shadowRoot) {
      var style = document.createElement('style');
      style.type = 'text/css';
      style.appendChild(document.createTextNode(options.shadowCss));

      element.shadowRoot.appendChild(style);
    }
    element.removeAttribute('vce-cloak');
    element.setAttribute('vce-ready', '');
    customEmit(element, 'vce-ready');
  }
}

function install(Vue) {
  Vue.customElement = function vueCustomElement(tag, componentDefinition) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

    var isAsyncComponent = typeof componentDefinition === 'function';
    var optionsProps = isAsyncComponent && { props: options.props || [] };
    var props = getProps(isAsyncComponent ? optionsProps : componentDefinition);

    var CustomElement = registerCustomElement(tag, {
      constructorCallback: function constructorCallback() {
        typeof options.constructorCallback === 'function' && options.constructorCallback.call(this);
      },
      connectedCallback: function connectedCallback() {
        var _this = this;

        var asyncComponentPromise = isAsyncComponent && componentDefinition();
        var isAsyncComponentPromise = asyncComponentPromise && asyncComponentPromise.then && typeof asyncComponentPromise.then === 'function';

        if (isAsyncComponent && !isAsyncComponentPromise) {
          throw new Error('Async component ' + tag + ' do not returns Promise');
        }
        if (!this.__detached__) {
          if (isAsyncComponentPromise) {
            asyncComponentPromise.then(function (lazyLoadedComponent) {
              var lazyLoadedComponentProps = getProps(lazyLoadedComponent);
              createVueInstance(_this, Vue, lazyLoadedComponent, lazyLoadedComponentProps, options);
            });
          } else {
            createVueInstance(this, Vue, componentDefinition, props, options);
          }
        }

        this.__detached__ = false;
      },
      disconnectedCallback: function disconnectedCallback() {
        var _this2 = this;

        this.__detached__ = true;
        typeof options.disconnectedCallback === 'function' && options.disconnectedCallback.call(this);

        setTimeout(function () {
          if (_this2.__detached__ && _this2.__vue_custom_element__) {
            _this2.__vue_custom_element__.$destroy(true);
          }
        }, options.destroyTimeout || 3000);
      },
      attributeChangedCallback: function attributeChangedCallback(name, oldValue, value) {
        if (this.__vue_custom_element__ && typeof value !== 'undefined') {
          var nameCamelCase = camelize(name);
          typeof options.attributeChangedCallback === 'function' && options.attributeChangedCallback.call(this, name, oldValue, value);
          this.__vue_custom_element__[nameCamelCase] = convertAttributeValue(value);
        }
      },

      observedAttributes: props.hyphenate,

      shadow: !!options.shadow && !!HTMLElement.prototype.attachShadow
    });

    return CustomElement;
  };
}

if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use(install);
  if (install.installed) {
    install.installed = false;
  }
}

exports.default = install;

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/**
 * vue-gettext v2.0.24
 * (c) 2017 Polyconseil
 * @license MIT
 */
(function (global, factory) {
  ( false ? 'undefined' : _typeof(exports)) === 'object' && typeof module !== 'undefined' ? module.exports = factory() :  true ? !(__WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.call(exports, __webpack_require__, exports, module)) :
				__WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)) : global.VueGettext = factory();
})(undefined, function () {
  'use strict';

  // Polyfill Object.assign for legacy browsers.
  // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/assign

  if (typeof Object.assign !== 'function') {
    (function () {
      Object.assign = function (target) {
        'use strict';

        var arguments$1 = arguments;

        var output;
        var index;
        var source;
        var nextKey;
        if (target === undefined || target === null) {
          throw new TypeError('Cannot convert undefined or null to object');
        }
        output = Object(target);
        for (index = 1; index < arguments.length; index++) {
          source = arguments$1[index];
          if (source !== undefined && source !== null) {
            for (nextKey in source) {
              if (source.hasOwnProperty(nextKey)) {
                output[nextKey] = source[nextKey];
              }
            }
          }
        }
        return output;
      };
    })();
  }

  /**
   * Plural Forms
   *
   * This is a list of the plural forms, as used by Gettext PO, that are appropriate to each language.
   * http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
   *
   * This is a replica of angular-gettext's plural.js
   * https://github.com/rubenv/angular-gettext/blob/master/src/plural.js
   */
  var plurals = {

    getTranslationIndex: function getTranslationIndex(languageCode, n) {

      n = parseInt(n);
      n = typeof n === 'number' && isNaN(n) ? 1 : n; // Fallback to singular.

      // Extract the ISO 639 language code. The ISO 639 standard defines
      // two-letter codes for many languages, and three-letter codes for
      // more rarely used languages.
      // https://www.gnu.org/software/gettext/manual/html_node/Language-Codes.html#Language-Codes
      if (languageCode.length > 2 && languageCode !== 'pt_BR') {
        languageCode = languageCode.split('_')[0];
      }

      switch (languageCode) {
        case 'ay': // Aymará
        case 'bo': // Tibetan
        case 'cgg': // Chiga
        case 'dz': // Dzongkha
        case 'fa': // Persian
        case 'id': // Indonesian
        case 'ja': // Japanese
        case 'jbo': // Lojban
        case 'ka': // Georgian
        case 'kk': // Kazakh
        case 'km': // Khmer
        case 'ko': // Korean
        case 'ky': // Kyrgyz
        case 'lo': // Lao
        case 'ms': // Malay
        case 'my': // Burmese
        case 'sah': // Yakut
        case 'su': // Sundanese
        case 'th': // Thai
        case 'tt': // Tatar
        case 'ug': // Uyghur
        case 'vi': // Vietnamese
        case 'wo': // Wolof
        case 'zh':
          // Chinese
          // 1 form
          return 0;
        case 'is':
          // Icelandic
          // 2 forms
          return n % 10 !== 1 || n % 100 === 11 ? 1 : 0;
        case 'jv':
          // Javanese
          // 2 forms
          return n !== 0 ? 1 : 0;
        case 'mk':
          // Macedonian
          // 2 forms
          return n === 1 || n % 10 === 1 ? 0 : 1;
        case 'ach': // Acholi
        case 'ak': // Akan
        case 'am': // Amharic
        case 'arn': // Mapudungun
        case 'br': // Breton
        case 'fil': // Filipino
        case 'fr': // French
        case 'gun': // Gun
        case 'ln': // Lingala
        case 'mfe': // Mauritian Creole
        case 'mg': // Malagasy
        case 'mi': // Maori
        case 'oc': // Occitan
        case 'pt_BR': // Brazilian Portuguese
        case 'tg': // Tajik
        case 'ti': // Tigrinya
        case 'tr': // Turkish
        case 'uz': // Uzbek
        case 'wa': // Walloon
        /* eslint-disable */
        /* Disable "Duplicate case label" because there are 2 forms of Chinese plurals */
        case 'zh':
          // Chinese
          /* eslint-enable */
          // 2 forms
          return n > 1 ? 1 : 0;
        case 'lv':
          // Latvian
          // 3 forms
          return n % 10 === 1 && n % 100 !== 11 ? 0 : n !== 0 ? 1 : 2;
        case 'lt':
          // Lithuanian
          // 3 forms
          return n % 10 === 1 && n % 100 !== 11 ? 0 : n % 10 >= 2 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2;
        case 'be': // Belarusian
        case 'bs': // Bosnian
        case 'hr': // Croatian
        case 'ru': // Russian
        case 'sr': // Serbian
        case 'uk':
          // Ukrainian
          // 3 forms
          return n % 10 === 1 && n % 100 !== 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2;
        case 'mnk':
          // Mandinka
          // 3 forms
          return n === 0 ? 0 : n === 1 ? 1 : 2;
        case 'ro':
          // Romanian
          // 3 forms
          return n === 1 ? 0 : n === 0 || n % 100 > 0 && n % 100 < 20 ? 1 : 2;
        case 'pl':
          // Polish
          // 3 forms
          return n === 1 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2;
        case 'cs': // Czech
        case 'sk':
          // Slovak
          // 3 forms
          return n === 1 ? 0 : n >= 2 && n <= 4 ? 1 : 2;
        case 'csb':
          // Kashubian
          // 3 forms
          return n === 1 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2;
        case 'sl':
          // Slovenian
          // 4 forms
          return n % 100 === 1 ? 0 : n % 100 === 2 ? 1 : n % 100 === 3 || n % 100 === 4 ? 2 : 3;
        case 'mt':
          // Maltese
          // 4 forms
          return n === 1 ? 0 : n === 0 || n % 100 > 1 && n % 100 < 11 ? 1 : n % 100 > 10 && n % 100 < 20 ? 2 : 3;
        case 'gd':
          // Scottish Gaelic
          // 4 forms
          return n === 1 || n === 11 ? 0 : n === 2 || n === 12 ? 1 : n > 2 && n < 20 ? 2 : 3;
        case 'cy':
          // Welsh
          // 4 forms
          return n === 1 ? 0 : n === 2 ? 1 : n !== 8 && n !== 11 ? 2 : 3;
        case 'kw':
          // Cornish
          // 4 forms
          return n === 1 ? 0 : n === 2 ? 1 : n === 3 ? 2 : 3;
        case 'ga':
          // Irish
          // 5 forms
          return n === 1 ? 0 : n === 2 ? 1 : n > 2 && n < 7 ? 2 : n > 6 && n < 11 ? 3 : 4;
        case 'ar':
          // Arabic
          // 6 forms
          return n === 0 ? 0 : n === 1 ? 1 : n === 2 ? 2 : n % 100 >= 3 && n % 100 <= 10 ? 3 : n % 100 >= 11 ? 4 : 5;
        default:
          // Everything else
          return n !== 1 ? 1 : 0;
      }
    }

  };

  // Ensure to always use the same Vue instance throughout the plugin.
  //
  // This was previously done in `index.js` using both named and default exports.
  // However, this currently must be kept in a separate file because we are using
  // Rollup to build the dist files and it has a drawback when using named and
  // default exports together, see:
  // https://github.com/rollup/rollup/blob/fca14d/src/utils/getExportMode.js#L27
  // https://github.com/rollup/rollup/wiki/JavaScript-API#exports
  //
  // If we had kept named and default exports in `index.js`, a user would have to
  // do something like this to access the default export: GetTextPlugin['default']

  var _Vue;

  function shareVueInstance(Vue) {
    _Vue = Vue;
  }

  var translate = {

    /**
     * Get the translated string from the translation.json file generated by easygettext.
     *
     * @param {String} msgid - The translation key
     * @param {Number} n - The number to switch between singular and plural
     * @param {String} context - The translation key context
     * @param {String} defaultPlural - The default plural value (optional)
     * @param {String} language - The language ID (e.g. 'fr_FR' or 'en_US')
     *
     * @return {String} The translated string
     */
    getTranslation: function getTranslation(msgid, n, context, defaultPlural, language) {
      if (n === void 0) n = 1;
      if (context === void 0) context = null;
      if (defaultPlural === void 0) defaultPlural = null;
      if (language === void 0) language = _Vue.config.language;

      if (!msgid) {
        return ''; // Allow empty strings.
      }

      // `easygettext`'s `gettext-compile` generates a JSON version of a .po file based on its `Language` field.
      // But in this field, `ll_CC` combinations denoting a language’s main dialect are abbreviated as `ll`,
      // for example `de` is equivalent to `de_DE` (German as spoken in Germany).
      // See the `Language` section in https://www.gnu.org/software/gettext/manual/html_node/Header-Entry.html
      // So try `ll_CC` first, or the `ll` abbreviation which can be three-letter sometimes:
      // https://www.gnu.org/software/gettext/manual/html_node/Language-Codes.html#Language-Codes
      var translations = _Vue.$translations[language] || _Vue.$translations[language.split('_')[0]];

      if (!translations) {
        if (!_Vue.config.getTextPluginSilent) {
          console.warn("No translations found for " + language);
        }
        // Returns the untranslated string, singular or plural.
        return defaultPlural && plurals.getTranslationIndex(language, n) > 0 ? defaultPlural : msgid;
      }

      var translated = translations[msgid];

      // Sometimes msgid may not have the same number of spaces than its key. This could happen e.g. when using
      // new lines. See comments in the `created` hook of `component.js` and issue #15 for more information.
      if (!translated && /\s{2,}/g.test(msgid)) {
        Object.keys(translations).some(function (key) {
          if (key.replace(/\s{2,}/g, ' ') === msgid.trim().replace(/\s{2,}/g, ' ')) {
            translated = translations[key];
            return translated;
          }
        });
      }

      if (!translated) {
        if (!_Vue.config.getTextPluginSilent) {
          console.warn("Untranslated " + language + " key found:\n" + msgid);
        }
        // Returns the untranslated string, singular or plural.
        return defaultPlural && plurals.getTranslationIndex(language, n) > 0 ? defaultPlural : msgid;
      }

      if (context) {
        translated = translated[context];
      }

      if (typeof translated === 'string') {
        translated = [translated];
      }

      // Avoid a crash when a msgid exists with and without a context, see #32.
      if (!(translated instanceof Array) && translated.hasOwnProperty('')) {
        // As things currently stand, the void key means a void context for easygettext.
        translated = [translated['']];
      }

      return translated[plurals.getTranslationIndex(language, n)];
    },

    /**
     * Returns a string of the translation of the message.
     * Also makes the string discoverable by xgettext.
     *
     * @param {String} msgid - The translation key
     *
     * @return {String} The translated string
     */
    'gettext': function gettext(msgid) {
      return this.getTranslation(msgid);
    },

    /**
     * Returns a string of the translation for the given context.
     * Also makes the string discoverable by xgettext.
     *
     * @param {String} context - The context of the string to translate
     * @param {String} msgid - The translation key
     *
     * @return {String} The translated string
     */
    'pgettext': function pgettext(context, msgid) {
      return this.getTranslation(msgid, 1, context);
    },

    /**
     * Returns a string of the translation of either the singular or plural,
     * based on the number.
     * Also makes the string discoverable by xgettext.
     *
     * @param {String} msgid - The translation key
     * @param {String} plural - The plural form of the translation key
     * @param {Number} n - The number to switch between singular and plural
     *
     * @return {String} The translated string
     */
    'ngettext': function ngettext(msgid, plural, n) {
      return this.getTranslation(msgid, n, null, plural);
    },

    /**
     * Returns a string of the translation of either the singular or plural,
     * based on the number, for the given context.
     * Also makes the string discoverable by xgettext.
     *
     * @param {String} context - The context of the string to translate
     * @param {String} msgid - The translation key
     * @param {String} plural - The plural form of the translation key
     * @param {Number} n - The number to switch between singular and plural
     *
     * @return {String} The translated string
     */
    'npgettext': function npgettext(context, msgid, plural, n) {
      return this.getTranslation(msgid, n, context, plural);
    }

  };

  // UUID v4 generator (RFC4122 compliant).
  //
  // https://gist.github.com/jcxplorer/823878

  function uuid() {

    var uuid = '';
    var i;
    var random;

    for (i = 0; i < 32; i++) {
      random = Math.random() * 16 | 0;
      if (i === 8 || i === 12 || i === 16 || i === 20) {
        uuid += '-';
      }
      uuid += (i === 12 ? 4 : i === 16 ? random & 3 | 8 : random).toString(16);
    }

    return uuid;
  }

  /**
   * Translate content according to the current language.
   */
  var Component = {

    name: 'translate',

    created: function created() {

      this.msgid = ''; // Don't crash the app with an empty component, i.e.: <translate></translate>.

      // Store the raw uninterpolated string to translate.
      // This is currently done by looking inside a private attribute `_renderChildren` of the current
      // Vue instance's instantiation options.
      // However spaces introduced by newlines are not exactly the same between the HTML and the
      // content of `_renderChildren`, e.g. 6 spaces becomes 4 etc. See issue #15 for problems which
      // can arise with this.
      // I haven't (yet) found a better way to access the raw content of the component.
      if (this.$options._renderChildren) {
        if (this.$options._renderChildren[0].hasOwnProperty('text')) {
          this.msgid = this.$options._renderChildren[0].text.trim();
        } else {
          this.msgid = this.$options._renderChildren[0].trim();
        }
      }

      this.isPlural = this.translateN !== undefined && this.translatePlural !== undefined;
      if (!this.isPlural && (this.translateN || this.translatePlural)) {
        throw new Error("`translate-n` and `translate-plural` attributes must be used together: " + this.msgid + ".");
      }
    },

    props: {
      tag: {
        type: String,
        default: 'span'
      },
      // Always use v-bind for dynamically binding the `translateN` prop to data on the parent,
      // i.e.: `:translateN`.
      translateN: {
        type: Number,
        required: false
      },
      translatePlural: {
        type: String,
        required: false
      },
      translateContext: {
        type: String,
        required: false
      },
      translateParams: {
        type: Object,
        required: false
      },
      // `translateComment` is used exclusively by `easygettext`'s `gettext-extract`.
      translateComment: {
        type: String,
        required: false
      }
    },

    computed: {
      translation: function translation() {
        var translation = translate.getTranslation(this.msgid, this.translateN, this.translateContext, this.isPlural ? this.translatePlural : null, this.$language.current);

        var context = this.$parent;

        if (this.translateParams) {
          context = Object.assign({}, this.$parent, this.translateParams);
        }

        return this.$gettextInterpolate(translation, context);
      }
    },

    render: function render(createElement) {

      // Fix the problem with v-if, see #29.
      // Vue re-uses DOM elements for efficiency if they don't have a key attribute, see:
      // https://vuejs.org/v2/guide/conditional.html#Controlling-Reusable-Elements-with-key
      // https://vuejs.org/v2/api/#key
      if (!this.$vnode.key) {
        this.$vnode.key = uuid();
      }

      // The text must be wraped inside a root HTML element, so we use a <span> (by default).
      // https://github.com/vuejs/vue/blob/a4fcdb/src/compiler/parser/index.js#L209
      return createElement(this.tag, [this.translation]);
    }

  };

  /* Interpolation RegExp.
   *
   * Because interpolation inside attributes are deprecated in Vue 2 we have to
   * use another set of delimiters to be able to use `translate-plural` etc.
   * We use %{ } delimiters.
   *
   * /
   *   %\{                => Starting delimiter: `%{`
   *     (                => Start capture
   *       (?:.|\n)       => Non-capturing group: any character or newline
   *       +?             => One or more times (ungreedy)
   *     )                => End capture
   *   \}                 => Ending delimiter: `}`
   * /g                   => Global: don't return after first match
   */
  var INTERPOLATION_RE = /%\{((?:.|\n)+?)\}/g;

  var MUSTACHE_SYNTAX_RE = /\{\{((?:.|\n)+?)\}\}/g;

  /**
   * Evaluate a piece of template string containing %{ } placeholders.
   * E.g.: 'Hi %{ user.name }' => 'Hi Bob'
   *
   * This is a vm.$interpolate alternative for Vue 2.
   * https://vuejs.org/v2/guide/migration.html#vm-interpolate-removed
   *
   * @param {String} msgid - The translation key containing %{ } placeholders
   * @param {Object} context - An object whose elements are put in their corresponding placeholders
   *
   * @return {String} The interpolated string
   */
  var interpolate = function interpolate(msgid, context) {
    if (context === void 0) context = {};

    if (!_Vue.config.getTextPluginSilent && MUSTACHE_SYNTAX_RE.test(msgid)) {
      console.warn("Mustache syntax cannot be used with vue-gettext. Please use \"%{}\" instead of \"{{}}\" in: " + msgid);
    }

    var result = msgid.replace(INTERPOLATION_RE, function (match, token) {

      var expression = token.trim();
      var evaluated;

      function evalInContext(expression) {
        try {
          evaluated = eval('this.' + expression); // eslint-disable-line no-eval
        } catch (e) {
          // Ignore errors, because this function may be called recursively later.
        }
        if (evaluated === undefined) {
          if (this.$parent) {
            // Recursively climb the $parent chain to allow evaluation inside nested components, see #23 and #24.
            return evalInContext.call(this.$parent, expression);
          } else {
            console.warn("Cannot evaluate expression: " + expression);
            evaluated = expression;
          }
        }
        return evaluated;
      }

      return evalInContext.call(context, expression);
    });

    return result;
  };

  // Store this values as function attributes for easy access elsewhere to bypass a Rollup
  // weak point with `export`:
  // https://github.com/rollup/rollup/blob/fca14d/src/utils/getExportMode.js#L27
  interpolate.INTERPOLATION_RE = INTERPOLATION_RE;
  interpolate.INTERPOLATION_PREFIX = '%{';

  var updateTranslation = function updateTranslation(el, binding, vnode) {

    var attrs = vnode.data.attrs || {};
    var msgid = el.dataset.msgid;
    var translateContext = attrs['translate-context'];
    var translateN = attrs['translate-n'];
    var translatePlural = attrs['translate-plural'];
    var isPlural = translateN !== undefined && translatePlural !== undefined;
    var context = vnode.context;

    if (!isPlural && (translateN || translatePlural)) {
      throw new Error('`translate-n` and `translate-plural` attributes must be used together:' + msgid + '.');
    }

    if (!_Vue.config.getTextPluginSilent && attrs['translate-params']) {
      console.warn("`translate-params` is required as an expression for v-translate directive. Please change to `v-translate='params'`: " + msgid);
    }

    if (binding.value && _typeof(binding.value) === 'object') {
      context = Object.assign({}, vnode.context, binding.value);
    }

    var translation = translate.getTranslation(msgid, translateN, translateContext, isPlural ? translatePlural : null, el.dataset.currentLanguage);

    var msg = interpolate(translation, context);

    el.innerHTML = msg;
  };

  /**
   * A directive to translate content according to the current language.
   *
   * Use this directive instead of the component if you need to translate HTML content.
   * It's too tricky to support HTML content within the component because we cannot get the raw HTML to use as `msgid`.
   *
   * This directive has a similar interface to the <translate> component, supporting
   * `translate-comment`, `translate-context`, `translate-plural`, `translate-n`.
   *
   * `<p v-translate translate-comment='Good stuff'>This is <strong class='txt-primary'>Sparta</strong>!</p>`
   *
   * If you need interpolation, you must add an expression that outputs binding value that changes with each of the
   * context variable:
   * `<p v-translate="fullName + location">I am %{ fullName } and from %{ location }</p>`
   */
  var Directive = {

    bind: function bind(el, binding, vnode) {

      // Fix the problem with v-if, see #29.
      // Vue re-uses DOM elements for efficiency if they don't have a key attribute, see:
      // https://vuejs.org/v2/guide/conditional.html#Controlling-Reusable-Elements-with-key
      // https://vuejs.org/v2/api/#key
      if (!vnode.key) {
        vnode.key = uuid();
      }

      // Get the raw HTML and store it in the element's dataset (as advised in Vue's official guide).
      // Note: not trimming the content here as it should be picked up as-is by the extractor.
      var msgid = el.innerHTML;
      el.dataset.msgid = msgid;

      // Store the current language in the element's dataset.
      el.dataset.currentLanguage = _Vue.config.language;

      // Output a info in the console if an interpolation is required but no expression is provided.
      if (!_Vue.config.getTextPluginSilent) {
        var hasInterpolation = msgid.indexOf(interpolate.INTERPOLATION_PREFIX) !== -1;
        if (hasInterpolation && !binding.expression) {
          console.info("No expression is provided for change detection. The translation for this key will be static:\n" + msgid);
        }
      }

      updateTranslation(el, binding, vnode);
    },

    update: function update(el, binding, vnode) {

      var doUpdate = false;

      // Trigger an update if the language has changed.
      if (el.dataset.currentLanguage !== _Vue.config.language) {
        el.dataset.currentLanguage = _Vue.config.language;
        doUpdate = true;
      }

      // Trigger an update if an optional bound expression has changed.
      if (!doUpdate && binding.expression && binding.value !== binding.oldValue) {
        doUpdate = true;
      }

      if (doUpdate) {
        updateTranslation(el, binding, vnode);
      }
    }

  };

  var Config = function Config(Vue, languageVm, getTextPluginSilent) {

    /*
     * Adds a `language` property to `Vue.config` and makes it reactive:
     * Vue.config.language = 'fr_FR'
     */
    Object.defineProperty(Vue.config, 'language', {
      enumerable: true,
      configurable: true,
      get: function get() {
        return languageVm.current;
      },
      set: function set(val) {
        languageVm.current = val;
      }
    });

    /*
     * Adds a `getTextPluginSilent` property to `Vue.config`.
     * Used to enable/disable some console warnings.
     */
    Object.defineProperty(Vue.config, 'getTextPluginSilent', {
      enumerable: true,
      writable: true,
      value: getTextPluginSilent
    });
  };

  var Override = function Override(Vue, languageVm) {

    // Override the main init sequence. This is called for every instance.
    var init = Vue.prototype._init;
    Vue.prototype._init = function (options) {
      if (options === void 0) options = {};

      var root = options._parent || options.parent || this;
      // Expose languageVm to every instance.
      this.$language = root.$language || languageVm;
      init.call(this, options);
    };

    // Override the main destroy sequence to destroy all languageVm watchers.
    var destroy = Vue.prototype._destroy;
    Vue.prototype._destroy = function () {
      this.$language = null;
      destroy.apply(this, arguments);
    };
  };

  var languageVm; // Singleton.

  var GetTextPlugin = function GetTextPlugin(Vue, options) {
    if (options === void 0) options = {};

    var defaultConfig = {
      availableLanguages: { en_US: 'English' },
      defaultLanguage: 'en_US',
      languageVmMixin: {},
      silent: Vue.config.silent,
      translations: null
    };

    Object.keys(options).forEach(function (key) {
      if (Object.keys(defaultConfig).indexOf(key) === -1) {
        throw new Error(key + " is an invalid option for the translate plugin.");
      }
    });

    if (!options.translations) {
      throw new Error('No translations available.');
    }

    options = Object.assign(defaultConfig, options);

    languageVm = new Vue({
      created: function created() {
        // Non-reactive data.
        this.available = options.availableLanguages;
      },
      data: {
        current: options.defaultLanguage
      },
      mixins: [options.languageVmMixin]
    });

    shareVueInstance(Vue);

    Override(Vue, languageVm);

    Config(Vue, languageVm, options.silent);

    // Makes <translate> available as a global component.
    Vue.component('translate', Component);

    // An option to support translation with HTML content: `v-translate`.
    Vue.directive('translate', Directive);

    // Exposes global properties.
    Vue.$translations = options.translations;
    // Exposes instance methods.
    Vue.prototype.$gettext = translate.gettext.bind(translate);
    Vue.prototype.$pgettext = translate.pgettext.bind(translate);
    Vue.prototype.$ngettext = translate.ngettext.bind(translate);
    Vue.prototype.$npgettext = translate.npgettext.bind(translate);
    Vue.prototype.$gettextInterpolate = interpolate.bind(interpolate);
  };

  return GetTextPlugin;
});

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(78)
__webpack_require__(77)

var Component = __webpack_require__(3)(
  /* script */
  __webpack_require__(24),
  /* template */
  __webpack_require__(71),
  /* scopeId */
  "data-v-3b62e416",
  /* cssModules */
  null
)
Component.options.__file = "/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/AnakeenLoading/AnakeenLoading.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key !== "__esModule"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] AnakeenLoading.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-3b62e416", Component.options)
  } else {
    hotAPI.reload("data-v-3b62e416", Component.options)
  }
})()}

module.exports = Component.exports


/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(76)

var Component = __webpack_require__(3)(
  /* script */
  __webpack_require__(25),
  /* template */
  __webpack_require__(70),
  /* scopeId */
  null,
  /* cssModules */
  null
)
Component.options.__file = "/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/Authent/Authent.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key !== "__esModule"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] Authent.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-2468153e", Component.options)
  } else {
    hotAPI.reload("data-v-2468153e", Component.options)
  }
})()}

module.exports = Component.exports


/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(83)
__webpack_require__(81)

var Component = __webpack_require__(3)(
  /* script */
  __webpack_require__(27),
  /* template */
  __webpack_require__(73),
  /* scopeId */
  "data-v-56650ed6",
  /* cssModules */
  null
)
Component.options.__file = "/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/Document/Document.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key !== "__esModule"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] Document.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-56650ed6", Component.options)
  } else {
    hotAPI.reload("data-v-56650ed6", Component.options)
  }
})()}

module.exports = Component.exports


/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(75)

var Component = __webpack_require__(3)(
  /* script */
  __webpack_require__(28),
  /* template */
  __webpack_require__(69),
  /* scopeId */
  null,
  /* cssModules */
  null
)
Component.options.__file = "/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/DocumentList/documentList.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key !== "__esModule"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] documentList.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-143db676", Component.options)
  } else {
    hotAPI.reload("data-v-143db676", Component.options)
  }
})()}

module.exports = Component.exports


/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(79)
__webpack_require__(80)

var Component = __webpack_require__(3)(
  /* script */
  __webpack_require__(29),
  /* template */
  __webpack_require__(72),
  /* scopeId */
  null,
  /* cssModules */
  null
)
Component.options.__file = "/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/DocumentTabs/documentTabs.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key !== "__esModule"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] documentTabs.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-49db6276", Component.options)
  } else {
    hotAPI.reload("data-v-49db6276", Component.options)
  }
})()}

module.exports = Component.exports


/***/ }),
/* 23 */
/***/ (function(module, exports) {

module.exports = {"en":{"Help Content":{"Authent":"This window allows you to log on the application, that is indicate to the applications whom you are.\n\nThis is necessary to guarantee that yours informations are accessible only by you. To log on, it is necessary to fill the login and password (If you do not possess it contact your administrator).\n\nPay attention, this password is your identification : be sure to keep it safe and do not spread it !\n\nMind the case (use of the characters capital letter and small letter) during the fill of your login and password!"},"Identifier :":{"Authent":"Username"},"Send reset password ask":{"Authent":"Send"},"Sign in":{"Authent":"Log in"},"Enter your identifier":{"Authent":"Enter your username"},"Help to sign in":{"Authent":"Help to log in"},"Authentication error":{"Authent":"Authentication error"},"Form to reset password":{"Authent":"Reset password"},"of":{"DocumentList":"of"},"Items per page":{"DocumentList":"Items per page"}},"fr":{"Enter identifier or email address :":{"Authent":"Saisissez votre identifiant ou votre adresse de courriel"},"Forget password ?":{"Authent":"Mot de passe oublié ?"},"Go back to home page":{"Authent":"Retour à la page principale"},"Help":{"Authent":"Aide"},"Help Content":{"Authent":"Cette fenêtre permet de vous identifier.\n\nCeci est nécessaire pour garantir que les informations vous concernant sont accessibles seulement par vous-même. Pour vous identifier, il vous faut saisir un nom d'utilisateur et un mot de passe (si vous n'en possédez pas adressez-vous au gestionnaire du site).\n\nAttention, ce mot de passe est le garant de votre identification : conservez le précieusement et ne le diffusez pas !\n\nIl est important de respecter la casse (utilisation des caractères majuscules et minuscules) lors de la saisie de votre nom d'utilisateur et mot de passe."},"Identifier :":{"Authent":"Identifiant : "},"Send reset password ask":{"Authent":"Réinitialiser le mot de passe"},"Sign in":{"Authent":"Se connecter"},"Enter your identifier":{"Authent":"Entrez votre identifiant"},"Enter your password":{"Authent":"Entrez votre mot de passe"},"You must enter your password":{"Authent":"Vous devez saisir votre mot de passe"},"You must enter your identifier":{"Authent":"Vous devez saisir votre identifiant"},"Help to sign in":{"Authent":"Aide pour la connection"},"Authentication error":{"Authent":"Échec de l'authentification"},"Unexpected error":{"Authent":"Erreur inconnue"},"Form to reset password":{"Authent":"Réinitialisation du mot de passe"},"Identifier or email address":{"Authent":"Identifiant ou adresse email"},"Password :":{"Authent":"Mot de passe :"},"New password :":{"Authent":"Nouveau mot de passe :"},"Confirm password :":{"Authent":"Confirmation du mot de passe :"},"Confirm password are not same as new password":{"Authent":"Les mots des passes indiqués sont différents"},"of":{"DocumentList":"sur"},"Search in : %{collection}":{"DocumentList":"Rechercher dans : %{collection}"},"No %{collection} to display":{"DocumentList":"Aucun %{collection} à afficher"},"Items per page":{"DocumentList":"Eléments par page"}}}

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = {
    props: {
        color: {
            type: String,
            default: 'white',
            validator: function validator(value) {
                return value === 'black' || value === 'white';
            }
        },
        width: {
            type: String,
            default: 'auto'
        },
        height: {
            type: String,
            default: 'auto'
        }
    },
    computed: {
        viewBox: function viewBox() {
            return '0 0 400 120';
        }
    }
};

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _vue = __webpack_require__(5);

var _vue2 = _interopRequireDefault(_vue);

var _axios = __webpack_require__(8);

var _axios2 = _interopRequireDefault(_axios);

var _AuthentPassword = __webpack_require__(68);

var _AuthentPassword2 = _interopRequireDefault(_AuthentPassword);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// noinspection JSUnusedGlobalSymbols
exports.default = {
    name: 'Authent',
    props: {
        loginUrl: {
            type: String,
            default: 'authent/{login3}/?lang={lang}'
        },
        authentLanguages: {
            type: String,
            default: 'fr_FR, en_US'
        },
        defaultLanguage: {
            type: String,
            default: 'fr_FR'
        }
    },
    data: function data() {
        return {
            login: '',
            authentError: 'Error',
            forgetError: 'Error',
            forgetSuccess: '',
            forgetStatusFailed: false,
            resetError: '',
            resetSuccess: '',
            resetStatusFailed: true,
            wrongPassword: false,
            resetPassword: false,
            pwd: '',
            resetPwd1: '',
            resetPwd2: ''
        };
    },


    components: {
        'ank-password': _AuthentPassword2.default
    },
    computed: {
        translations: function translations() {
            return {
                loginPlaceHolder: this.$pgettext('Authent', 'Enter your identifier'),
                passwordPlaceHolder: this.$pgettext('Authent', 'Enter your password'),
                validationMessagePassword: this.$pgettext('Authent', 'You must enter your password'),
                validationMessageIdentifier: this.$pgettext('Authent', 'You must enter your identifier'),
                helpContentTitle: this.$pgettext('Authent', 'Help to sign in'),
                authentError: this.$pgettext('Authent', 'Authentication error'),
                unexpectedError: this.$pgettext('Authent', 'Unexpected error'),
                forgetContentTitle: this.$pgettext('Authent', 'Form to reset password'),
                forgetPlaceHolder: this.$pgettext('Authent', 'Identifier or email address'),
                passwordLabel: this.$pgettext('Authent', 'Password :'),
                resetPasswordLabel: this.$pgettext('Authent', 'New password :'),
                confirmPasswordLabel: this.$pgettext('Authent', 'Confirm password :'),
                confirmPasswordError: this.$pgettext('Authent', 'Confirm password are not same as new password')
            };
        },
        availableLanguages: function availableLanguages() {
            var _this = this;

            var languages = this.authentLanguages.split(',');

            return languages.map(function (lang) {
                // jscs:ignore requireShorthandArrowFunctions
                return {
                    key: lang.trim(),
                    label: _this.$language.available[lang.trim()]
                };
            });
        },
        redirectUri: function redirectUri() {
            var uri = this._protected.getSearchArg('redirect_uri');
            if (!uri) {
                uri = '/';
            }

            return uri.replace(/(https?:\/\/)|(\/)+/g, '$1$2');
        }
    },

    beforeMount: function beforeMount() {
        var passKey = this._protected.getSearchArg('passkey');
        var currentLanguage = this.defaultLanguage;
        if (this.defaultLanguage === 'auto') {
            var navLanguage = navigator.language || navigator.userLanguage;
            if (navLanguage === 'fr') {
                currentLanguage = 'fr_FR';
            } else {
                currentLanguage = 'en_US';
            }
        }

        _vue2.default.config.language = currentLanguage;

        if (passKey) {
            this.resetPassword = true;
            this.login = this._protected.getSearchArg('uid');
            this.authToken = passKey;
        }
    },
    created: function created() {
        var _this2 = this;

        this._protected = {};

        this._protected.getSearchArg = function (key) {
            var result = null;
            var tmp = [];
            location.search.substr(1).split('&').forEach(function (item) {
                tmp = item.split('=');
                if (tmp[0] === key) result = decodeURIComponent(tmp[1]);
            });

            return result;
        };

        this._protected.initForgetElements = function () {

            var $ = _this2.$kendo.jQuery;
            var $forgetForm = $(_this2.$refs.authentForgetForm);
            var forgetWindow = void 0;

            forgetWindow = $(_this2.$refs.authentForgetForm).kendoWindow({
                visible: false,
                actions: ['Maximize', 'Close']
            }).data('kendoWindow');

            $(_this2.$refs.authentForgetButton).kendoButton({
                click: function click() {
                    forgetWindow.title(_this2.translations.forgetContentTitle).center().open();
                }
            });

            $(_this2.$refs.authentForgetSubmit).kendoButton();
            $forgetForm.on('submit', _this2.forgetPassword);
        };

        this._protected.initResetPassword = function () {

            var $ = _this2.$kendo.jQuery;
            var $resetForm = $(_this2.$refs.authentResetPasswordForm);

            $(_this2.$refs.authentResetSubmit).kendoButton();

            $resetForm.on('submit', _this2.applyResetPassword);
        };
    },
    mounted: function mounted() {
        var _this3 = this;

        var $ = this.$kendo.jQuery;
        var $connectForm = $(this.$refs.authentForm);
        var helpWindow = void 0;

        $(this.$refs.authentHelpButton).kendoButton({
            click: function click() {
                helpWindow.title(_this3.translations.helpContentTitle).center().open();
            }
        });

        $(this.$refs.loginButton).kendoButton();
        $connectForm.on('submit', this.createSession);
        $(this.$refs.authentComponent).find('.btn-reveal').on('click', function revealPass() {
            var $pwd = $(this).closest('.input-group-btn').find('input');
            if ($pwd.attr('type') === 'password') {
                $pwd.attr('type', 'text');
                $(this).find('.fa').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                if ($pwd.attr('type') === 'text') {
                    $pwd.attr('type', 'password');
                    $(this).find('.fa').addClass('fa-eye').removeClass('fa-eye-slash');
                }
            }
        });

        $(this.$refs.authentLocale).kendoDropDownList({
            change: function changeLocale() {
                _vue2.default.config.language = this.value();
            }
        });

        helpWindow = $(this.$refs.authentHelpContent).kendoWindow({
            visible: false,
            actions: ['Maximize', 'Close']
        }).data('kendoWindow');

        /**
         * Special custom warning if required fields are empty
         */

        if (this.resetPassword) {
            this._protected.initResetPassword();
        } else {
            this._protected.initForgetElements();
        }
    },


    methods: {
        createSession: function createSession(event) {
            var _this4 = this;

            var $ = this.$kendo.jQuery;

            kendo.ui.progress($(this.$refs.authentForm), true);

            var login = encodeURIComponent(this.login);
            event.preventDefault();
            this.$http.post('/authent/sessions/' + login, {
                password: this.pwd,
                language: this.$language.current
            }).then(function () {
                window.location.href = _this4.redirectUri;
                _this4.wrongPassword = false;
            }).catch(function (e) {
                console.log('Error', e);
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    var info = e.response.data;
                    if (info.messages && info.messages.length > 0 && info.messages[0].code === 'AUTH0001') {
                        // Normal authentication error
                        _this4.authentError = _this4.translations.authentError;
                    } else {
                        _this4.authentError = e.response.data.exceptionMessage;
                    }
                } else {
                    _this4.authentError = _this4.translations.authentError;
                }

                _this4.wrongPassword = true;

                kendo.ui.progress($(_this4.$refs.authentForm), false);
                $(_this4.$refs.loginButton).prop('disabled', false);
            });

            $(this.$refs.loginButton).prop('disabled', true);
        },
        forgetPassword: function forgetPassword(event) {
            var _this5 = this;

            var $ = this.$kendo.jQuery;

            kendo.ui.progress($(this.$refs.authentForgetForm), true);

            var login = encodeURIComponent(this.login);
            event.preventDefault();
            this.$http.post('/authent/mailPassword/' + login, {
                password: this.pwd,
                language: this.$language.current
            }).then(function (response) {
                console.log('Success', response);
                _this5.forgetStatusFailed = false;
                kendo.ui.progress($(_this5.$refs.authentForgetForm), false);
                _this5.forgetSuccess = response.data.data.message;
                $(_this5.$refs.authentForgetSubmit).prop('disabled', true).hide();
            }).catch(function (e) {
                console.log('Error', e);
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    var info = e.response.data;

                    if (info.messages && info.messages.length > 0) {
                        _this5.forgetError = info.messages[0].contentText;
                    } else {
                        _this5.forgetError = e.response.data.exceptionMessage;
                    }
                } else {
                    _this5.forgetError = _this5.translations.unexpectedError;
                }

                _this5.forgetStatusFailed = true;

                kendo.ui.progress($(_this5.$refs.authentForgetForm), false);
                $(_this5.$refs.authentForgetSubmit).prop('disabled', false);
            });
        },
        applyResetPassword: function applyResetPassword(event) {
            var _this6 = this;

            var $ = this.$kendo.jQuery;

            event.preventDefault();

            if (!this.resetPwd1 || this.resetPwd1 !== this.resetPwd2) {
                this.resetStatusFailed = true;
                this.resetError = this.translations.confirmPasswordError;
                return;
            }

            var httpAuth = _axios2.default.create({
                baseURL: '/api/v1',
                headers: {
                    Authorization: 'DcpOpen ' + this.authToken
                }
            });

            kendo.ui.progress($(this.$refs.authentResetPasswordForm), true);

            var login = encodeURIComponent(this.login);
            httpAuth.put('/authent/password/' + login, {
                password: this.resetPwd1,
                language: this.$language.current

            }).then(function (response) {
                console.log('Success', response);
                _this6.resetStatusFailed = false;
                kendo.ui.progress($(_this6.$refs.authentResetPasswordForm), false);
                _this6.resetSuccess = response.data.data.message;
                window.setTimeout(function () {
                    $(_this6.$refs.authentGoHome).kendoButton({
                        click: function click() {
                            window.location.href = '../';
                        }
                    });
                }, 10);
            }).catch(function (e) {
                if (e.response && e.response.data && e.response.data.exceptionMessage) {
                    var info = e.response.data;

                    if (info.messages && info.messages.length > 0) {
                        _this6.resetError = info.messages[0].contentText;
                    } else {
                        _this6.resetError = e.response.data.exceptionMessage;
                    }
                } else {
                    _this6.resetError = _this6.translations.unexpectedError;
                }

                _this6.resetStatusFailed = true;

                kendo.ui.progress($(_this6.$refs.authentResetPasswordForm), false);
                $(_this6.$refs.authentForgetSubmit).prop('disabled', false);
            });

            $(this.$refs.authentForgetSubmit).prop('disabled', true).hide();
        }
    }
};

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

// noinspection JSUnusedGlobalSymbols
exports.default = {
    name: 'ank-input-password',
    props: {
        label: {
            type: String,
            default: 'Password'
        },
        placeholder: {
            type: String,
            default: ''
        },

        validationMessage: {
            type: String,
            default: ''
        }
    },
    data: function data() {
        return {
            value: '',
            pwdId: 'pwd' + this._uid
        };
    },
    mounted: function mounted() {
        var $ = this.$kendo.jQuery;
        var $input = $(this.$refs.authentPassword).find('input');
        var _this = this;

        $(this.$refs.authentReveal).on('click', function revealPass() {
            var $pwd = $(this).closest('.input-group-btn').find('input');
            if ($pwd.attr('type') === 'password') {
                $pwd.attr('type', 'text');
                $(this).find('.fa').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                if ($pwd.attr('type') === 'text') {
                    $pwd.attr('type', 'password');
                    $(this).find('.fa').addClass('fa-eye').removeClass('fa-eye-slash');
                }
            }
        });

        $input.on('input, invalid', function requireMessage() {
            if (this.value === '' && _this.validationMessage) {
                this.setCustomValidity(_this.validationMessage);
            } else {
                this.setCustomValidity('');
            }
        });

        $input.trigger('input');
    },


    methods: {
        changePassword: function changePassword() {
            this.$emit('input', this.value);
        }
    }
};

/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _componentBase = __webpack_require__(7);

var _componentBase2 = _interopRequireDefault(_componentBase);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } } /**
                                                                                                                                                                                                     * Dynacase document component object ***
                                                                                                                                                                                                     */

exports.default = {
    mixins: [_componentBase2.default],
    name: 'ank-document',
    data: function data() {
        return {
            value: {
                default: function _default() {
                    return {
                        initid: 0,
                        viewid: '!defaultConsultation',
                        revision: -1,
                        customClientData: null
                    };
                }
            }
        };
    },


    props: {
        documentvalue: {
            type: [String],
            default: function _default() {
                return JSON.stringify({
                    initid: 0,
                    viewid: '!defaultConsultation',
                    revision: -1,
                    customClientData: null
                });
            },
            validator: function validator(value) {
                try {
                    return JSON.parse(value).initid !== undefined;
                } catch (e) {
                    return false;
                }
            }
        },
        browserhistory: {
            default: false,
            type: Boolean
        },
        url: {
            default: '',
            type: String
        },
        initid: {
            type: [Number, String],
            default: 0
        },
        customclientdata: {
            type: [String],
            default: null,
            validator: function validator(value) {
                try {
                    JSON.parse(value);
                    return true;
                } catch (e) {
                    return false;
                }
            }
        },
        viewid: {
            type: String,
            default: '!defaultConsultation'
        },
        revision: {
            type: Number,
            default: -1
        }
    },

    updated: function updated() {
        this.fetchDocument(this.getInitialData).then(function (response) {}).catch(function (error) {
            console.error(error);
        });
    },


    computed: {
        documentValue: function documentValue() {
            return JSON.parse(this.documentvalue);
        },
        customClientData: function customClientData() {
            return JSON.parse(this.customclientdata);
        },
        getInitialData: function getInitialData() {
            /**
             * Access to document
             * Using fetchDocument
             */
            // let dUrl = this.url;
            var initialData = {
                noRouter: this.browserhistory !== true
            };

            /**
             * Prop documentValue are priority on single properties
             */
            initialData.initid = this.documentValue.initid || this.initid;
            if (this.documentValue.customClientData || this.customClientData) {
                initialData.customClientData = this.documentValue.customClientData || this.customClientData;
            }

            if (this.documentValue.revision !== -1) {
                initialData.revision = this.documentValue.revision;
            } else if (this.revision !== -1) {
                initialData.revision = this.revision;
            }

            if (this.documentValue.viewid !== '!defaultConsultation') {
                initialData.viewId = this.documentValue.viewid;
            } else if (this.viewid !== '!defaultConsultation') {
                initialData.viewId = this.viewid;
            }

            return initialData;
        }
    },

    beforeMount: function beforeMount() {},


    methods: {
        /**
         * True when internal widget is loaded
         * @returns {boolean}
         */
        isLoaded: function isLoaded() {
            return this.documentWidget !== undefined;
        },


        /**
         * Rebind all declared binding to internal widget
         * @returns void
         */
        listenAttributes: function listenAttributes() {
            var _this = this;

            var eventNames = ['beforeRender', 'ready', 'change', 'displayMessage', 'displayError', 'validate', 'attributeBeforeRender', 'attributeReady', 'attributeHelperSearch', 'attributeHelperResponse', 'attributeHelperSelect', 'attributeArrayChange', 'actionClick', 'attributeAnchorClick', 'beforeClose', 'close', 'beforeSave', 'afterSave', 'attributeDownloadFile', 'attributeUploadFile', 'beforeDelete', 'afterDelete', 'beforeRestore', 'afterRestore', 'failTransition', 'successTransition', 'beforeDisplayTransition', 'afterDisplayTransition', 'beforeTransition', 'beforeTransitionClose', 'destroy', 'attributeCreateDialogDocumentBeforeSetFormValues', 'attributeCreateDialogDocumentBeforeSetTargetValue', 'attributeCreateDialogDocumentReady', 'attributeCreateDialogDocumentBeforeClose', 'attributeCreateDialogDocumentBeforeDestroy'];
            /* eslint-disable no-underscore-dangle */
            var localListener = this.$options._parentListeners || {};

            eventNames.forEach(function (eventName) {
                _this.documentWidget.addEventListener(eventName, {
                    name: 'v-on-' + eventName + '-listen',
                    documentCheck: function documentCheck() /* documentObject */{
                        return true;
                    }
                }, function (event, documentObject) {
                    for (var _len = arguments.length, others = Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
                        others[_key - 2] = arguments[_key];
                    }

                    _this.$emit.apply(_this, [eventName, event, documentObject].concat(others));
                });
            });

            Object.keys(localListener).forEach(function (key) {
                // input is an internal vuejs bind
                if (eventNames.indexOf(key) === -1 && key !== 'documentLoaded' && key !== 'input') {
                    /* eslint-disable no-console */
                    console.error('Cannot listen to "' + key + '". It is not a defined listener for ank-document component');
                }
            });

            /**
             * Add listener to update component values
             */
            this.documentWidget.addEventListener('ready', {
                name: 'v-on-dcpready-listen'
            }, function (event, documentObject) {
                _this.value = documentObject;
            });
        },
        addEventListener: function addEventListener(eventType, options, callback) {
            return this.documentWidget.addEventListener(eventType, options, callback);
        },
        fetchDocument: function fetchDocument(value, options) {
            return this.documentWidget.fetchDocument(value, options);
        },
        saveDocument: function saveDocument(options) {
            return this.documentWidget.saveDocument(options);
        },
        showMessage: function showMessage(message) {
            return this.documentWidget.showMessage(message);
        },
        getAttributes: function getAttributes() {
            return this.documentWidget.getAttributes();
        },
        getAttribute: function getAttribute(attributeId) {
            return this.documentWidget.getAttribute(attributeId);
        },
        setValue: function setValue(attributeId, newValue) {
            if (typeof newValue === 'string') {
                /* eslint-disable no-param-reassign */
                newValue = {
                    value: newValue,
                    displayValue: newValue
                };
            }

            return this.documentWidget.setValue(attributeId, newValue);
        },
        reinitDocument: function reinitDocument(values, options) {
            return this.documentWidget.reinitDocument(values, options);
        },
        changeStateDocument: function changeStateDocument(parameters, reinitOptions, options) {
            return this.documentWidget.changeStateDocument(parameters, reinitOptions, options);
        },
        deleteDocument: function deleteDocument(options) {
            return this.documentWidget.deleteDocument(options);
        },
        restoreDocument: function restoreDocument(options) {
            return this.documentWidget.restoreDocument(options);
        },
        getProperty: function getProperty(property) {
            return this.documentWidget.getProperty(property);
        },
        getProperties: function getProperties() {
            return this.documentWidget.getProperties();
        },
        hasAttribute: function hasAttribute(attributeId) {
            return this.documentWidget.hasAttribute(attributeId);
        },
        hasMenu: function hasMenu(menuId) {
            return this.documentWidget.hasMenu(menuId);
        },
        getMenu: function getMenu(menuId) {
            return this.documentWidget.getMenu(menuId);
        },
        getMenus: function getMenus() {
            return this.documentWidget.getMenus();
        },
        getValue: function getValue(attributeId, type) {
            return this.documentWidget.getValue(attributeId, type);
        },
        getValues: function getValues() {
            return this.documentWidget.getValues();
        },
        getCustomServerData: function getCustomServerData() {
            return this.documentWidget.getCustomServerData();
        },
        isModified: function isModified() {
            return this.documentWidget.getProperty('isModified');
        },
        addCustomClientData: function addCustomClientData(documentCheck, value) {
            return this.documentWidget.addCustomClientData(documentCheck, value);
        },
        getCustomClientData: function getCustomClientData(deleteOnce) {
            return this.documentWidget.getCustomClientData(deleteOnce);
        },
        removeCustomClientData: function removeCustomClientData(key) {
            return this.documentWidget.removeCustomClientData(key);
        },
        appendArrayRow: function appendArrayRow(attributeId, values) {
            return this.documentWidget.appendArrayRow(attributeId, values);
        },
        insertBeforeArrayRow: function insertBeforeArrayRow(attributeId, values, index) {
            return this.documentWidget.insertBeforeArrayRow(attributeId, values, index);
        },
        removeArrayRow: function removeArrayRow(attributeId, index) {
            return this.documentWidget.removeArrayRow(attributeId, index);
        },
        addConstraint: function addConstraint(options, callback) {
            return this.documentWidget.addConstraint(options, callback);
        },
        listConstraints: function listConstraints() {
            return this.documentWidget.listConstraints();
        },
        removeConstraint: function removeConstraint(constraintName, allKind) {
            return this.documentWidget.removeConstraint(constraintName, allKind);
        },
        listEventListeners: function listEventListeners() {
            return this.documentWidget.listEventListeners();
        },
        removeEventListener: function removeEventListener(eventName, allKind) {
            return this.documentWidget.removeEventListener(eventName, allKind);
        },
        triggerEvent: function triggerEvent(eventName) {
            var _documentWidget;

            for (var _len2 = arguments.length, parameters = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
                parameters[_key2 - 1] = arguments[_key2];
            }

            return (_documentWidget = this.documentWidget).triggerEvent.apply(_documentWidget, [eventName].concat(_toConsumableArray(parameters)));
        },
        hideAttribute: function hideAttribute(attributeId) {
            return this.documentWidget.hideAttribute(attributeId);
        },
        showAttribute: function showAttribute(attributeId) {
            return this.documentWidget.showAttribute(attributeId);
        },
        maskDocument: function maskDocument(message, px) {
            return this.documentWidget.maskDocument(message, px);
        },
        unmaskDocument: function unmaskDocument(force) {
            return this.documentWidget.unmaskDocument(force);
        },
        tryToDestroy: function tryToDestroy() {
            return this.documentWidget.tryToDestroy();
        },
        injectCSS: function injectCSS(cssToInject) {
            return this.documentWidget.injectCSS(cssToInject);
        }
    },

    mounted: function mounted() {
        var _this2 = this;

        var $iframe = this.$refs.iDocument;
        var documentWindow = $iframe.contentWindow;
        $iframe.addEventListener('load', function () {
            documentWindow.documentLoaded = function (domNode) {
                // Re Bind the internalController function to the current widget
                _this2.documentWidget = domNode.data('dcpDocumentController');
                if (_this2.initid !== 0) {
                    _this2.listenAttributes();
                    $iframe.style.visibility = '';
                    _this2.fetchDocument(_this2.getInitialData);
                } else {
                    _this2.documentWidget.addEventListener('ready', { once: true }, function () {
                        _this2.listenAttributes();
                        $iframe.style.visibility = '';
                    });
                }

                _this2.$emit('documentLoaded');
            };

            if (documentWindow.dcp && documentWindow.dcp.triggerReload && documentWindow.dcp.documentReady === false) {
                documentWindow.dcp.triggerReload();
            }
        }, true);
    }
};

/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _componentBase = __webpack_require__(7);

var _componentBase2 = _interopRequireDefault(_componentBase);

var _documentItemTemplate = __webpack_require__(62);

var _documentItemTemplate2 = _interopRequireDefault(_documentItemTemplate);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
    mixins: [_componentBase2.default],
    props: {
        logoUrl: {
            type: String,
            default: 'CORE/Images/anakeen-logo.svg'
        }
    },

    created: function created() {
        var _this2 = this;

        this.privateScope = {
            replaceTopPagerButton: function replaceTopPagerButton() {
                var $pager = _this2.$(_this2.$refs.summaryPager);
                var buttons = $pager.find('.k-pager-nav:not(.k-pager-last):not(.k-pager-first)');
                var label = $pager.find('span.k-pager-info');
                label.insertBefore(buttons[1]);
            },

            initKendo: function initKendo() {
                var _this = _this2;
                _this2.dataSource = new _this2.$kendo.data.DataSource({
                    transport: {
                        read: function read(options) {
                            if (options.data.collection) {
                                var params = {
                                    fields: 'document.properties.state,document.properties.icon',
                                    page: options.data.page,
                                    offset: (options.data.page - 1) * options.data.take,
                                    slice: options.data.take
                                };
                                if (_this2.filterInput) {
                                    params.filter = _this2.filterInput;
                                }

                                _this.privateScope.sendGetRequest('api/v1/sba/collections/' + options.data.collection + '/documentsList', {
                                    params: params
                                }).then(function (response) {
                                    options.success(response);
                                }).catch(function (response) {
                                    options.error(response);
                                });
                            } else {
                                options.error();
                            }
                        }
                    },
                    pageSize: _this2.pageSizeOptions[1].value,
                    serverPaging: true,
                    schema: {
                        total: function total(response) {
                            return response.data.data.resultMax;
                        },

                        data: function data(response) {
                            return response.data.data.documents;
                        }
                    }

                });
                _this2.$(_this2.$refs.listView).kendoListView({
                    dataSource: _this2.dataSource,
                    template: _this2.$kendo.template(_documentItemTemplate2.default),
                    selectable: 'single',
                    change: _this2.privateScope.onSelectDocument,
                    scrollable: true
                });

                _this2.$(_this2.$refs.pager).kendoPager({
                    dataSource: _this2.dataSource,
                    numeric: false,
                    input: true,
                    info: false,
                    pageSizes: false,
                    change: _this2.privateScope.onPagerChange,
                    messages: {
                        page: '',
                        of: '/ {0}',
                        empty: _this2.translations.noDataPagerLabel
                    }
                });
                _this2.$(_this2.$refs.summaryPager).kendoPager({
                    dataSource: _this2.dataSource,
                    numeric: false,
                    input: false,
                    info: true,
                    change: _this2.privateScope.onPagerChange,
                    messages: {
                        display: '{0} - {1} ' + _this2.$pgettext('DocumentList', 'of') + ' {2}',
                        empty: _this2.translations.noDataPagerLabel
                    }
                });

                _this2.$(_this2.$refs.pagerCounter).kendoDropDownList({
                    dataSource: _this2.pageSizeOptions,
                    dataTextField: 'text',
                    dataValueField: 'value',
                    animation: false,
                    index: 1,
                    change: _this2.privateScope.onSelectPageSize,
                    // valueTemplate: '<span class="fa fa-list-ol"></span>',
                    headerTemplate: '<li class="dropdown-header">' + _this2.translations.itemsPerPageLabel + '</li>',
                    template: '<span class="documentsList__documents__pagination__pageSize">#= data.text#</span>'
                }).data('kendoDropDownList').list.addClass('documentsList__documents__pagination__list');
            },

            onPagerChange: function onPagerChange(e) {
                _this2.dataSource.page(e.index);
                _this2.refreshDocumentsList();
            },

            sendGetRequest: function sendGetRequest(url, conf) {
                var element = _this2.$(_this2.$refs.wrapper);
                _this2.$kendo.ui.progress(element, true);
                return new Promise(function (resolve, reject) {
                    _this2.$http.get(url, conf).then(function (response) {
                        _this2.$kendo.ui.progress(element, false);
                        resolve(response);
                    }).catch(function (error) {
                        _this2.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },

            onSelectPageSize: function onSelectPageSize(e) {
                var counter = _this2.$(_this2.$refs.pagerCounter).data('kendoDropDownList');
                var newPageSize = counter.dataItem(e.item).value;
                _this2.dataSource.pageSize(newPageSize);
                _this2.refreshDocumentsList();
            },

            onSelectDocument: function onSelectDocument() {
                // this.$emit('store-save', {action: 'openDocument', data: document });
                var data = _this2.dataSource.view();
                var listView = _this2.$(_this2.$refs.listView).data('kendoListView');
                var selected = _this2.$.map(listView.select(), function (item) {
                    return data[_this2.$(item).index()];
                });
                _this2.selectDocument(selected[0]);
            }
        };
    },
    mounted: function mounted() {
        var _this3 = this;

        this.$kendo.ui.progress(this.$(this.$refs.wrapper), true);
        var ready = function ready() {
            _this3.privateScope.initKendo();
            _this3.privateScope.replaceTopPagerButton();
            _this3.$kendo.ui.progress(_this3.$(_this3.$refs.wrapper), false);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },
    data: function data() {
        return {
            collection: null,
            documents: [],
            appConfig: null,
            dataSource: null,
            filterInput: '',
            pageSizeOptions: [{
                text: '5',
                value: 5
            }, {
                text: '10',
                value: 10
            }, {
                text: '25',
                value: 25
            }, {
                text: '50',
                value: 50
            }, {
                text: '100',
                value: 100
            }]
        };
    },


    computed: {
        translations: function translations() {
            var searchTranslated = this.$pgettext('DocumentList', 'Search in : %{collection}');
            var noDataTranslated = this.$pgettext('DocumentList', 'No %{collection} to display');
            return {
                searchPlaceholder: this.$gettextInterpolate(searchTranslated, {
                    collection: this.collection ? this.collection.html_label.toUpperCase() : ''
                }),
                itemsPerPageLabel: this.$pgettext('DocumentList', 'Items per page'),
                noDataPagerLabel: this.$gettextInterpolate(noDataTranslated, {
                    collection: this.collection ? this.collection.html_label : ''
                })
            };
        }
    },

    methods: {
        selectDocument: function selectDocument(document) {
            this.$emit('document-selected', Object.assign({}, document.properties));
        },
        filterDocumentsList: function filterDocumentsList(filterValue) {
            this.filterInput = filterValue;
            if (filterValue) {
                this.refreshDocumentsList();
            } else {
                this.clearDocumentsListFilter();
            }
        },
        clearDocumentsListFilter: function clearDocumentsListFilter() {
            this.filterInput = '';
            this.refreshDocumentsList();
        },
        setCollection: function setCollection(c) {
            this.collection = c;
            this.dataSource.page(1);
            this.refreshDocumentsList();
        },
        refreshDocumentsList: function refreshDocumentsList() {
            var _this4 = this;

            return new Promise(function (resolve, reject) {
                if (_this4.collection && _this4.dataSource) {
                    _this4.dataSource.read({ collection: _this4.collection.initid }).then(resolve).catch(reject);
                } else {
                    reject();
                }
            });
        }
    }
};

/***/ }),
/* 29 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; // jscs:disable requirePaddingNewLinesBeforeLineComments


var _documentTabsContentTemplate = __webpack_require__(64);

var _documentTabsContentTemplate2 = _interopRequireDefault(_documentTabsContentTemplate);

var _documentTabsHeaderTemplate = __webpack_require__(65);

var _documentTabsHeaderTemplate2 = _interopRequireDefault(_documentTabsHeaderTemplate);

var _documentTabsWelcomeHeaderTemplate = __webpack_require__(67);

var _documentTabsWelcomeHeaderTemplate2 = _interopRequireDefault(_documentTabsWelcomeHeaderTemplate);

var _documentTabsWelcomeContentTemplate = __webpack_require__(66);

var _documentTabsWelcomeContentTemplate2 = _interopRequireDefault(_documentTabsWelcomeContentTemplate);

var _documentOpenedTabListItemTemplate = __webpack_require__(63);

var _documentOpenedTabListItemTemplate2 = _interopRequireDefault(_documentOpenedTabListItemTemplate);

var _componentBase = __webpack_require__(7);

var _componentBase2 = _interopRequireDefault(_componentBase);

var _tabModel = __webpack_require__(51);

var _tabModel2 = _interopRequireDefault(_tabModel);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var Constants = {
    WELCOME_TAB_ID: 'welcome_tab',
    NEW_TAB_ID: 'new_tab',
    LAZY_TAB_ID: 'lazy_tab_id'
};
exports.default = {
    mixins: [_componentBase2.default],
    props: {
        closable: {
            type: Boolean,
            default: true
        },

        'empty-img': {
            type: String,
            default: 'CORE/Images/anakeenplatform-logo-fondblanc.svg'
        },

        'document-css': {
            type: String,
            default: ''
        }

    },

    data: function data() {
        return {
            collections: [],
            currentUser: null,
            tabModel: null,
            tabstripEl: null,
            tabslistEl: null,
            tabslistSource: null
        };
    },


    computed: {
        emptyState: function emptyState() {
            if (this.tabModel) {
                return this.tabModel.isEmpty();
            } else {
                return true;
            }
        },
        tabstrip: function tabstrip() {
            if (this.tabstripEl) {
                return this.tabstripEl.data('kendoTabStrip');
            }

            return null;
        },
        tabslist: function tabslist() {
            if (this.tabslistEl) {
                return this.tabslistEl.data('kendoDropDownList');
            }

            return null;
        },
        newLazyTab: function newLazyTab() {
            return {
                tabId: Constants.LAZY_TAB_ID,
                headerTemplate: _documentTabsHeaderTemplate2.default,
                contentTemplate: _documentTabsContentTemplate2.default,
                data: {
                    initid: 0
                }
            };
        },
        lazyTabDocument: function lazyTabDocument() {
            var index = this.privateScope.getLazyTabIndex();
            if (index > -1) {
                return this.$(this.tabstrip.contentElement(index)).find('ank-document');
            }

            return null;
        }
    },

    watch: {
        closable: function closable(newValue, oldValue) {
            var _this = this;

            if (newValue !== oldValue) {
                this.tabstrip.tabGroup.children().each(function (i, t) {
                    _this.privateScope.configureCloseTab(t, _this.newValue);
                });
            }
        }
    },

    created: function created() {
        var _this2 = this;

        this.privateScope = {
            createKendoComponents: function createKendoComponents() {
                _this2.privateScope.createKendoTabStrip();
                _this2.privateScope.createKendoOpenedTabsList();
                _this2.privateScope.sendGetRequest('sba/collections').then(function (response) {
                    _this2.collections = response.data.data.collections;
                    _this2.currentUser = response.data.data.user;
                    _this2.privateScope.initTabModel();
                });
                _this2.$(window).resize(function () {
                    _this2.privateScope.resizeComponents();
                });
                _this2.privateScope.resizeComponents();
            },

            createKendoTabStrip: function createKendoTabStrip() {
                _this2.tabstripEl = _this2.$(_this2.$refs.tabstrip).kendoTabStrip({
                    animation: false,
                    select: _this2.privateScope.onTabstripSelect
                });
                _this2.tabModel = new _tabModel2.default();
                _this2.tabModel.on('add', _this2.privateScope.onModelAddItem);
                _this2.tabModel.on('remove', _this2.privateScope.onModelRemoveItem);
                _this2.tabModel.on('itemchange', _this2.privateScope.onModelItemChange);
            },

            createKendoOpenedTabsList: function createKendoOpenedTabsList() {
                _this2.tabsListSource = new _this2.$kendo.data.DataSource({
                    data: []
                });
                _this2.tabslistEl = _this2.$(_this2.$refs.tabsList).kendoDropDownList({
                    animation: false,
                    dataSource: _this2.tabsListSource,
                    template: _this2.$kendo.template(_documentOpenedTabListItemTemplate2.default),
                    valueTemplate: '<i class="material-icons">list</i>',
                    dataBound: _this2.privateScope.onOpenedTabsListDataBound,
                    autoWidth: true,
                    select: _this2.privateScope.onOpenedTabsListItemClick,
                    noDataTemplate: 'Aucun document ouvert',
                    headerTemplate: '<button class="documentsList__documentsTabs__tabsList__list__close__all">\n                                        Fermer tous les onglets\n                                     </button>'
                });
                _this2.tabslist.list.addClass('documentsList__documentsTabs__tabsList__list');
                _this2.tabslist.list.find('.documentsList__documentsTabs__tabsList__list__close__all').on('click', _this2.closeAllDocuments);
            },

            sendGetRequest: function sendGetRequest(url, config, loadingElement) {
                var element = _this2.$(loadingElement);
                _this2.$kendo.ui.progress(element, true);
                return new Promise(function (resolve, reject) {
                    _this2.$http.get(url, config).then(function (response) {
                        _this2.$kendo.ui.progress(element, false);
                        resolve(response);
                    }).catch(function (error) {
                        _this2.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },

            resizeComponents: function resizeComponents() {
                _this2.tabstrip.resize();
                _this2.privateScope.setTabstripPagination();
            },

            setTabstripPagination: function setTabstripPagination() {
                var paginatorWidth = _this2.$(_this2.$refs.tabsPaginator).outerWidth(true);
                var marginRight = paginatorWidth || 0;
                var prev = _this2.tabstripEl.find('.k-tabstrip-prev');
                var next = _this2.tabstripEl.find('.k-tabstrip-next');
                if (prev.length && next.length) {
                    var nextWidth = next.outerWidth(true);
                    var prevWidth = prev.outerWidth(true);
                    next.css('right', paginatorWidth + 'px');
                    prev.css('right', paginatorWidth + nextWidth + 'px');
                    marginRight = marginRight + prevWidth + nextWidth;
                }

                _this2.tabstrip.tabGroup.css('margin-right', marginRight + 'px');
            },

            initTabModel: function initTabModel() {
                var welcomeTab = {
                    tabId: Constants.WELCOME_TAB_ID,
                    headerTemplate: _documentTabsWelcomeHeaderTemplate2.default,
                    contentTemplate: _documentTabsWelcomeContentTemplate2.default,
                    data: {
                        user: _this2.currentUser.firstName,
                        welcomeMessage: 'bienvenue sur Business App.',
                        promptMessage: 'Que voulez-vous faire ?',
                        collections: JSON.stringify(_this2.collections),
                        title: 'Bienvenue'
                    }
                };
                if (_this2.privateScope.getLazyTabIndex() > -1) {
                    _this2.tabModel.add(welcomeTab);
                } else {
                    _this2.tabModel.add(welcomeTab, _this2.newLazyTab);
                }

                _this2.selectDocument(0);
            },

            canUseLazyTab: function canUseLazyTab() {
                if (_this2.lazyTabDocument) {
                    if (_this2.lazyTabDocument.prop('publicMethods').isLoaded()) {
                        return true;
                    }
                }

                return false;
            },

            getLazyTabIndex: function getLazyTabIndex() {
                if (_this2.tabModel) {
                    return _this2.tabModel.findIndex(function (t) {
                        return t.tabId === Constants.LAZY_TAB_ID;
                    });
                }

                return -1;
            },

            setAddTabButton: function setAddTabButton() {
                var newTabButton = _this2.$('#documentsList__documentsTabs__new__tab__button');
                if (!newTabButton.length) {
                    newTabButton = _this2.$('<button id="documentsList__documentsTabs__new__tab__button" class="tab__new__button"><i class="material-icons">add</i></button>');
                    newTabButton.on('click', _this2.privateScope.onAddTabClick);
                }

                _this2.tabstrip.tabGroup.append(newTabButton);
            },

            setCloseTabButton: function setCloseTabButton(tab, forceClose) {
                var $tab = _this2.$(tab);
                var closable = forceClose !== undefined ? forceClose : _this2.closable;
                if (closable) {
                    $tab.find('.tab__document__header__content').append('<span data-type="remove" class="k-link"><span class="k-icon k-i-x"></span></span>');
                    $tab.on('click', "[data-type='remove']", _this2.privateScope.onCloseTabClick);
                } else {
                    $tab.off('click', "[data-type='remove']");
                    $tab.find("span[data-type='remove']").remove();
                }
            },

            setVisitedTagToDocument: function setVisitedTagToDocument(document) {
                _this2.$http.put('documents/' + document.initid + '/usertags/open_document', {
                    counter: 1
                }).then(function (response) {
                    // console.log(response);
                }).catch(function (error) {
                    console.error(error);
                });
            },

            loadLazyTabDocument: function loadLazyTabDocument(data) {
                var tab = _this2.$(_this2.tabstrip.items()[_this2.privateScope.getLazyTabIndex()]);
                tab.find('.tab__document__title').text(data.data.title);
                tab.find('.tab__document__icon').replaceWith('<img class="tab__document__icon" src="' + data.data.icon + '" />');
                _this2.privateScope.onAddDocumentTab(_this2.privateScope.getLazyTabIndex());
                _this2.$(_this2.tabstrip.items()[_this2.privateScope.getLazyTabIndex()]).show();
                _this2.$(_this2.lazyTabDocument).prop('documentvalue', JSON.stringify(data.data));
                _this2.tabModel.get(_this2.privateScope.getLazyTabIndex()).tabId = data.tabId;
                _this2.tabsListSource.add(data);
            },

            bindWelcomeTabEvents: function bindWelcomeTabEvents($newTab, index) {
                $newTab.on('document-creation', function (e) {
                    return _this2.privateScope.onCreateDocumentClick(e, index);
                });
                $newTab.on('document-selected', function (e) {
                    _this2.setDocument(e.detail[0], index);
                });
            },

            bindLazyTabEvents: function bindLazyTabEvents() {},

            bindDocumentTabEvents: function bindDocumentTabEvents($doc, index) {
                var documentComponent = $doc;
                documentComponent.on('ready', function (e) {
                    return _this2.privateScope.onDocumentReady(e, index);
                });
                documentComponent.on('actionClick', function (e) {
                    return _this2.privateScope.onDocumentActionClick(e, index);
                });
                documentComponent.on('afterSave', function (e) {
                    return _this2.privateScope.onDocumentAfterSave(e, index);
                });
                documentComponent.on('afterDelete', function (e) {
                    return _this2.privateScope.onDocumentAfterDelete(e, index);
                });
            },

            onModelAddItem: function onModelAddItem(event) {
                var addedItems = event.items;
                addedItems.forEach(function (item, pos) {
                    var header = _this2.$kendo.template(item.headerTemplate)(item.data);
                    var content = _this2.$kendo.template(item.contentTemplate)(item.data);
                    var tabAdded = { text: header, encoded: false, content: content };
                    var index = event.index + pos;
                    if (index === _this2.tabModel.size() - addedItems.length) {
                        _this2.tabstrip.append(tabAdded);
                    } else if (index === 0) {
                        _this2.tabstrip.insertBefore(tabAdded, _this2.tabstrip.items()[0]);
                    } else {
                        _this2.tabstrip.insertAfter(tabAdded, _this2.tabstrip.items()[index - 1]);
                    }

                    _this2.privateScope.onAddGenericTab(index);
                    if (item.tabId === Constants.NEW_TAB_ID || item.tabId === Constants.WELCOME_TAB_ID) {
                        _this2.privateScope.onAddWelcomeTab(index);
                    } else if (item.tabId === Constants.LAZY_TAB_ID) {
                        _this2.privateScope.onAddLazyTab(index);
                    } else {
                        _this2.privateScope.onAddDocumentTab(index);
                        _this2.tabsListSource.add(item);
                    }
                });
            },

            onModelRemoveItem: function onModelRemoveItem(event, model) {
                if (event.items.length === 1) {
                    if (_this2.$(_this2.tabstrip.items()[event.index]).hasClass('k-state-active') && !_this2.tabModel.isEmpty()) {
                        _this2.selectDocument(0);
                    }

                    _this2.tabstrip.remove(event.index);
                } else if (event.items.length > 1) {
                    _this2.tabstrip.remove('li');
                }

                if (_this2.tabModel.isEmpty() || _this2.tabModel.findIndex(function (t) {
                    return t.tabId !== Constants.LAZY_TAB_ID;
                }) === -1) {
                    _this2.privateScope.initTabModel();
                }

                _this2.privateScope.setTabstripPagination();
                event.items.forEach(function (i) {
                    return _this2.tabsListSource.remove(i);
                });
            },

            onModelItemChange: function onModelItemChange(event, model) {
                var index = _this2.tabModel.findIndex(function (d) {
                    return d.tabId === event.items[0].tabId;
                });
                var props = event.field.split('.');
                var newValue = void 0;
                var $indexedItem = _this2.$(_this2.tabstrip.items()[index]);
                switch (event.field) {
                    case 'data.title':
                        newValue = event.items[0][props[0]][props[1]];
                        $indexedItem.find('span.tab__document__title').text(newValue);
                        break;
                    case 'data.icon':
                        newValue = event.items[0][props[0]][props[1]];
                        $indexedItem.find('img.tab__document__icon').prop('src', newValue);
                        break;
                }
            },

            onAddGenericTab: function onAddGenericTab(index) {
                _this2.privateScope.setAddTabButton();
                _this2.privateScope.setCloseTabButton(_this2.tabstrip.items()[index]);
                _this2.privateScope.setTabstripPagination();
            },

            onAddWelcomeTab: function onAddWelcomeTab(index) {
                var tabContent = _this2.tabstrip.contentElement(index);
                var $newTab = _this2.$(tabContent).find('ank-welcome-tab');
                _this2.privateScope.bindWelcomeTabEvents($newTab, index);
            },

            onAddLazyTab: function onAddLazyTab(index) {
                _this2.$(_this2.tabstrip.items()[index]).hide();
                _this2.$(_this2.tabstrip.contentElement(index)).hide();
                var tabContent = _this2.tabstrip.contentElement(index);
                _this2.privateScope.bindLazyTabEvents(tabContent, index);
            },

            onAddDocumentTab: function onAddDocumentTab(index) {
                var tabContent = _this2.tabstrip.contentElement(index);
                var $doc = _this2.$(tabContent).find('ank-document');
                _this2.privateScope.bindDocumentTabEvents($doc, index);
                $doc.one('ready', function () {
                    _this2.$(tabContent).find('.documentsList__documentsTabs__tab__content--document').show();
                    _this2.$(tabContent).find('.documentsList__documentsTabs__tab__content--loading').hide();
                });
            },

            onAddTabClick: function onAddTabClick(e) {
                e.preventDefault();
                e.stopPropagation();
                _this2.tabModel.add({
                    tabId: Constants.NEW_TAB_ID,
                    headerTemplate: _documentTabsWelcomeHeaderTemplate2.default,
                    contentTemplate: _documentTabsWelcomeContentTemplate2.default,
                    data: {
                        user: _this2.currentUser.firstName,
                        promptMessage: 'Que voulez-vous faire ?',
                        collections: JSON.stringify(_this2.collections),
                        title: 'Nouvel Onglet'
                    }
                });
                _this2.selectDocument(_this2.tabModel.size() - 1);
            },

            onCloseTabClick: function onCloseTabClick(e) {
                e.preventDefault();
                e.stopPropagation();

                var item = _this2.$(e.target).closest('.k-item');
                _this2.closeDocument(item.index());
            },

            onCreateDocumentClick: function onCreateDocumentClick(e, index) {
                var newId = e.detail[0].initid;
                var collection = _this2.collections.find(function (c) {
                    return c.initid === newId;
                });
                if (collection) {
                    _this2.setDocument({
                        initid: collection.initid,
                        viewid: '!defaultCreation',
                        title: 'Cr\xE9ation ' + collection.html_label,
                        icon: collection.image_url
                    }, index);
                    _this2.selectDocument(index);
                }
            },

            onTabstripSelect: function onTabstripSelect(e) {
                var itemSelectedPos = _this2.$(e.item).index();
                var selectedTab = _this2.tabModel.get(itemSelectedPos);
                if (selectedTab.tabId === Constants.NEW_TAB_ID || selectedTab.tabId === Constants.WELCOME_TAB_ID) {
                    var DOMElement = _this2.tabstrip.contentElement(itemSelectedPos);
                    var welcomeTab = _this2.$(DOMElement).find('ank-welcome-tab');
                    if (welcomeTab.prop('publicMethods')) {
                        welcomeTab.prop('publicMethods').refresh();
                    }
                }
            },

            onOpenedTabsListDataBound: function onOpenedTabsListDataBound(e) {
                e.sender.list.find('.documentTabs__openedTab__listItem__close').off('click');
                e.sender.list.find('.documentTabs__openedTab__listItem__close').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    _this2.closeDocument({
                        tabId: e.target.parentElement.dataset.docid
                    });
                });
            },

            onOpenedTabsListItemClick: function onOpenedTabsListItemClick(e) {
                _this2.selectDocument(e.dataItem.data);
            },

            onDocumentReady: function onDocumentReady(readyEvent, tabPosition) {
                var $document = _this2.$(readyEvent.target);
                var iframeDocument = _this2.$(readyEvent.detail[0].target);
                iframeDocument.find('.dcpDocument__header').hide();
                var menus = iframeDocument.find('nav.dcpDocument__menu');
                if (menus.length > 1) {
                    menus[0].classList.add('menu--top');
                    menus[1].classList.add('menu--bottom');
                }

                if (_this2.documentCss) {
                    $document.prop('publicMethods').injectCSS(_this2.documentCss);
                }

                if (tabPosition !== undefined) {
                    _this2.$(_this2.tabstrip.items()[tabPosition]).find('a.tab__document__header__content').prop('href', readyEvent.detail[1].url);
                }

                var lazyIndex = _this2.privateScope.getLazyTabIndex();
                if (lazyIndex != -1) {
                    _this2.tabModel.remove(lazyIndex);
                }
                _this2.tabModel.add(_this2.newLazyTab);
            },

            onDocumentActionClick: function onDocumentActionClick(e, tabPosition) {
                if (e.detail.length > 2 && e.detail[2].options) {
                    if (e.detail[2].eventId === 'document.load') {
                        e.detail[0].preventDefault();
                        var initid = e.detail[2].options[0];
                        var viewid = e.detail[2].options[1];
                        _this2.addDocument({ initid: initid, viewid: viewid });
                    }
                }
            },

            onDocumentAfterSave: function onDocumentAfterSave(e, tabPosition) {
                var tab = _this2.tabModel.get(tabPosition);
                tab.set('tabId', e.detail[1].initid);
                tab.set('data.title', e.detail[1].title);
                tab.set('data.icon', e.detail[1].icon);
                _this2.$emit('document-modified', e.detail);
            },

            onDocumentAfterDelete: function onDocumentAfterDelete(e, tabPosition) {
                _this2.$emit('document-deleted', e.detail);
            }
        };
    },
    mounted: function mounted() {
        var _this3 = this;

        this.$kendo.ui.progress(this.$(this.$refs.tabsWrapper), true);
        var ready = function ready() {
            _this3.privateScope.createKendoComponents();
            _this3.$emit('document-tabs-ready', _this3.$el.parentElement);
            _this3.$kendo.ui.progress(_this3.$(_this3.$refs.tabsWrapper), false);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },


    methods: {
        addDocument: function addDocument(document) {
            var index = this.tabModel.findIndex(function (t) {
                return t.tabId == document.initid;
            });
            if (index < 0) {
                var tabData = {
                    tabId: document.initid,
                    headerTemplate: _documentTabsHeaderTemplate2.default,
                    contentTemplate: _documentTabsContentTemplate2.default,
                    data: Object.assign({}, document)
                };
                if (this.privateScope.canUseLazyTab()) {
                    console.log('USE LAZY LOAD');
                    this.privateScope.loadLazyTabDocument(tabData);
                } else {
                    console.log("DON'T USE LAZY LOAD");
                    this.tabModel.add(tabData);
                }

                this.selectDocument(document);
                this.privateScope.setVisitedTagToDocument(document);
            } else {
                this.selectDocument(index);
            }
        },
        setDocument: function setDocument(document, position) {
            if (position === undefined) {
                this.addDocument(document);
            } else {
                var index = this.tabModel.findIndex(function (t) {
                    return t.tabId == document.initid;
                });
                if (index < 0) {
                    var tabData = {
                        tabId: document.initid,
                        headerTemplate: _documentTabsHeaderTemplate2.default,
                        contentTemplate: _documentTabsContentTemplate2.default,
                        data: Object.assign({}, document)
                    };
                    this.tabModel.replace(position, tabData);
                    /*if (this.privateScope.canUseLazyTab()) {
                        console.log('USE LAZY LOAD');
                        this.tabModel.replace(position, this.tabModel.remove(this.lazyTabIndex));
                        this.privateScope.loadLazyTabDocument(tabData);
                        this.selectDocument(document);
                    } else {
                        console.log("DON'T USE LAZY LOAD");
                        this.tabModel.replace(position, tabData);
                    }*/
                    this.selectDocument(document);
                } else {
                    this.selectDocument(index);
                }
            }
        },
        selectDocument: function selectDocument(documentId) {
            var index = 0;
            if (typeof documentId === 'number') {
                if (documentId >= 0 && documentId < this.tabModel.size()) {
                    index = documentId;
                }
            } else if ((typeof documentId === 'undefined' ? 'undefined' : _typeof(documentId)) === 'object' && documentId !== null && documentId.initid !== undefined) {
                index = this.tabModel.findIndex(function (t) {
                    return t.tabId == documentId.initid;
                });
                if (index < 0) {
                    index = 0;
                }
            }

            this.tabstrip.select(index);
        },
        closeDocument: function closeDocument(documentId) {
            this.tabModel.remove(documentId);
        },
        closeAllDocuments: function closeAllDocuments() {
            this.tabModel.removeAll();
        }
    }
};

/***/ }),
/* 30 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);
var bind = __webpack_require__(13);
var Axios = __webpack_require__(32);
var defaults = __webpack_require__(6);

/**
 * Create an instance of Axios
 *
 * @param {Object} defaultConfig The default config for the instance
 * @return {Axios} A new instance of Axios
 */
function createInstance(defaultConfig) {
  var context = new Axios(defaultConfig);
  var instance = bind(Axios.prototype.request, context);

  // Copy axios.prototype to instance
  utils.extend(instance, Axios.prototype, context);

  // Copy context to instance
  utils.extend(instance, context);

  return instance;
}

// Create the default instance to be exported
var axios = createInstance(defaults);

// Expose Axios class to allow class inheritance
axios.Axios = Axios;

// Factory for creating new instances
axios.create = function create(instanceConfig) {
  return createInstance(utils.merge(defaults, instanceConfig));
};

// Expose Cancel & CancelToken
axios.Cancel = __webpack_require__(10);
axios.CancelToken = __webpack_require__(31);
axios.isCancel = __webpack_require__(11);

// Expose all/spread
axios.all = function all(promises) {
  return Promise.all(promises);
};
axios.spread = __webpack_require__(46);

module.exports = axios;

// Allow use of default import syntax in TypeScript
module.exports.default = axios;

/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Cancel = __webpack_require__(10);

/**
 * A `CancelToken` is an object that can be used to request cancellation of an operation.
 *
 * @class
 * @param {Function} executor The executor function.
 */
function CancelToken(executor) {
  if (typeof executor !== 'function') {
    throw new TypeError('executor must be a function.');
  }

  var resolvePromise;
  this.promise = new Promise(function promiseExecutor(resolve) {
    resolvePromise = resolve;
  });

  var token = this;
  executor(function cancel(message) {
    if (token.reason) {
      // Cancellation has already been requested
      return;
    }

    token.reason = new Cancel(message);
    resolvePromise(token.reason);
  });
}

/**
 * Throws a `Cancel` if cancellation has been requested.
 */
CancelToken.prototype.throwIfRequested = function throwIfRequested() {
  if (this.reason) {
    throw this.reason;
  }
};

/**
 * Returns an object that contains a new `CancelToken` and a function that, when called,
 * cancels the `CancelToken`.
 */
CancelToken.source = function source() {
  var cancel;
  var token = new CancelToken(function executor(c) {
    cancel = c;
  });
  return {
    token: token,
    cancel: cancel
  };
};

module.exports = CancelToken;

/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var defaults = __webpack_require__(6);
var utils = __webpack_require__(0);
var InterceptorManager = __webpack_require__(33);
var dispatchRequest = __webpack_require__(34);
var isAbsoluteURL = __webpack_require__(42);
var combineURLs = __webpack_require__(40);

/**
 * Create a new instance of Axios
 *
 * @param {Object} instanceConfig The default config for the instance
 */
function Axios(instanceConfig) {
  this.defaults = instanceConfig;
  this.interceptors = {
    request: new InterceptorManager(),
    response: new InterceptorManager()
  };
}

/**
 * Dispatch a request
 *
 * @param {Object} config The config specific for this request (merged with this.defaults)
 */
Axios.prototype.request = function request(config) {
  /*eslint no-param-reassign:0*/
  // Allow for axios('example/url'[, config]) a la fetch API
  if (typeof config === 'string') {
    config = utils.merge({
      url: arguments[0]
    }, arguments[1]);
  }

  config = utils.merge(defaults, this.defaults, { method: 'get' }, config);
  config.method = config.method.toLowerCase();

  // Support baseURL config
  if (config.baseURL && !isAbsoluteURL(config.url)) {
    config.url = combineURLs(config.baseURL, config.url);
  }

  // Hook up interceptors middleware
  var chain = [dispatchRequest, undefined];
  var promise = Promise.resolve(config);

  this.interceptors.request.forEach(function unshiftRequestInterceptors(interceptor) {
    chain.unshift(interceptor.fulfilled, interceptor.rejected);
  });

  this.interceptors.response.forEach(function pushResponseInterceptors(interceptor) {
    chain.push(interceptor.fulfilled, interceptor.rejected);
  });

  while (chain.length) {
    promise = promise.then(chain.shift(), chain.shift());
  }

  return promise;
};

// Provide aliases for supported request methods
utils.forEach(['delete', 'get', 'head', 'options'], function forEachMethodNoData(method) {
  /*eslint func-names:0*/
  Axios.prototype[method] = function (url, config) {
    return this.request(utils.merge(config || {}, {
      method: method,
      url: url
    }));
  };
});

utils.forEach(['post', 'put', 'patch'], function forEachMethodWithData(method) {
  /*eslint func-names:0*/
  Axios.prototype[method] = function (url, data, config) {
    return this.request(utils.merge(config || {}, {
      method: method,
      url: url,
      data: data
    }));
  };
});

module.exports = Axios;

/***/ }),
/* 33 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

function InterceptorManager() {
  this.handlers = [];
}

/**
 * Add a new interceptor to the stack
 *
 * @param {Function} fulfilled The function to handle `then` for a `Promise`
 * @param {Function} rejected The function to handle `reject` for a `Promise`
 *
 * @return {Number} An ID used to remove interceptor later
 */
InterceptorManager.prototype.use = function use(fulfilled, rejected) {
  this.handlers.push({
    fulfilled: fulfilled,
    rejected: rejected
  });
  return this.handlers.length - 1;
};

/**
 * Remove an interceptor from the stack
 *
 * @param {Number} id The ID that was returned by `use`
 */
InterceptorManager.prototype.eject = function eject(id) {
  if (this.handlers[id]) {
    this.handlers[id] = null;
  }
};

/**
 * Iterate over all the registered interceptors
 *
 * This method is particularly useful for skipping over any
 * interceptors that may have become `null` calling `eject`.
 *
 * @param {Function} fn The function to call for each interceptor
 */
InterceptorManager.prototype.forEach = function forEach(fn) {
  utils.forEach(this.handlers, function forEachHandler(h) {
    if (h !== null) {
      fn(h);
    }
  });
};

module.exports = InterceptorManager;

/***/ }),
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);
var transformData = __webpack_require__(37);
var isCancel = __webpack_require__(11);
var defaults = __webpack_require__(6);

/**
 * Throws a `Cancel` if cancellation has been requested.
 */
function throwIfCancellationRequested(config) {
  if (config.cancelToken) {
    config.cancelToken.throwIfRequested();
  }
}

/**
 * Dispatch a request to the server using the configured adapter.
 *
 * @param {object} config The config that is to be used for the request
 * @returns {Promise} The Promise to be fulfilled
 */
module.exports = function dispatchRequest(config) {
  throwIfCancellationRequested(config);

  // Ensure headers exist
  config.headers = config.headers || {};

  // Transform request data
  config.data = transformData(config.data, config.headers, config.transformRequest);

  // Flatten headers
  config.headers = utils.merge(config.headers.common || {}, config.headers[config.method] || {}, config.headers || {});

  utils.forEach(['delete', 'get', 'head', 'post', 'put', 'patch', 'common'], function cleanHeaderConfig(method) {
    delete config.headers[method];
  });

  var adapter = config.adapter || defaults.adapter;

  return adapter(config).then(function onAdapterResolution(response) {
    throwIfCancellationRequested(config);

    // Transform response data
    response.data = transformData(response.data, response.headers, config.transformResponse);

    return response;
  }, function onAdapterRejection(reason) {
    if (!isCancel(reason)) {
      throwIfCancellationRequested(config);

      // Transform response data
      if (reason && reason.response) {
        reason.response.data = transformData(reason.response.data, reason.response.headers, config.transformResponse);
      }
    }

    return Promise.reject(reason);
  });
};

/***/ }),
/* 35 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Update an Error with the specified config, error code, and response.
 *
 * @param {Error} error The error to update.
 * @param {Object} config The config.
 * @param {string} [code] The error code (for example, 'ECONNABORTED').
 * @param {Object} [request] The request.
 * @param {Object} [response] The response.
 * @returns {Error} The error.
 */

module.exports = function enhanceError(error, config, code, request, response) {
  error.config = config;
  if (code) {
    error.code = code;
  }
  error.request = request;
  error.response = response;
  return error;
};

/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var createError = __webpack_require__(12);

/**
 * Resolve or reject a Promise based on response status.
 *
 * @param {Function} resolve A function that resolves the promise.
 * @param {Function} reject A function that rejects the promise.
 * @param {object} response The response.
 */
module.exports = function settle(resolve, reject, response) {
  var validateStatus = response.config.validateStatus;
  // Note: status is not exposed by XDomainRequest
  if (!response.status || !validateStatus || validateStatus(response.status)) {
    resolve(response);
  } else {
    reject(createError('Request failed with status code ' + response.status, response.config, null, response.request, response));
  }
};

/***/ }),
/* 37 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

/**
 * Transform the data for a request or a response
 *
 * @param {Object|String} data The data to be transformed
 * @param {Array} headers The headers for the request or response
 * @param {Array|Function} fns A single function or Array of functions
 * @returns {*} The resulting transformed data
 */
module.exports = function transformData(data, headers, fns) {
  /*eslint no-param-reassign:0*/
  utils.forEach(fns, function transform(fn) {
    data = fn(data, headers);
  });

  return data;
};

/***/ }),
/* 38 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// btoa polyfill for IE<10 courtesy https://github.com/davidchambers/Base64.js

var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

function E() {
  this.message = 'String contains an invalid character';
}
E.prototype = new Error();
E.prototype.code = 5;
E.prototype.name = 'InvalidCharacterError';

function btoa(input) {
  var str = String(input);
  var output = '';
  for (
  // initialize result and counter
  var block, charCode, idx = 0, map = chars;
  // if the next str index does not exist:
  //   change the mapping table to "="
  //   check if d has no fractional digits
  str.charAt(idx | 0) || (map = '=', idx % 1);
  // "8 - idx % 1 * 8" generates the sequence 2, 4, 6, 8
  output += map.charAt(63 & block >> 8 - idx % 1 * 8)) {
    charCode = str.charCodeAt(idx += 3 / 4);
    if (charCode > 0xFF) {
      throw new E();
    }
    block = block << 8 | charCode;
  }
  return output;
}

module.exports = btoa;

/***/ }),
/* 39 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

function encode(val) {
  return encodeURIComponent(val).replace(/%40/gi, '@').replace(/%3A/gi, ':').replace(/%24/g, '$').replace(/%2C/gi, ',').replace(/%20/g, '+').replace(/%5B/gi, '[').replace(/%5D/gi, ']');
}

/**
 * Build a URL by appending params to the end
 *
 * @param {string} url The base of the url (e.g., http://www.google.com)
 * @param {object} [params] The params to be appended
 * @returns {string} The formatted url
 */
module.exports = function buildURL(url, params, paramsSerializer) {
  /*eslint no-param-reassign:0*/
  if (!params) {
    return url;
  }

  var serializedParams;
  if (paramsSerializer) {
    serializedParams = paramsSerializer(params);
  } else if (utils.isURLSearchParams(params)) {
    serializedParams = params.toString();
  } else {
    var parts = [];

    utils.forEach(params, function serialize(val, key) {
      if (val === null || typeof val === 'undefined') {
        return;
      }

      if (utils.isArray(val)) {
        key = key + '[]';
      }

      if (!utils.isArray(val)) {
        val = [val];
      }

      utils.forEach(val, function parseValue(v) {
        if (utils.isDate(v)) {
          v = v.toISOString();
        } else if (utils.isObject(v)) {
          v = JSON.stringify(v);
        }
        parts.push(encode(key) + '=' + encode(v));
      });
    });

    serializedParams = parts.join('&');
  }

  if (serializedParams) {
    url += (url.indexOf('?') === -1 ? '?' : '&') + serializedParams;
  }

  return url;
};

/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Creates a new URL by combining the specified URLs
 *
 * @param {string} baseURL The base URL
 * @param {string} relativeURL The relative URL
 * @returns {string} The combined URL
 */

module.exports = function combineURLs(baseURL, relativeURL) {
  return relativeURL ? baseURL.replace(/\/+$/, '') + '/' + relativeURL.replace(/^\/+/, '') : baseURL;
};

/***/ }),
/* 41 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

module.exports = utils.isStandardBrowserEnv() ?

// Standard browser envs support document.cookie
function standardBrowserEnv() {
  return {
    write: function write(name, value, expires, path, domain, secure) {
      var cookie = [];
      cookie.push(name + '=' + encodeURIComponent(value));

      if (utils.isNumber(expires)) {
        cookie.push('expires=' + new Date(expires).toGMTString());
      }

      if (utils.isString(path)) {
        cookie.push('path=' + path);
      }

      if (utils.isString(domain)) {
        cookie.push('domain=' + domain);
      }

      if (secure === true) {
        cookie.push('secure');
      }

      document.cookie = cookie.join('; ');
    },

    read: function read(name) {
      var match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
      return match ? decodeURIComponent(match[3]) : null;
    },

    remove: function remove(name) {
      this.write(name, '', Date.now() - 86400000);
    }
  };
}() :

// Non standard browser env (web workers, react-native) lack needed support.
function nonStandardBrowserEnv() {
  return {
    write: function write() {},
    read: function read() {
      return null;
    },
    remove: function remove() {}
  };
}();

/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Determines whether the specified URL is absolute
 *
 * @param {string} url The URL to test
 * @returns {boolean} True if the specified URL is absolute, otherwise false
 */

module.exports = function isAbsoluteURL(url) {
  // A URL is considered absolute if it begins with "<scheme>://" or "//" (protocol-relative URL).
  // RFC 3986 defines scheme name as a sequence of characters beginning with a letter and followed
  // by any combination of letters, digits, plus, period, or hyphen.
  return (/^([a-z][a-z\d\+\-\.]*:)?\/\//i.test(url)
  );
};

/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

module.exports = utils.isStandardBrowserEnv() ?

// Standard browser envs have full support of the APIs needed to test
// whether the request URL is of the same origin as current location.
function standardBrowserEnv() {
  var msie = /(msie|trident)/i.test(navigator.userAgent);
  var urlParsingNode = document.createElement('a');
  var originURL;

  /**
  * Parse a URL to discover it's components
  *
  * @param {String} url The URL to be parsed
  * @returns {Object}
  */
  function resolveURL(url) {
    var href = url;

    if (msie) {
      // IE needs attribute set twice to normalize properties
      urlParsingNode.setAttribute('href', href);
      href = urlParsingNode.href;
    }

    urlParsingNode.setAttribute('href', href);

    // urlParsingNode provides the UrlUtils interface - http://url.spec.whatwg.org/#urlutils
    return {
      href: urlParsingNode.href,
      protocol: urlParsingNode.protocol ? urlParsingNode.protocol.replace(/:$/, '') : '',
      host: urlParsingNode.host,
      search: urlParsingNode.search ? urlParsingNode.search.replace(/^\?/, '') : '',
      hash: urlParsingNode.hash ? urlParsingNode.hash.replace(/^#/, '') : '',
      hostname: urlParsingNode.hostname,
      port: urlParsingNode.port,
      pathname: urlParsingNode.pathname.charAt(0) === '/' ? urlParsingNode.pathname : '/' + urlParsingNode.pathname
    };
  }

  originURL = resolveURL(window.location.href);

  /**
  * Determine if a URL shares the same origin as the current location
  *
  * @param {String} requestURL The URL to test
  * @returns {boolean} True if URL shares the same origin, otherwise false
  */
  return function isURLSameOrigin(requestURL) {
    var parsed = utils.isString(requestURL) ? resolveURL(requestURL) : requestURL;
    return parsed.protocol === originURL.protocol && parsed.host === originURL.host;
  };
}() :

// Non standard browser envs (web workers, react-native) lack needed support.
function nonStandardBrowserEnv() {
  return function isURLSameOrigin() {
    return true;
  };
}();

/***/ }),
/* 44 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

module.exports = function normalizeHeaderName(headers, normalizedName) {
  utils.forEach(headers, function processHeader(value, name) {
    if (name !== normalizedName && name.toUpperCase() === normalizedName.toUpperCase()) {
      headers[normalizedName] = value;
      delete headers[name];
    }
  });
};

/***/ }),
/* 45 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var utils = __webpack_require__(0);

/**
 * Parse headers into an object
 *
 * ```
 * Date: Wed, 27 Aug 2014 08:58:49 GMT
 * Content-Type: application/json
 * Connection: keep-alive
 * Transfer-Encoding: chunked
 * ```
 *
 * @param {String} headers Headers needing to be parsed
 * @returns {Object} Headers parsed into an object
 */
module.exports = function parseHeaders(headers) {
  var parsed = {};
  var key;
  var val;
  var i;

  if (!headers) {
    return parsed;
  }

  utils.forEach(headers.split('\n'), function parser(line) {
    i = line.indexOf(':');
    key = utils.trim(line.substr(0, i)).toLowerCase();
    val = utils.trim(line.substr(i + 1));

    if (key) {
      parsed[key] = parsed[key] ? parsed[key] + ', ' + val : val;
    }
  });

  return parsed;
};

/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Syntactic sugar for invoking a function and expanding an array for arguments.
 *
 * Common use case would be to use `Function.prototype.apply`.
 *
 *  ```js
 *  function f(x, y, z) {}
 *  var args = [1, 2, 3];
 *  f.apply(null, args);
 *  ```
 *
 * With `spread` this example can be re-written.
 *
 *  ```js
 *  spread(function(x, y, z) {})([1, 2, 3]);
 *  ```
 *
 * @param {Function} callback
 * @returns {Function}
 */

module.exports = function spread(callback) {
  return function wrap(arr) {
    return callback.apply(null, arr);
  };
};

/***/ }),
/* 47 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/*!
 * Determine if an object is a Buffer
 *
 * @author   Feross Aboukhadijeh <feross@feross.org> <http://feross.org>
 * @license  MIT
 */

// The _isBuffer check is for Safari 5-7 support, because it's missing
// Object.prototype.constructor. Remove this eventually
module.exports = function (obj) {
  return obj != null && (isBuffer(obj) || isSlowBuffer(obj) || !!obj._isBuffer);
};

function isBuffer(obj) {
  return !!obj.constructor && typeof obj.constructor.isBuffer === 'function' && obj.constructor.isBuffer(obj);
}

// For Node v0.10 support. Remove this eventually.
function isSlowBuffer(obj) {
  return typeof obj.readFloatLE === 'function' && typeof obj.slice === 'function' && isBuffer(obj.slice(0, 0));
}

/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global, process) {

(function (global, undefined) {
    "use strict";

    if (global.setImmediate) {
        return;
    }

    var nextHandle = 1; // Spec says greater than zero
    var tasksByHandle = {};
    var currentlyRunningATask = false;
    var doc = global.document;
    var registerImmediate;

    function setImmediate(callback) {
        // Callback can either be a function or a string
        if (typeof callback !== "function") {
            callback = new Function("" + callback);
        }
        // Copy function arguments
        var args = new Array(arguments.length - 1);
        for (var i = 0; i < args.length; i++) {
            args[i] = arguments[i + 1];
        }
        // Store and register the task
        var task = { callback: callback, args: args };
        tasksByHandle[nextHandle] = task;
        registerImmediate(nextHandle);
        return nextHandle++;
    }

    function clearImmediate(handle) {
        delete tasksByHandle[handle];
    }

    function run(task) {
        var callback = task.callback;
        var args = task.args;
        switch (args.length) {
            case 0:
                callback();
                break;
            case 1:
                callback(args[0]);
                break;
            case 2:
                callback(args[0], args[1]);
                break;
            case 3:
                callback(args[0], args[1], args[2]);
                break;
            default:
                callback.apply(undefined, args);
                break;
        }
    }

    function runIfPresent(handle) {
        // From the spec: "Wait until any invocations of this algorithm started before this one have completed."
        // So if we're currently running a task, we'll need to delay this invocation.
        if (currentlyRunningATask) {
            // Delay by doing a setTimeout. setImmediate was tried instead, but in Firefox 7 it generated a
            // "too much recursion" error.
            setTimeout(runIfPresent, 0, handle);
        } else {
            var task = tasksByHandle[handle];
            if (task) {
                currentlyRunningATask = true;
                try {
                    run(task);
                } finally {
                    clearImmediate(handle);
                    currentlyRunningATask = false;
                }
            }
        }
    }

    function installNextTickImplementation() {
        registerImmediate = function registerImmediate(handle) {
            process.nextTick(function () {
                runIfPresent(handle);
            });
        };
    }

    function canUsePostMessage() {
        // The test against `importScripts` prevents this implementation from being installed inside a web worker,
        // where `global.postMessage` means something completely different and can't be used for this purpose.
        if (global.postMessage && !global.importScripts) {
            var postMessageIsAsynchronous = true;
            var oldOnMessage = global.onmessage;
            global.onmessage = function () {
                postMessageIsAsynchronous = false;
            };
            global.postMessage("", "*");
            global.onmessage = oldOnMessage;
            return postMessageIsAsynchronous;
        }
    }

    function installPostMessageImplementation() {
        // Installs an event handler on `global` for the `message` event: see
        // * https://developer.mozilla.org/en/DOM/window.postMessage
        // * http://www.whatwg.org/specs/web-apps/current-work/multipage/comms.html#crossDocumentMessages

        var messagePrefix = "setImmediate$" + Math.random() + "$";
        var onGlobalMessage = function onGlobalMessage(event) {
            if (event.source === global && typeof event.data === "string" && event.data.indexOf(messagePrefix) === 0) {
                runIfPresent(+event.data.slice(messagePrefix.length));
            }
        };

        if (global.addEventListener) {
            global.addEventListener("message", onGlobalMessage, false);
        } else {
            global.attachEvent("onmessage", onGlobalMessage);
        }

        registerImmediate = function registerImmediate(handle) {
            global.postMessage(messagePrefix + handle, "*");
        };
    }

    function installMessageChannelImplementation() {
        var channel = new MessageChannel();
        channel.port1.onmessage = function (event) {
            var handle = event.data;
            runIfPresent(handle);
        };

        registerImmediate = function registerImmediate(handle) {
            channel.port2.postMessage(handle);
        };
    }

    function installReadyStateChangeImplementation() {
        var html = doc.documentElement;
        registerImmediate = function registerImmediate(handle) {
            // Create a <script> element; its readystatechange event will be fired asynchronously once it is inserted
            // into the document. Do so, thus queuing up the task. Remember to clean up once it's been called.
            var script = doc.createElement("script");
            script.onreadystatechange = function () {
                runIfPresent(handle);
                script.onreadystatechange = null;
                html.removeChild(script);
                script = null;
            };
            html.appendChild(script);
        };
    }

    function installSetTimeoutImplementation() {
        registerImmediate = function registerImmediate(handle) {
            setTimeout(runIfPresent, 0, handle);
        };
    }

    // If supported, we should attach to the prototype of global, since that is where setTimeout et al. live.
    var attachTo = Object.getPrototypeOf && Object.getPrototypeOf(global);
    attachTo = attachTo && attachTo.setTimeout ? attachTo : global;

    // Don't get fooled by e.g. browserify environments.
    if ({}.toString.call(global.process) === "[object process]") {
        // For Node.js before 0.9
        installNextTickImplementation();
    } else if (canUsePostMessage()) {
        // For non-IE10 modern browsers
        installPostMessageImplementation();
    } else if (global.MessageChannel) {
        // For web workers, where supported
        installMessageChannelImplementation();
    } else if (doc && "onreadystatechange" in doc.createElement("script")) {
        // For IE 6–8
        installReadyStateChangeImplementation();
    } else {
        // For older browsers
        installSetTimeoutImplementation();
    }

    attachTo.setImmediate = setImmediate;
    attachTo.clearImmediate = clearImmediate;
})(typeof self === "undefined" ? typeof global === "undefined" ? undefined : global : self);
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(14), __webpack_require__(4)))

/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var apply = Function.prototype.apply;

// DOM APIs, for completeness

exports.setTimeout = function () {
  return new Timeout(apply.call(setTimeout, window, arguments), clearTimeout);
};
exports.setInterval = function () {
  return new Timeout(apply.call(setInterval, window, arguments), clearInterval);
};
exports.clearTimeout = exports.clearInterval = function (timeout) {
  if (timeout) {
    timeout.close();
  }
};

function Timeout(id, clearFn) {
  this._id = id;
  this._clearFn = clearFn;
}
Timeout.prototype.unref = Timeout.prototype.ref = function () {};
Timeout.prototype.close = function () {
  this._clearFn.call(window, this._id);
};

// Does not start the time, just sets up the members needed.
exports.enroll = function (item, msecs) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = msecs;
};

exports.unenroll = function (item) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = -1;
};

exports._unrefActive = exports.active = function (item) {
  clearTimeout(item._idleTimeoutId);

  var msecs = item._idleTimeout;
  if (msecs >= 0) {
    item._idleTimeoutId = setTimeout(function onTimeout() {
      if (item._onTimeout) item._onTimeout();
    }, msecs);
  }
};

// setimmediate attaches itself to the global object
__webpack_require__(48);
exports.setImmediate = setImmediate;
exports.clearImmediate = clearImmediate;

/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Translates the list format produced by css-loader into something
 * easier to manipulate.
 */
module.exports = function listToStyles(parentId, list) {
  var styles = [];
  var newStyles = {};
  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = item[0];
    var css = item[1];
    var media = item[2];
    var sourceMap = item[3];
    var part = {
      id: parentId + ':' + i,
      css: css,
      media: media,
      sourceMap: sourceMap
    };
    if (!newStyles[id]) {
      styles.push(newStyles[id] = { id: id, parts: [part] });
    } else {
      newStyles[id].parts.push(part);
    }
  }
  return styles;
};

/***/ }),
/* 51 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _vue = __webpack_require__(5);

var _vue2 = _interopRequireDefault(_vue);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var TabModel = function () {
    function TabModel() {
        var _this = this;

        _classCallCheck(this, TabModel);

        this.openedTabs = new _vue2.default.kendo.data.ObservableArray([]);
        this.modelListeners = [];
        this.openedTabs.bind('change', function (e) {
            _this.modelListeners.forEach(function (l) {
                if (l.action === e.action) {
                    l.callback.call(_this, e, _this);
                }
            });
            _this.modelListeners = _this.modelListeners.filter(function (l) {
                return l.action !== e.action || !l.once;
            });
        });
    }

    _createClass(TabModel, [{
        key: 'on',
        value: function on(action, callback) {
            this.modelListeners.push({
                action: action,
                callback: callback,
                once: false
            });
        }
    }, {
        key: 'once',
        value: function once(action, callback) {
            this.modelListeners.push({
                action: action,
                callback: callback,
                once: true
            });
        }
    }, {
        key: 'add',
        value: function add() {
            var _openedTabs;

            (_openedTabs = this.openedTabs).push.apply(_openedTabs, arguments);
        }
    }, {
        key: 'get',
        value: function get(identifier) {
            if (typeof identifier === 'number') {
                if (identifier >= 0 && identifier < this.size()) {
                    return this.openedTabs[identifier];
                }

                return null;
            } else if ((typeof identifier === 'undefined' ? 'undefined' : _typeof(identifier)) === 'object' && identifier !== null) {
                if (identifier.tabId !== undefined) {
                    return this.find(function (item) {
                        return item.tabId == identifier.tabId;
                    });
                }
            }

            return null;
        }
    }, {
        key: 'findIndex',
        value: function findIndex(callback) {
            return this.toJSON().findIndex(callback);
        }
    }, {
        key: 'find',
        value: function find(callback) {
            return this.toJSON().find(callback);
        }
    }, {
        key: 'size',
        value: function size() {
            return this.openedTabs.length;
        }
    }, {
        key: 'isEmpty',
        value: function isEmpty() {
            return !this.size();
        }
    }, {
        key: 'join',
        value: function join(separator) {
            return this.openedTabs.join(separator);
        }
    }, {
        key: 'remove',
        value: function remove(identifier) {
            if (typeof identifier === 'number') {
                if (identifier >= 0 && identifier < this.size()) {
                    return this.openedTabs.splice(identifier, 1)[0];
                }
            } else if ((typeof identifier === 'undefined' ? 'undefined' : _typeof(identifier)) === 'object' && identifier !== null) {
                if (identifier.tabId !== undefined) {
                    var index = this.findIndex(function (t) {
                        return t.tabId == identifier.tabId;
                    });
                    if (index > -1) {
                        return this.openedTabs.splice(index, 1)[0];
                    }
                }
            }

            return null;
        }
    }, {
        key: 'removeAll',
        value: function removeAll() {
            this.openedTabs.splice(0, this.size());
        }
    }, {
        key: 'replace',
        value: function replace(identifier, newItem) {
            if (typeof identifier === 'number') {
                if (identifier >= 0 && identifier < this.size()) {
                    return this.openedTabs.splice(identifier, 1, newItem)[0];
                }
            } else if ((typeof identifier === 'undefined' ? 'undefined' : _typeof(identifier)) === 'object' && identifier !== null) {
                if (identifier.tabId !== undefined) {
                    var index = this.findIndex(function (t) {
                        return t.tabId == identifier.tabId;
                    });
                    if (index > -1) {
                        return this.openedTabs.splice(index, 1, newItem)[0];
                    }
                }
            }
        }
    }, {
        key: 'toJSON',
        value: function toJSON() {
            return this.openedTabs.toJSON();
        }
    }]);

    return TabModel;
}();

exports.default = TabModel;

/***/ }),
/* 52 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(5);

var _vue2 = _interopRequireDefault(_vue);

var _axios = __webpack_require__(8);

var _axios2 = _interopRequireDefault(_axios);

var _vueCustomElement = __webpack_require__(16);

var _vueCustomElement2 = _interopRequireDefault(_vueCustomElement);

var _vueGettext = __webpack_require__(17);

var _vueGettext2 = _interopRequireDefault(_vueGettext);

var _translation = __webpack_require__(23);

var _translation2 = _interopRequireDefault(_translation);

var _Authent = __webpack_require__(19);

var _Authent2 = _interopRequireDefault(_Authent);

var _Document = __webpack_require__(20);

var _Document2 = _interopRequireDefault(_Document);

var _documentList = __webpack_require__(21);

var _documentList2 = _interopRequireDefault(_documentList);

var _documentTabs = __webpack_require__(22);

var _documentTabs2 = _interopRequireDefault(_documentTabs);

var _AnakeenLoading = __webpack_require__(18);

var _AnakeenLoading2 = _interopRequireDefault(_AnakeenLoading);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var installCE = __webpack_require__(15);

installCE(window, {
    type: 'auto',
    noBuiltIn: true
});

_vue2.default.use(_vueGettext2.default, {
    availableLanguages: {
        en_US: 'English',
        fr_FR: 'Français'
    },
    defaultLanguage: 'fr_FR',
    languageVmMixin: {
        computed: {
            currentKebabCase: function adjustCulture() {
                return this.current.toLowerCase().replace('_', '-');
            }
        }
    },
    translations: _translation2.default,
    silent: true
});

_vue2.default.use(_vueCustomElement2.default);
_vue2.default.http = _vue2.default.prototype.$http = _axios2.default.create({
    baseURL: '/api/v1',
    timeout: 10000
});
_vue2.default.jQuery = _vue2.default.jquery = _vue2.default.prototype.$ = kendo.jQuery;
_vue2.default.prototype.$kendo = _vue2.default.kendo = kendo;

// import and register your component(s)


_vue2.default.customElement('ank-loading', _AnakeenLoading2.default);
_vue2.default.customElement('ank-authent', _Authent2.default);
_vue2.default.customElement('ank-document', _Document2.default);
_vue2.default.customElement('ank-document-list', _documentList2.default);
_vue2.default.customElement('ank-document-tabs', _documentTabs2.default);

/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "\n.documentsList__documents__wrapper {\n  background-color: #FBFBFB;\n}\n.documentsList__documents__wrapper .k-loading-mask {\n    background-color: #000000;\n    opacity: 0.6;\n    z-index: 2000;\n}\n.documentsList__documents__wrapper .k-loading-mask .k-loading-image:before, .documentsList__documents__wrapper .k-loading-mask .k-loading-image:after {\n      color: #FFFFFF;\n}\n.documentsList__documents__wrapper,\n.documentsList__documents {\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n}\n.documentsList__documents,\n.documentsList__documents__list {\n  background-color: #FBFBFB;\n}\n.documentsList__documents__logo {\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  height: 4.1667rem;\n  width: 100%;\n  margin-bottom: 0px;\n}\n.documentsList__documents__logo .documentsList__documents__logo__img {\n    width: auto;\n    height: 100%;\n}\n.documentsList__documents__header__label {\n  height: 35px;\n  overflow-x: hidden;\n  font-weight: 700;\n  text-transform: uppercase;\n  position: relative;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  color: #157EFB;\n  border-top: 1px solid #e6e6e6;\n}\n.documentsList__documents__search__wrapper {\n  background-color: #F5F5F5;\n  margin-bottom: 0.6667rem;\n  z-index: 0;\n}\n.documentsList__documents__search__button {\n  background-color: #F1F1F1;\n  color: #878787;\n  position: relative;\n  left: -1rem;\n  border-left: 0;\n}\n.documentsList__documents__search__button:hover {\n    background-color: #F1F1F1;\n    color: #000000;\n    cursor: pointer;\n}\n.documentsList__documents__search {\n  padding: 7px 10px;\n}\n.documentsList__documents__search > .input-group {\n    width: calc(100% + 1rem);\n}\n.documentsList__documents__search .documentsList__documents__search__keyword {\n    flex: 1;\n}\n.documentsList__documents__search .input-group-addon.material-icons.documentsList__documents__search__keyword__remove {\n    border: 0;\n    margin: auto;\n    width: 1rem;\n    height: 1rem;\n    line-height: 1rem;\n    vertical-align: middle;\n    display: inline-block;\n    border-radius: 50%;\n    background: transparent;\n    position: relative;\n    z-index: 200;\n    padding: 0;\n    left: -1.5rem;\n}\n.documentsList__documents__search .input-group-addon.material-icons.documentsList__documents__search__keyword__remove:hover {\n      background-color: #FF542C;\n      color: #FFFFFF;\n      cursor: pointer;\n}\n.documentsList__documents__summary__wrapper {\n  padding-bottom: 0.6667rem;\n  z-index: 0;\n}\n.documentsList__documents__summary {\n  padding-right: 10px;\n  text-align: right;\n  justify-content: center;\n  align-items: center;\n  border: none;\n  height: 28px;\n}\n.documentsList__documents__summary.k-pager-wrap {\n    overflow: visible;\n}\n.documentsList__documents__summary .k-pager-first, .documentsList__documents__summary .k-pager-last {\n    display: none;\n}\n.documentsList__documents__summary .k-pager-info.k-label {\n    font-weight: bold;\n    flex: 0 1 auto;\n}\n.documentsList__documents__summary .k-pager-nav + .k-pager-nav {\n    border-right: 0;\n}\n.documentsList__documents__summary .k-pager-nav {\n    background-color: none;\n}\n.documentsList__documents__list {\n  flex: 1;\n  border: none;\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  overflow-y: auto;\n}\n.documentsList__documentCard {\n  height: 45px;\n  position: relative;\n  border: none;\n  cursor: pointer;\n  line-height: 1.3em;\n  color: #505050;\n  background-size: 100% 100%;\n  opacity: 0.80;\n  border-bottom: 1px solid #ccc;\n}\n.documentsList__documentCard.documentsList__documentCard__full {\n    height: 60px;\n}\n.documentsList__documentCard:hover {\n    color: #00b4fc;\n}\n.documentsList__documentCard.k-state-selected {\n    box-shadow: inset 3px 0px 0px 0px #8AE234;\n    background-color: #F4F4F4;\n    color: #00b4fc;\n    font-weight: 600;\n    opacity: 1;\n}\n.documentsList__documentCard__body {\n  height: 100%;\n  padding: 13px 15px 10px 20px;\n  text-transform: uppercase;\n}\n.documentsList__documentCard:first-child {\n  border-top: 1px solid #ccc;\n}\n.documentsList__documentCard__heading {\n  text-overflow: ellipsis;\n  overflow: hidden;\n  white-space: nowrap;\n}\n.documentsList__documentCard__heading > span {\n    position: relative;\n    bottom: -2px;\n}\n.documentsList__documentCard__heading__state {\n  font-size: 0.95rem;\n  text-transform: capitalize;\n  display: flex;\n  align-items: center;\n  padding-left: 2.5rem;\n}\n.documentsList__documentCard__heading__state .documentsList__documentCard__heading__state--label {\n    margin-top: 1%;\n}\n.documentsList__documentCard__heading__state .documentsList__documentCard__heading__state--color {\n    border-radius: 20%;\n    width: 0.6rem;\n    height: 0.7rem;\n    background-color: black;\n    margin-right: 3%;\n    margin-top: 1%;\n}\n.documentsList__documentCard--opened .documentsList__documentCard__heading__content,\n.documentsList__documentCard__heading__state,\n.documentsList__documentCard .documentsList__documentCard__heading__content {\n  color: #505050;\n  font-weight: 400;\n}\n.documentsList__documentCard__heading__content_icon {\n  padding: 0 1em 0 0;\n  width: auto;\n  height: 1.3rem;\n}\n.documentsList__documents__list__pager_wraper {\n  z-index: 0;\n  display: flex;\n  flex-direction: row;\n  height: 2.9167rem;\n  border-top: 0.0833rem solid #dde2e7;\n}\n.documentsList__documents__list__pager_wraper .documentsList__documents__list__pagerCounter {\n    margin-top: -0.0833rem;\n    flex: 0.13;\n}\n.documentsList__documents__list__pager_wraper .documentsList__documents__list__pagerCounter .k-dropdown-wrap {\n      background-color: #BFBFBF;\n      color: #FFFFFF;\n      border: 0;\n      border-radius: 0;\n      display: flex;\n      align-items: center;\n}\n.documentsList__documents__list__pager_wraper .documentsList__documents__list__pagerCounter .k-dropdown-wrap .k-input {\n        justify-content: center;\n        font-weight: bold;\n        padding-right: .5rem;\n}\n.documentsList__documents__list__pager_wraper .documentsList__documents__list__pagerCounter .k-dropdown-wrap .k-select {\n        padding-left: 0;\n}\n.documentsList__documents__list__pager_wraper .documentsList__documents__list__pagerCounter .k-dropdown-wrap .k-select .k-icon {\n          color: #FFFFFF;\n}\n.documentsList__documents__list__pager_wraper .k-pager-wrap {\n    padding: 0;\n}\n.documentsList__documents__list__pager_wraper .k-pager-wrap .k-link {\n      height: 100%;\n}\n.documentsList__documents__list__pager {\n  display: flex;\n  flex: 0.87;\n  padding-left: 0;\n  padding-right: 0;\n  border: 0;\n}\n.documentsList__documents__list__pager .k-pager-input.k-label {\n    height: 100%;\n    margin: 0;\n    flex: 0.48;\n}\n.documentsList__documents__list__pager .k-pager-input.k-label .k-textbox {\n      flex: 0.74;\n      text-align: center;\n      margin: 0 15% 0 0;\n      height: calc(100% - 2px);\n      border-radius: 0;\n      border-top-style: none;\n      border-bottom-style: none;\n      border-right-style: dashed;\n}\n.documentsList__documents__list__pager .k-pager-input.k-label .k-textbox:hover {\n        border-top-style: solid;\n        border-bottom-style: solid;\n}\n.documentsList__documents__list__pager .k-pager-first, .documentsList__documents__list__pager .k-pager-last {\n    background-color: #E0E0E0;\n}\n.documentsList__documents__list__pager .k-pager-nav {\n    display: flex;\n    flex: 0.2;\n    padding: 0;\n}\n.documentsList__documents__pagination__list.k-popup.k-group.k-reset {\n  width: auto !important;\n}\n.documentsList__documents__pagination__list.k-popup.k-group.k-reset .k-list-scroller .k-list .k-item {\n    padding: 0;\n}\n.documentsList__documents__pagination__pageSize {\n  padding: 10px 20px;\n  border-top: 1px solid #E6E6E6;\n  width: 100%;\n}\n", ""]);

// exports


/***/ }),
/* 54 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "\n.authent-form {\n  padding: 4rem;\n}\n.authent-form--forget {\n  display: flex;\n  flex-direction: column;\n}\n.authent-buttons {\n  text-align: center;\n}\n.authent-login-button {\n  margin: 1rem;\n  align-self: center;\n}\n.label {\n  padding-top: 1rem;\n}\n.message--error {\n  text-align: center;\n  color: red;\n}\n.message--success {\n  color: #099408;\n}\n.authent-bottom {\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  background-color: transparent;\n}\n.authent-bottom .k-dropdown-wrap, .authent-bottom .k-button {\n    background-color: transparent;\n}\n.authent-bottom > button, .authent-bottom > select {\n    flex-grow: 1;\n}\n.authent-bottom .authent-help-button {\n    margin-right: 0;\n}\n.authent-help {\n  width: 40rem;\n  white-space: pre-line;\n}\n", ""]);

// exports


/***/ }),
/* 55 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "", ""]);

// exports


/***/ }),
/* 56 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "\n.anakeenLoading--logo.white-logo .main-letter[data-v-3b62e416] {\n  fill: #93C648;\n}\n.anakeenLoading--logo.white-logo .letter[data-v-3b62e416] {\n  fill: #FFF;\n}\n.anakeenLoading--logo.white-logo .st0[data-v-3b62e416] {\n  fill: #FFF;\n  stroke: #FFF;\n  stroke-width: 0.25;\n  stroke-miterlimit: 10;\n}\n.anakeenLoading--logo.black-logo .main-letter[data-v-3b62e416] {\n  fill: #93C648;\n}\n.anakeenLoading--logo.black-logo .letter[data-v-3b62e416] {\n  fill: #263438;\n}\n.anakeenLoading--logo.black-logo .st0[data-v-3b62e416] {\n  fill: #263438;\n  stroke: #263438;\n  stroke-width: 0.25;\n  stroke-miterlimit: 10;\n}\n.anakeenLoading--logo.black-logo.isotype path.line[data-v-3b62e416] {\n  stroke: #000;\n}\n.anakeenLoading--logo.loader-container[data-v-3b62e416] {\n  padding: 3em;\n  overflow: hidden;\n}\n.anakeenLoading--logo.loader-container .text-logo[data-v-3b62e416], .anakeenLoading--logo.loader-container .main-logo[data-v-3b62e416] {\n    margin: 0 auto;\n    width: 100%;\n}\n.anakeenLoading--logo.loader-container .text-logo svg[data-v-3b62e416], .anakeenLoading--logo.loader-container .main-logo svg[data-v-3b62e416] {\n      overflow: visible;\n      width: 100%;\n}\n.anakeenLoading--logo.loader-container .text-logo _[data-v-3b62e416]:-ms-input-placeholder, :root .anakeenLoading--logo.loader-container .text-logo[data-v-3b62e416] {\n    margin-bottom: -2.2rem;\n}\n.anakeenLoading--logo.loader-container.isotype[data-v-3b62e416] {\n    height: 20%;\n}\n.anakeenLoading--logo.loader-container.isotype .main-logo[data-v-3b62e416] {\n      height: 100%;\n}\n.anakeenLoading--logo.loader-container.isotype .main-logo svg.bounce[data-v-3b62e416] {\n        width: 20%;\n        margin: 0 auto;\n        display: block;\n}\n.anakeenLoading--logo.loader-container.isotype .main-logo svg.bounce > *[data-v-3b62e416] {\n          animation: bounce-isotype infinite 800ms alternate ease-in-out;\n}\n.anakeenLoading--logo .main-logo[data-v-3b62e416] {\n  position: relative;\n  animation: text-appear infinite 2000ms normal ease-in;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416] {\n    animation: bounce infinite 2500ms normal ease-in-out;\n    opacity: 1;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(1) {\n      animation-delay: 50ms;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(2) {\n      animation-delay: 100ms;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(3) {\n      animation-delay: 150ms;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(4) {\n      animation-delay: 200ms;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(5) {\n      animation-delay: 250ms;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(6) {\n      animation-delay: 300ms;\n}\n.anakeenLoading--logo .main-logo svg.bounce > *[data-v-3b62e416]:nth-child(7) {\n      animation-delay: 350ms;\n}\n@keyframes text-appear {\n0% {\n    opacity: 1;\n}\n50% {\n    opacity: 0;\n}\n100% {\n    opacity: 0;\n}\n}\n@keyframes slide-right {\n0% {\n    opacity: 0;\n    transform: translate(-20%, 0);\n}\n35% {\n    opacity: 0;\n    transform: translate(-20%, 0);\n}\n50% {\n    opacity: 1;\n    transform: translate(-20%, 0);\n}\n75% {\n    transform: translate(100%, 0);\n}\n100% {\n    opacity: 1;\n}\n}\n@keyframes bounce {\n0% {\n    transform: scale(1);\n}\n70% {\n    transform: scale(1);\n    opacity: 1;\n}\n85% {\n    transform: scale(1.1);\n    opacity: 0.5;\n}\n100% {\n    transform: scale(1);\n    opacity: 1;\n}\n}\n@keyframes bounce-isotype {\n0% {\n    transform: scale(1);\n    opacity: 1;\n}\n60% {\n    transform: scale(1);\n    opacity: 1;\n}\n100% {\n    transform: scale(1.1);\n    opacity: 0.5;\n}\n}\n", ""]);

// exports


/***/ }),
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "\n.documentsList__documentsTabs__wrapper {\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n  height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons {\n    display: flex;\n    position: relative;\n    width: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__custom__slot {\n      z-index: 200;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator {\n      position: absolute;\n      right: 0;\n      display: flex;\n      z-index: 200;\n      height: 3.5rem;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button {\n        border: 0;\n        border-radius: 0;\n        height: 3.5rem;\n        width: 3.5rem;\n        background-color: #e6e6e6 !important;\n        padding: 0;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__close {\n          background-color: #969696 !important;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__close .k-icon {\n            color: #FFFFFF;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__close:hover {\n            cursor: pointer;\n            background-color: #FF542C !important;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.k-state-hover {\n          color: #157EFB;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__dropdown__button > .k-dropdown-wrap {\n          background: none;\n          display: flex;\n          justify-content: center;\n          align-items: center;\n          border: 0;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__dropdown__button > .k-dropdown-wrap > .k-select {\n            display: none;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__dropdown__button > .k-dropdown-wrap > .k-input {\n            display: flex;\n            justify-content: center;\n            align-items: center;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__dropdown__button > .k-dropdown-wrap > .k-input i {\n              color: #505050;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__dropdown__button > .k-dropdown-wrap.k-state-hover {\n            cursor: pointer;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__buttons .documentsList__documentsTabs__tabs__paginator .documentsList__documentsTabs__tabs__paginator__button.documentsList__documentsTabs__tabs__paginator__dropdown__button > .k-dropdown-wrap.k-state-hover .k-input > .k-icon {\n              color: #00b4fc;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__empty {\n    display: flex;\n    justify-content: center;\n    align-items: center;\n    flex: 1;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__empty .documentsList__documentsTabs__empty__img {\n      width: 225px;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper {\n    position: absolute;\n    width: 100%;\n    height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper {\n      height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs {\n        height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-content {\n          height: 100%;\n          overflow: hidden;\n          padding: 0;\n          border-top: none;\n          background-color: #F3F3F3;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-content .documentsList__documentsTabs__tab__content__wrapper {\n            height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-content .documentsList__documentsTabs__tab__content__wrapper .documentsList__documentsTabs__tab__content--document > iframe {\n              height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-content .documentsList__documentsTabs__tab__content__wrapper .documentsList__documentsTabs__tab__content--loading > div {\n              height: 100%;\n              display: flex;\n              flex-direction: column;\n              justify-content: center;\n              align-items: center;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-next, .documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-prev {\n          display: flex !important;\n          z-index: 200;\n          border: 0;\n          border-radius: 0;\n          height: 3.5rem;\n          width: 3.5rem;\n          background-color: #e6e6e6 !important;\n          padding: 0;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-next.documentsList__documentsTabs__tabs__paginator__close, .documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-prev.documentsList__documentsTabs__tabs__paginator__close {\n            background-color: #969696 !important;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-next.documentsList__documentsTabs__tabs__paginator__close .k-icon, .documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-prev.documentsList__documentsTabs__tabs__paginator__close .k-icon {\n              color: #FFFFFF;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-next.documentsList__documentsTabs__tabs__paginator__close:hover, .documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-prev.documentsList__documentsTabs__tabs__paginator__close:hover {\n              cursor: pointer;\n              background-color: #FF542C !important;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-next.k-state-hover, .documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-prev.k-state-hover {\n            color: #157EFB;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-prev {\n          left: auto;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items {\n          height: 3.5rem;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > #documentsList__documentsTabs__new__tab__button {\n            width: 2.75rem;\n            height: 2.35rem;\n            border: 1px solid lightgray;\n            background: #FFFFFF;\n            display: flex;\n            position: relative;\n            left: .4rem;\n            top: .6rem;\n            justify-content: center;\n            align-items: center;\n            cursor: pointer;\n            transform: skew(25deg);\n            transition: background-color .2s ease;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > #documentsList__documentsTabs__new__tab__button:hover {\n              background-color: #F3F3F3;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > #documentsList__documentsTabs__new__tab__button i.material-icons {\n              transform: skew(-25deg);\n              font-size: 1.5rem;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item {\n            display: flex;\n            justify-content: space-between;\n            width: 200px;\n            background-color: #E6E6E6;\n            margin-right: 2px;\n            border: 1px solid lightgrey;\n            border-radius: 0;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item .tab__document__header__content .tab__document__title {\n              color: #505050;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-active {\n              box-shadow: inset 0 2px 0px 0px #8ae234;\n              opacity: 1;\n              background-color: #FFFFFF;\n              border-bottom-color: transparent;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-active .tab__document__header__content .tab__document__title {\n                font-weight: 700;\n                color: #00b4fc;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-active > .tab__document__header__content > .k-link[data-type=remove] {\n                box-shadow: inset 0 2px 0px 0px #8ae234;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-active > .tab__document__header__content > .k-link[data-type=remove] .k-icon {\n                  color: #ff542c;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-hover > .k-link > .tab__document__header__content .tab__document__title {\n              color: #00b4fc;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-hover > .k-link > .tab__document__header__content.k-link[data-type=remove] {\n              background-color: #E6E6E6;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item.k-state-hover > .k-link > .tab__document__header__content.k-link[data-type=remove] .k-icon {\n                color: #ff542c;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link {\n              padding: 0 0 0 1rem;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content {\n                width: 100%;\n                height: 100%;\n                display: flex;\n                justify-content: space-between;\n                align-items: center;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content:hover {\n                  text-decoration: none;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content .tab__document__icon {\n                  width: auto;\n                  height: 1.3rem;\n                  margin-right: 5px;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content .tab__document__title {\n                  width: 120px;\n                  overflow: hidden;\n                  white-space: nowrap;\n                  text-overflow: ellipsis;\n                  text-transform: uppercase;\n                  padding-top: 2px;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content > .k-link[data-type=remove] {\n                  height: 100%;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content > .k-link[data-type=remove]:hover {\n                    background-color: #ff542c;\n}\n.documentsList__documentsTabs__wrapper .documentsList__documentsTabs__tabs__wrapper .k-tabstrip-wrapper .documentsList__documentsTabs__tabs > .k-tabstrip-items > .k-item > .k-link .tab__document__header__content > .k-link[data-type=remove]:hover .k-icon {\n                      color: #ffffff;\n}\n.documentsList__documentsTabs__tabsList__list {\n  padding: 0;\n}\n.documentsList__documentsTabs__tabsList__list .documentsList__documentsTabs__tabsList__list__close__all {\n    display: flex;\n    justify-content: center;\n    align-items: center;\n    background-color: transparent;\n    border: none;\n    border-bottom: 1px solid lightgrey;\n    color: #FF542C;\n    text-transform: uppercase;\n    width: 100%;\n    height: 40px;\n    cursor: pointer;\n    transition: background-color .2s ease;\n}\n.documentsList__documentsTabs__tabsList__list .documentsList__documentsTabs__tabsList__list__close__all:hover {\n      background-color: #F3F3F3;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller {\n    overflow-x: hidden;\n    width: auto;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item {\n      padding: 0 0 0 .5rem;\n      border-bottom: 1px solid #e6e6e6;\n      background-color: #FFFFFF;\n      text-transform: uppercase;\n      color: black;\n      font-size: 1rem;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem {\n        display: flex;\n        justify-content: space-between;\n        align-items: center;\n        width: 200px;\n        height: 40px;\n        position: relative;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__heading {\n          flex: 1;\n          overflow: hidden;\n          text-overflow: ellipsis;\n          white-space: nowrap;\n          display: flex;\n          align-items: center;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__heading .documentTabs__openedTab__listItem__heading--title {\n            margin-left: .8rem;\n            display: flex;\n            justify-content: center;\n            align-items: center;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__heading .documentTabs__openedTab__listItem__heading--icon {\n            width: 2rem;\n            height: 2rem;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__close {\n          height: 100%;\n          padding: 0 .5rem;\n          display: flex;\n          align-items: center;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__close i {\n            font-size: 1.3rem;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__close:hover {\n            background-color: #FF542C;\n}\n.documentsList__documentsTabs__tabsList__list .k-list-scroller .k-item .documentTabs__openedTab__listItem .documentTabs__openedTab__listItem__close:hover i {\n              color: white;\n}\n", ""]);

// exports


/***/ }),
/* 58 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "", ""]);

// exports


/***/ }),
/* 59 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "", ""]);

// exports


/***/ }),
/* 60 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(undefined);
// imports


// module
exports.push([module.i, "\n.input-group-btn > .btn.btn-reveal {\n  position: absolute;\n  right: 0;\n  top: 0;\n}\n", ""]);

// exports


/***/ }),
/* 61 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(true);
// imports


// module
exports.push([module.i, "\niframe.apDocumentWrapper[data-v-56650ed6] {\n    width:100%;\n    border:0;\n    padding:0;\n    margin:0;\n}\n", "", {"version":3,"sources":["/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/Document/Document.vue?df0fa87a"],"names":[],"mappings":";AAQA;IACA,WAAA;IACA,SAAA;IACA,UAAA;IACA,SAAA;CACA","file":"Document.vue","sourcesContent":["<template>\n    <iframe style=\"visibility:hidden\" ref=\"iDocument\" src=\"api/v1/documents/0.html\" class=\"apDocumentWrapper\"></iframe>\n</template>\n\n<script src=\"./Document.component.js\" ></script>\n\n<!-- Add \"scoped\" attribute to limit CSS to this component only -->\n<style scoped>\n    iframe.apDocumentWrapper {\n        width:100%;\n        border:0;\n        padding:0;\n        margin:0;\n    }\n</style>\n\n\n<style lang=\"scss\" >\n    @import \"./Document.scss\";\n</style>\n"],"sourceRoot":""}]);

// exports


/***/ }),
/* 62 */
/***/ (function(module, exports) {

module.exports = "<div class=\"documentsList__documentCard#if (data.properties.state){# documentsList__documentCard__full#}#\">\n    <div class=\"documentsList__documentCard__body\">\n        <div class=\"documentsList__documentCard__heading\">\n            <img class=\"documentsList__documentCard__heading__content_icon\" src=\"#: properties.icon#\"\n                 alt=\"#: properties.title# image\"/>\n            <span>#: properties.title#</span>\n        </div>\n        # if (data.properties.state) {#\n            <div class=\"documentsList__documentCard__heading__state\">\n                <span class=\"documentsList__documentCard__heading__state--color\"\n                      style=\"background-color: #= properties.state.color#\"></span>\n                <span class=\"documentsList__documentCard__heading__state--label\">\n                    #: properties.state.displayValue#\n                </span>\n            </div>\n        #}#\n    </div>\n</div>"

/***/ }),
/* 63 */
/***/ (function(module, exports) {

module.exports = "<div class=\"documentTabs__openedTab__listItem\" data-docid=\"#=data.initid#\">\n    <div class=\"documentTabs__openedTab__listItem__heading\">\n        <img class=\"documentTabs__openedTab__listItem__heading--icon\" src=\"#= data.icon#\"/>\n        <span class=\"documentTabs__openedTab__listItem__heading--title\">#= data.title#</span>\n    </div>\n    <div class=\"documentTabs__openedTab__listItem__close\">\n        <i class=\"material-icons\">close</i>\n    </div>\n</div>"

/***/ }),
/* 64 */
/***/ (function(module, exports) {

module.exports = "<div class=\"documentsList__documentsTabs__tab__content__wrapper\">\n    <ank-document class=\"documentsList__documentsTabs__tab__content--document\" style=\"display: none\" initid=\"#: initid#\"\n                 # if (data.viewid) { #viewid=\"#= viewid#\" # }#\n                 # if (data.revision) { #revision=\"#=revision#\" #}#>\n    </ank-document>\n    <ank-loading class=\"documentsList__documentsTabs__tab__content--loading\" width=\"auto\" height=\"5rem\" color=\"black\"></ank-loading>\n</div>"

/***/ }),
/* 65 */
/***/ (function(module, exports) {

module.exports = "<a class=\"tab__document__header__content\">\n    # if (data.icon) { #\n    <img class=\"tab__document__icon\" src=\"#= icon#\" />\n    # } else { #\n    <i class=\"fa fa-spinner fa-pulse tab__document__icon\"></i>\n    #}#\n    <span class=\"tab__document__title\"># if (data.title) { # #= title# # } else {# Chargement en cours...#}#</span>\n</a>"

/***/ }),
/* 66 */
/***/ (function(module, exports) {

module.exports = "<ank-welcome-tab #if (data.welcomeMessage) {# welcome-message=\"#=welcomeMessage#\" #}#\n                #if (data.promptMessage) {# prompt-message=\"#=promptMessage#\" #}#\n                user-name=\"#=user#\"\n                collections=\"#: collections#\"></ank-welcome-tab>"

/***/ }),
/* 67 */
/***/ (function(module, exports) {

module.exports = "<span class=\"tab__document__header__content\">\n    <img class=\"tab__document__icon\" src=\"api/v1/images/assets/sizes/15x15/anakeen_monogramme_S.png\"\n         style=\"position:relative; top: 0.1666rem;\"/>\n    <span class=\"tab__document__title\">#= title#</span>\n</span>"

/***/ }),
/* 68 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(82)

var Component = __webpack_require__(3)(
  /* script */
  __webpack_require__(26),
  /* template */
  __webpack_require__(74),
  /* scopeId */
  null,
  /* cssModules */
  null
)
Component.options.__file = "/mnt/c/Users/Charles/Documents/git/anakeen-ui/Document-uis/src/vendor/Anakeen/Components/Authent/AuthentPassword.vue"
if (Component.esModule && Object.keys(Component.esModule).some(function (key) {return key !== "default" && key !== "__esModule"})) {console.error("named exports are not supported in *.vue files.")}
if (Component.options.functional) {console.error("[vue-loader] AuthentPassword.vue: functional components are not supported with templates, they should use render functions.")}

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-74934f0e", Component.options)
  } else {
    hotAPI.reload("data-v-74934f0e", Component.options)
  }
})()}

module.exports = Component.exports


/***/ }),
/* 69 */
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    ref: "wrapper",
    staticClass: "documentsList__documents__wrapper"
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.collection),
      expression: "collection"
    }],
    staticClass: "documentsList__documents"
  }, [_c('div', {
    staticClass: "documentsList__documents__logo"
  }, [_c('img', {
    staticClass: "documentsList__documents__logo__img",
    attrs: {
      "src": _vm.logoUrl
    }
  })]), _vm._v(" "), _c('div', {
    staticClass: "documentsList__documents__header__wrapper"
  }, [_c('div', {
    staticClass: "documentsList__documents__header"
  }, [_c('div', {
    staticClass: "documentsList__documents__header__label"
  }, [_vm._v("\n                    " + _vm._s(_vm.collection ? _vm.collection.html_label : '') + "\n                ")])])]), _vm._v(" "), _c('div', {
    staticClass: "documentsList__documents__search__wrapper"
  }, [_c('div', {
    staticClass: "documentsList__documents__search"
  }, [_c('div', {
    staticClass: "input-group"
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.filterInput),
      expression: "filterInput"
    }],
    staticClass: "form-control documentsList__documents__search__keyword",
    attrs: {
      "type": "text",
      "placeholder": _vm.translations.searchPlaceholder
    },
    domProps: {
      "value": (_vm.filterInput)
    },
    on: {
      "change": function($event) {
        _vm.filterDocumentsList(_vm.filterInput)
      },
      "input": function($event) {
        if ($event.target.composing) { return; }
        _vm.filterInput = $event.target.value
      }
    }
  }), _vm._v(" "), _c('i', {
    staticClass: "input-group-addon material-icons documentsList__documents__search__keyword__remove",
    on: {
      "click": _vm.clearDocumentsListFilter
    }
  }, [_vm._v("\n                        close\n                    ")]), _vm._v(" "), _c('i', {
    staticClass: "input-group-addon material-icons documentsList__documents__search__button",
    on: {
      "click": function($event) {
        _vm.filterDocumentsList(_vm.filterInput)
      }
    }
  }, [_vm._v("\n                        search\n                    ")])])])]), _vm._v(" "), _c('div', {
    staticClass: "documentsList__documents__summary__wrapper"
  }, [_c('div', {
    ref: "summaryPager",
    staticClass: "documentsList__documents__summary"
  })]), _vm._v(" "), _c('div', {
    ref: "listView",
    staticClass: "documentsList__documents__list"
  }), _vm._v(" "), _c('div', {
    staticClass: "documentsList__documents__list__pager_wraper"
  }, [_c('input', {
    ref: "pagerCounter",
    staticClass: "documentsList__documents__list__pagerCounter"
  }), _vm._v(" "), _c('div', {
    ref: "pager",
    staticClass: "documentsList__documents__list__pager"
  })])])])
},staticRenderFns: []}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-143db676", module.exports)
  }
}

/***/ }),
/* 70 */
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('section', {
    ref: "authentComponent",
    staticClass: "authent-component"
  }, [(!_vm.resetPassword) ? _c('div', {
    staticClass: "euthent-form-connect"
  }, [_c('form', {
    ref: "authentForm",
    staticClass: "authent-form"
  }, [_c('label', {
    directives: [{
      name: "translate",
      rawName: "v-translate"
    }],
    staticClass: "label",
    attrs: {
      "for": "login",
      "translate-context": "Authent"
    }
  }, [_vm._v("Identifier :")]), _vm._v(" "), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.login),
      expression: "login"
    }],
    staticClass: "authent-login form-control k-textbox",
    attrs: {
      "id": "login",
      "type": "text",
      "required": "",
      "autocapitalize": "off",
      "autocorrect": "off",
      "spellcheck": "false",
      "placeholder": _vm.translations.loginPlaceHolder,
      "validationmessage": _vm.translations.validationMessageIdentifier
    },
    domProps: {
      "value": (_vm.login)
    },
    on: {
      "input": function($event) {
        if ($event.target.composing) { return; }
        _vm.login = $event.target.value
      }
    }
  }), _vm._v(" "), _c('ank-password', {
    attrs: {
      "label": _vm.translations.passwordLabel,
      "validationMessage": _vm.translations.validationMessagePassword,
      "placeholder": _vm.translations.passwordPlaceHolder
    },
    model: {
      value: (_vm.pwd),
      callback: function($$v) {
        _vm.pwd = $$v
      },
      expression: "pwd"
    }
  }), _vm._v(" "), (_vm.wrongPassword) ? _c('div', {
    staticClass: "message message--error"
  }, [_vm._v("\n                " + _vm._s(_vm.authentError) + "\n            ")]) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "authent-buttons"
  }, [_c('button', {
    ref: "loginButton",
    staticClass: "authent-login-button k-primary",
    attrs: {
      "type": "submit"
    }
  }, [_c('translate', {
    attrs: {
      "translate-context": "Authent"
    }
  }, [_vm._v("Sign in")])], 1)])], 1), _vm._v(" "), _c('div', {
    ref: "authentHelpContent",
    staticClass: "authent-help",
    staticStyle: {
      "display": "none"
    }
  }, [_c('p', {
    directives: [{
      name: "translate",
      rawName: "v-translate"
    }],
    staticClass: "label",
    attrs: {
      "for": "password",
      "translate-context": "Authent"
    }
  }, [_vm._v("Help Content")])]), _vm._v(" "), _c('form', {
    ref: "authentForgetForm",
    staticClass: "authent-form authent-form--forget",
    staticStyle: {
      "display": "none"
    }
  }, [_c('label', {
    directives: [{
      name: "translate",
      rawName: "v-translate"
    }],
    staticClass: "label",
    attrs: {
      "for": "forgetlogin",
      "translate-context": "Authent"
    }
  }, [_vm._v("Enter identifier or email address :")]), _vm._v(" "), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.login),
      expression: "login"
    }],
    staticClass: "authent-login form-control k-textbox",
    attrs: {
      "id": "forgetlogin",
      "type": "text",
      "required": "",
      "autocapitalize": "off",
      "autocorrect": "off",
      "spellcheck": "false",
      "placeholder": _vm.translations.forgetPlaceHolder
    },
    domProps: {
      "value": (_vm.login)
    },
    on: {
      "input": function($event) {
        if ($event.target.composing) { return; }
        _vm.login = $event.target.value
      }
    }
  }), _vm._v(" "), (_vm.forgetStatusFailed) ? _c('div', {
    staticClass: "message message--error"
  }, [_vm._v("\n                " + _vm._s(_vm.forgetError) + "\n            ")]) : _vm._e(), _vm._v(" "), (!_vm.forgetStatusFailed) ? _c('div', {
    staticClass: "message message--success"
  }, [_vm._v("\n                " + _vm._s(_vm.forgetSuccess) + "\n            ")]) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "authent-buttons"
  }, [_c('button', {
    ref: "authentForgetSubmit",
    staticClass: "authent-login-button k-primary",
    attrs: {
      "type": "submit"
    }
  }, [_c('translate', {
    attrs: {
      "translate-context": "Authent"
    }
  }, [_vm._v("Send reset password ask")])], 1)])])]) : _vm._e(), _vm._v(" "), (_vm.resetPassword) ? _c('form', {
    ref: "authentResetPasswordForm",
    staticClass: "authent-form authent-form--resetpassword"
  }, [(_vm.resetStatusFailed) ? _c('ank-password', {
    attrs: {
      "label": _vm.translations.resetPasswordLabel,
      "validationMessage": _vm.translations.validationMessagePassword,
      "placeholder": _vm.translations.passwordPlaceHolder
    },
    model: {
      value: (_vm.resetPwd1),
      callback: function($$v) {
        _vm.resetPwd1 = $$v
      },
      expression: "resetPwd1"
    }
  }) : _vm._e(), _vm._v(" "), (_vm.resetStatusFailed) ? _c('ank-password', {
    attrs: {
      "label": _vm.translations.confirmPasswordLabel,
      "validationMessage": _vm.translations.validationMessagePassword,
      "placeholder": _vm.translations.passwordPlaceHolder
    },
    model: {
      value: (_vm.resetPwd2),
      callback: function($$v) {
        _vm.resetPwd2 = $$v
      },
      expression: "resetPwd2"
    }
  }) : _vm._e(), _vm._v(" "), (_vm.resetStatusFailed) ? _c('div', {
    staticClass: "message message--error"
  }, [_vm._v("\n            " + _vm._s(_vm.resetError) + "\n        ")]) : _vm._e(), _vm._v(" "), (!_vm.resetStatusFailed) ? _c('div', {
    staticClass: "message message--success"
  }, [_vm._v("\n            " + _vm._s(_vm.resetSuccess) + "\n        ")]) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "authent-buttons"
  }, [(_vm.resetStatusFailed) ? _c('button', {
    ref: "authentResetSubmit",
    staticClass: "authent-login-button k-primary",
    attrs: {
      "type": "submit"
    }
  }, [_c('translate', {
    attrs: {
      "translate-context": "Authent"
    }
  }, [_vm._v("Send reset password ask")])], 1) : _vm._e(), _vm._v(" "), (!_vm.resetStatusFailed) ? _c('button', {
    ref: "authentGoHome",
    staticClass: "authent-login-home k-primary"
  }, [_c('translate', {
    attrs: {
      "translate-context": "Authent"
    }
  }, [_vm._v("Go back to home page")])], 1) : _vm._e()])], 1) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "authent-bottom"
  }, [_c('select', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.$language.current),
      expression: "$language.current"
    }],
    ref: "authentLocale",
    staticClass: "authent-locale",
    attrs: {
      "name": "language"
    },
    on: {
      "change": function($event) {
        var $$selectedVal = Array.prototype.filter.call($event.target.options, function(o) {
          return o.selected
        }).map(function(o) {
          var val = "_value" in o ? o._value : o.value;
          return val
        });
        _vm.$set(_vm.$language, "current", $event.target.multiple ? $$selectedVal : $$selectedVal[0])
      }
    }
  }, _vm._l((_vm.availableLanguages), function(language) {
    return _c('option', {
      domProps: {
        "value": language.key
      }
    }, [_vm._v(_vm._s(language.label))])
  })), _vm._v(" "), (!_vm.resetPassword) ? _c('button', {
    ref: "authentHelpButton",
    staticClass: "authent-help-button k-secondary"
  }, [_c('translate', {
    attrs: {
      "translate-context": "Authent"
    }
  }, [_vm._v("Help")])], 1) : _vm._e(), _vm._v(" "), (!_vm.resetPassword) ? _c('button', {
    ref: "authentForgetButton",
    staticClass: "authent-forget-button k-secondary"
  }, [_c('translate', {
    attrs: {
      "translate-context": "Authent"
    }
  }, [_vm._v("Forget password ?")])], 1) : _vm._e()])])
},staticRenderFns: []}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-2468153e", module.exports)
  }
}

/***/ }),
/* 71 */
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    class: ("anakeenLoading--logo loader-container " + _vm.color + "-logo")
  }, [_c('div', {
    style: ({
      width: _vm.width,
      height: _vm.height
    })
  }, [_c('div', {
    staticClass: "text-logo"
  }, [_c('svg', {
    style: ({
      width: _vm.width,
      height: _vm.height
    }),
    attrs: {
      "xmlns:xlink": "http://www.w3.org/1999/xlink",
      "version": "1.1",
      "id": "poweredby",
      "xmlns": "http://www.w3.org/2000/svg",
      "x": "0px",
      "y": "0px",
      "viewBox": _vm.viewBox,
      "xml:space": "preserve"
    }
  }, [_c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M115.5,34.1H99.7v18.5h-2.6V2.2h18.4c5.2,0,9,1.3,11.3,3.8c2.4,2.5,3.6,6.4,3.6,11.7\n                        C130.4,28.6,125.4,34.1,115.5,34.1z M99.7,31.6h15.8c8.1,0,12.2-4.7,12.2-14c0-4.4-1-7.7-2.9-9.8c-1.9-2.1-5-3.2-9.3-3.2H99.7V31.6z\n                        "
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M145.9,19.9c2.2-2.7,6-4,11.3-4s9.1,1.3,11.3,4c2.2,2.7,3.3,7.5,3.3,14.5c0,7-1,11.9-3,14.6\n                        c-2,2.7-5.9,4.1-11.7,4.1c-5.8,0-9.7-1.4-11.7-4.1c-2-2.7-3-7.6-3-14.6C142.5,27.5,143.6,22.6,145.9,19.9z M150.8,50.1\n                        c1.6,0.6,3.7,0.8,6.4,0.8c2.7,0,4.8-0.3,6.4-0.8c1.6-0.6,2.8-1.6,3.6-3.1c0.8-1.5,1.4-3.2,1.6-5.1c0.2-1.9,0.4-4.5,0.4-8\n                        c0-6-0.8-10.2-2.5-12.4c-1.7-2.2-4.8-3.3-9.4-3.3s-7.8,1.1-9.4,3.3c-1.7,2.2-2.5,6.3-2.5,12.4c0,3.4,0.1,6.1,0.4,8\n                        c0.2,1.9,0.8,3.6,1.6,5.1C148,48.5,149.2,49.5,150.8,50.1z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M183.4,16.6h2.7l9.1,33.7h0.8l10.7-33h2.7l10.7,33h0.8l9.1-33.7h2.7l-9.7,36h-4.9l-10-32.3l-10,32.3h-4.9\n                        L183.4,16.6z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M268.8,50.4l2.2-0.1l0.1,2.3c-5.4,0.4-9.8,0.6-13.3,0.6c-5.1,0-8.6-1.6-10.6-4.6c-2-3-3-7.7-3-14\n                        c0-12.4,4.8-18.6,14.5-18.6c4.6,0,8,1.4,10.3,4.1c2.3,2.7,3.4,7.1,3.4,13.2v2.4h-25.6c0,5.2,0.8,9,2.4,11.6c1.6,2.5,4.3,3.8,8.1,3.8\n                        C261.2,50.9,265,50.7,268.8,50.4z M246.9,33.3h22.9c0-5.4-0.9-9.3-2.6-11.6c-1.8-2.3-4.5-3.5-8.4-3.5\n                        C250.8,18.2,246.9,23.2,246.9,33.3z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M287.3,52.6v-36h2.5v5.6c1.4-1.2,3.6-2.4,6.4-3.7c2.9-1.3,5.5-2.2,7.8-2.6v2.6c-2.1,0.4-4.4,1.2-6.7,2.2\n                        c-2.4,1-4.2,1.9-5.5,2.7l-2,1.2v28H287.3z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M338.1,50.4l2.2-0.1l0.1,2.3c-5.4,0.4-9.8,0.6-13.3,0.6c-5.1,0-8.6-1.6-10.6-4.6c-2-3-3-7.7-3-14\n                        c0-12.4,4.8-18.6,14.5-18.6c4.6,0,8,1.4,10.3,4.1c2.3,2.7,3.4,7.1,3.4,13.2v2.4h-25.6c0,5.2,0.8,9,2.4,11.6c1.6,2.5,4.3,3.8,8.1,3.8\n                        C330.6,50.9,334.3,50.7,338.1,50.4z M316.2,33.3h22.9c0-5.4-0.9-9.3-2.6-11.6c-1.8-2.3-4.5-3.5-8.4-3.5\n                        C320.2,18.2,316.2,23.2,316.2,33.3z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M382.3,0v52.6h-2.5v-3c-1.4,0.9-3.3,1.8-5.8,2.5c-2.5,0.8-4.5,1.2-6.1,1.2c-1.6,0-2.8-0.1-3.6-0.2\n                        c-0.8-0.1-1.9-0.5-3.1-1.2s-2.3-1.6-3.1-2.8c-0.8-1.2-1.5-3-2.1-5.4c-0.6-2.4-0.9-5.3-0.9-8.6c0-6.5,1.1-11.4,3.3-14.5\n                        c2.2-3.1,6.2-4.7,11.8-4.7c2.7,0,5.9,0.3,9.5,0.9V0H382.3z M364,50.4c0.9,0.3,2.2,0.5,3.7,0.5s3.3-0.3,5.4-0.9\n                        c2.1-0.6,3.7-1.2,4.9-1.9l1.7-0.9V19.1c-3.7-0.6-6.9-0.9-9.5-0.9c-4.8,0-8.1,1.4-9.9,4.1c-1.8,2.7-2.6,7-2.6,12.8\n                        c0,6.9,1.1,11.4,3.3,13.4C362.1,49.5,363.1,50.1,364,50.4z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M422.8,18.8c4-1.9,8.2-2.9,12.7-2.9s7.6,1.4,9.3,4.2c1.7,2.8,2.6,7.6,2.6,14.3S446.3,46,444,48.9\n                        c-2.3,2.9-6.6,4.3-13,4.3c-3.4,0-6.4-0.2-9.1-0.5l-1.6-0.1V0h2.5V18.8z M422.8,50.5c3.7,0.3,6.8,0.4,9.1,0.4c2.3,0,4.4-0.3,6.3-0.9\n                        c1.8-0.6,3.3-1.7,4.2-3.2c1-1.5,1.6-3.3,1.9-5.1c0.3-1.9,0.5-4.4,0.5-7.6c0-5.7-0.6-9.7-1.9-12.1c-1.3-2.4-3.8-3.6-7.6-3.6\n                        c-1.9,0-3.9,0.2-6,0.7c-2.1,0.5-3.6,1-4.8,1.4l-1.7,0.7V50.5z"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "st0",
    attrs: {
      "d": "M458.6,16.6h2.7l10.6,33.7h3l10.7-33.7h2.7l-16.5,52.6H469l5.2-16.6h-4.4L458.6,16.6z"
    }
  })])]), _vm._v(" "), _c('div', {
    staticClass: "main-logo"
  }, [_c('svg', {
    staticClass: "bounce",
    style: ({
      width: _vm.width,
      height: _vm.height
    }),
    attrs: {
      "xmlns:xlink": "http://www.w3.org/1999/xlink",
      "version": "1.1",
      "id": "logo",
      "xmlns": "http://www.w3.org/2000/svg",
      "x": "0px",
      "y": "0px",
      "viewBox": _vm.viewBox,
      "xml:space": "preserve",
      "preserveAspectRatio": "xMidYMin"
    }
  }, [_c('path', {
    staticClass: "main-letter",
    attrs: {
      "id": "A1",
      "d": "M42.1,43.6c17.8,0,28.4,13.7,28.5,30.5c0.1,17-9.6,32-28.1,32c-18,0-27.7-15.6-27.7-32.1\n                        C14.7,58.1,25.4,43.6,42.1,43.6 M40.5,30.3C15.1,30.3-0.1,50.9,0,75c0.1,23.3,15.8,44.3,40.6,44.3c11.9,0,21.7-4.6,29-14H70l0,11.6\n                        h14.4l-0.3-84.3H69.7l0.1,12h-0.4C62.4,35.7,52,30.3,40.5,30.3"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "letter",
    attrs: {
      "id": "N1",
      "d": "M135.1,30.3c-10.4,0-18.1,4.5-24,12.6h-0.4l0-10.2H96.3l0.3,84.4H111l-0.1-40\n                        c-0.1-15.1,1.1-33.4,20.9-33.4c16.7,0,17.8,12.2,17.9,25.8l0.2,47.6h14.4L164,66.9C163.9,47.2,158.5,30.3,135.1,30.3"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "letter",
    attrs: {
      "id": "A2",
      "d": "M215.4,43.6c17.8,0,28.4,13.7,28.5,30.5c0.1,17-9.6,32-28.1,32c-18,0-27.7-15.6-27.8-32.1\n                        C188,58.1,198.7,43.6,215.4,43.6 M213.8,30.3c-25.3,0-40.5,20.7-40.4,44.7c0.1,23.3,15.8,44.3,40.5,44.3c11.9,0,21.7-4.6,29.1-14\n                        h0.4l0,11.6h14.4l-0.3-84.3h-14.4l0,12h-0.4C235.7,35.7,225.3,30.3,213.8,30.3"
    }
  }), _vm._v(" "), _c('polygon', {
    staticClass: "letter",
    attrs: {
      "id": "K",
      "points": "336.8,32.6 317.6,32.6 287,63.9 286.8,0 272.4,0 272.8,117 287.2,117 287.1,82.3 290.8,78.6\n                        324.9,117 343.9,117 300.8,68.5 "
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "letter",
    attrs: {
      "id": "E1",
      "d": "M376.9,43.6c12.6,0,22.9,9.9,24.9,22.1h-50.5C353.1,53.4,364.5,43.6,376.9,43.6 M376.7,30.3\n                        c-26.4,0-40.9,20.7-40.8,45.6c0.1,24.2,16.3,43.4,41.4,43.4c17.2,0,30.9-8.6,38.9-23.7l-12.2-7c-5.5,10-13.4,17.4-25.6,17.4\n                        c-16.3,0-27.5-12.6-27.7-28.2h66.3C418.3,52.7,403.8,30.3,376.7,30.3"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "letter",
    attrs: {
      "id": "E2",
      "d": "M462.9,43.6c12.6,0,22.9,9.9,24.9,22.1h-50.5C439.1,53.4,450.5,43.6,462.9,43.6 M462.7,30.3\n                        c-26.4,0-40.9,20.7-40.8,45.6c0.1,24.2,16.3,43.4,41.4,43.4c17.2,0,30.9-8.6,38.9-23.7l-12.2-7c-5.5,10-13.4,17.4-25.6,17.4\n                        c-16.3,0-27.5-12.6-27.7-28.2h66.3C504.3,52.7,489.8,30.3,462.7,30.3"
    }
  }), _vm._v(" "), _c('path', {
    staticClass: "letter",
    attrs: {
      "id": "N2",
      "d": "M550.6,30.3c-10.4,0-18.1,4.5-24,12.6h-0.4l0-10.2h-14.4l0.3,84.4h14.4l-0.1-40\n                        c-0.1-15.1,1.1-33.4,20.9-33.4c16.7,0,17.8,12.2,17.9,25.8l0.2,47.6h14.4l-0.2-50.1C579.4,47.2,573.9,30.3,550.6,30.3"
    }
  })])])])])
},staticRenderFns: []}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-3b62e416", module.exports)
  }
}

/***/ }),
/* 72 */
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    ref: "tabsWrapper",
    staticClass: "documentsList__documentsTabs__wrapper"
  }, [_c('div', {
    staticClass: "documentsList__documentsTabs__tabs__buttons"
  }, [_c('div', {
    ref: "slotContent",
    staticClass: "documentsList__documentsTabs__tabs__custom__slot"
  }, [_vm._t("left-content")], 2), _vm._v(" "), _c('div', {
    ref: "tabsPaginator",
    staticClass: "documentsList__documentsTabs__tabs__paginator"
  }, [_c('input', {
    ref: "tabsList",
    staticClass: "documentsList__documentsTabs__tabs__paginator__button documentsList__documentsTabs__tabs__paginator__dropdown__button"
  })])]), _vm._v(" "), (_vm.emptyState) ? _c('div', {
    staticClass: "documentsList__documentsTabs__empty"
  }, [_c('img', {
    staticClass: "documentsList__documentsTabs__empty__img",
    attrs: {
      "src": _vm.emptyImg
    }
  })]) : _vm._e(), _vm._v(" "), _c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (!_vm.emptyState),
      expression: "!emptyState"
    }],
    staticClass: "documentsList__documentsTabs__tabs__wrapper"
  }, [_c('div', {
    ref: "tabstrip",
    staticClass: "documentsList__documentsTabs__tabs"
  })])])
},staticRenderFns: []}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-49db6276", module.exports)
  }
}

/***/ }),
/* 73 */
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('iframe', {
    ref: "iDocument",
    staticClass: "apDocumentWrapper",
    staticStyle: {
      "visibility": "hidden"
    },
    attrs: {
      "src": "api/v1/documents/0.html"
    }
  })
},staticRenderFns: []}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-56650ed6", module.exports)
  }
}

/***/ }),
/* 74 */
/***/ (function(module, exports, __webpack_require__) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    ref: "authentPassword",
    staticClass: "authent-password"
  }, [_c('label', {
    staticClass: "label",
    attrs: {
      "for": _vm.pwdId
    }
  }, [_vm._v(_vm._s(_vm.label))]), _vm._v(" "), _c('span', {
    staticClass: "input-group-btn"
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.value),
      expression: "value"
    }],
    ref: "authentPassword",
    staticClass: "authent-pwd form-control",
    attrs: {
      "id": _vm.pwdId,
      "type": "password",
      "placeholder": _vm.placeholder,
      "required": "",
      "autocapitalize": "off",
      "autocorrect": "off"
    },
    domProps: {
      "value": (_vm.value)
    },
    on: {
      "input": [function($event) {
        if ($event.target.composing) { return; }
        _vm.value = $event.target.value
      }, _vm.changePassword]
    }
  }), _vm._v(" "), _c('button', {
    ref: "authentReveal ",
    staticClass: "btn btn-reveal btn-secondary",
    attrs: {
      "type": "button"
    }
  }, [_c('i', {
    staticClass: "fa fa-eye"
  })])])])
},staticRenderFns: []}
module.exports.render._withStripped = true
if (false) {
  module.hot.accept()
  if (module.hot.data) {
     require("vue-hot-reload-api").rerender("data-v-74934f0e", module.exports)
  }
}

/***/ }),
/* 75 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(53);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("7861f104", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-143db676\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./documentList.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-143db676\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./documentList.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 76 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(54);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("06e0af8d", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-2468153e\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./Authent.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-2468153e\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./Authent.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 77 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(55);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("773c8f5b", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-3b62e416\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=1!./AnakeenLoading.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-3b62e416\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=1!./AnakeenLoading.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 78 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(56);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("d1740536", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-3b62e416\",\"scoped\":true,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AnakeenLoading.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-3b62e416\",\"scoped\":true,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AnakeenLoading.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 79 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(57);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("59539a47", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-49db6276\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./documentTabs.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-49db6276\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./documentTabs.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 80 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(58);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("65ec8fc8", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-49db6276\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=1!./documentTabs.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-49db6276\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=1!./documentTabs.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 81 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(59);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("b807e446", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-56650ed6\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=1!./Document.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-56650ed6\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=1!./Document.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 82 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(60);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("a3c62dd4", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-74934f0e\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AuthentPassword.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-74934f0e\",\"scoped\":false,\"hasInlineConfig\":false}!../../../../../node_modules/sass-loader/lib/loader.js!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./AuthentPassword.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 83 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(61);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(2)("c4a2778a", content, false);
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../../node_modules/css-loader/index.js?sourceMap!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-56650ed6\",\"scoped\":true,\"hasInlineConfig\":false}!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./Document.vue", function() {
     var newContent = require("!!../../../../../node_modules/css-loader/index.js?sourceMap!../../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"id\":\"data-v-56650ed6\",\"scoped\":true,\"hasInlineConfig\":false}!../../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./Document.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ })
/******/ ]);
//# sourceMappingURL=ank-components.js.map