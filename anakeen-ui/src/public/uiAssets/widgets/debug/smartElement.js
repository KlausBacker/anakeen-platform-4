(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("jQuery"));
	else if(typeof define === 'function' && define.amd)
		define(["jQuery"], factory);
	else {
		var a = typeof exports === 'object' ? factory(require("jQuery")) : factory(root["jQuery"]);
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(this, function(__WEBPACK_EXTERNAL_MODULE_external__jQuery___) {
return /******/ (function(modules) { // webpackBootstrap
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
/******/ 	__webpack_require__.p = "uiAssets/widgets/debug/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/Apps/DOCUMENT/IHM/smartElement.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/underscore/underscore.js":
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;//     Underscore.js 1.8.3
//     http://underscorejs.org
//     (c) 2009-2015 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.

(function() {

  // Baseline setup
  // --------------

  // Establish the root object, `window` in the browser, or `exports` on the server.
  var root = this;

  // Save the previous value of the `_` variable.
  var previousUnderscore = root._;

  // Save bytes in the minified (but not gzipped) version:
  var ArrayProto = Array.prototype, ObjProto = Object.prototype, FuncProto = Function.prototype;

  // Create quick reference variables for speed access to core prototypes.
  var
    push             = ArrayProto.push,
    slice            = ArrayProto.slice,
    toString         = ObjProto.toString,
    hasOwnProperty   = ObjProto.hasOwnProperty;

  // All **ECMAScript 5** native function implementations that we hope to use
  // are declared here.
  var
    nativeIsArray      = Array.isArray,
    nativeKeys         = Object.keys,
    nativeBind         = FuncProto.bind,
    nativeCreate       = Object.create;

  // Naked function reference for surrogate-prototype-swapping.
  var Ctor = function(){};

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };

  // Export the Underscore object for **Node.js**, with
  // backwards-compatibility for the old `require()` API. If we're in
  // the browser, add `_` as a global object.
  if (true) {
    if (typeof module !== 'undefined' && module.exports) {
      exports = module.exports = _;
    }
    exports._ = _;
  } else {
    root._ = _;
  }

  // Current version.
  _.VERSION = '1.8.3';

  // Internal function that returns an efficient (for current engines) version
  // of the passed-in callback, to be repeatedly applied in other Underscore
  // functions.
  var optimizeCb = function(func, context, argCount) {
    if (context === void 0) return func;
    switch (argCount == null ? 3 : argCount) {
      case 1: return function(value) {
        return func.call(context, value);
      };
      case 2: return function(value, other) {
        return func.call(context, value, other);
      };
      case 3: return function(value, index, collection) {
        return func.call(context, value, index, collection);
      };
      case 4: return function(accumulator, value, index, collection) {
        return func.call(context, accumulator, value, index, collection);
      };
    }
    return function() {
      return func.apply(context, arguments);
    };
  };

  // A mostly-internal function to generate callbacks that can be applied
  // to each element in a collection, returning the desired result — either
  // identity, an arbitrary callback, a property matcher, or a property accessor.
  var cb = function(value, context, argCount) {
    if (value == null) return _.identity;
    if (_.isFunction(value)) return optimizeCb(value, context, argCount);
    if (_.isObject(value)) return _.matcher(value);
    return _.property(value);
  };
  _.iteratee = function(value, context) {
    return cb(value, context, Infinity);
  };

  // An internal function for creating assigner functions.
  var createAssigner = function(keysFunc, undefinedOnly) {
    return function(obj) {
      var length = arguments.length;
      if (length < 2 || obj == null) return obj;
      for (var index = 1; index < length; index++) {
        var source = arguments[index],
            keys = keysFunc(source),
            l = keys.length;
        for (var i = 0; i < l; i++) {
          var key = keys[i];
          if (!undefinedOnly || obj[key] === void 0) obj[key] = source[key];
        }
      }
      return obj;
    };
  };

  // An internal function for creating a new object that inherits from another.
  var baseCreate = function(prototype) {
    if (!_.isObject(prototype)) return {};
    if (nativeCreate) return nativeCreate(prototype);
    Ctor.prototype = prototype;
    var result = new Ctor;
    Ctor.prototype = null;
    return result;
  };

  var property = function(key) {
    return function(obj) {
      return obj == null ? void 0 : obj[key];
    };
  };

  // Helper for collection methods to determine whether a collection
  // should be iterated as an array or as an object
  // Related: http://people.mozilla.org/~jorendorff/es6-draft.html#sec-tolength
  // Avoids a very nasty iOS 8 JIT bug on ARM-64. #2094
  var MAX_ARRAY_INDEX = Math.pow(2, 53) - 1;
  var getLength = property('length');
  var isArrayLike = function(collection) {
    var length = getLength(collection);
    return typeof length == 'number' && length >= 0 && length <= MAX_ARRAY_INDEX;
  };

  // Collection Functions
  // --------------------

  // The cornerstone, an `each` implementation, aka `forEach`.
  // Handles raw objects in addition to array-likes. Treats all
  // sparse array-likes as if they were dense.
  _.each = _.forEach = function(obj, iteratee, context) {
    iteratee = optimizeCb(iteratee, context);
    var i, length;
    if (isArrayLike(obj)) {
      for (i = 0, length = obj.length; i < length; i++) {
        iteratee(obj[i], i, obj);
      }
    } else {
      var keys = _.keys(obj);
      for (i = 0, length = keys.length; i < length; i++) {
        iteratee(obj[keys[i]], keys[i], obj);
      }
    }
    return obj;
  };

  // Return the results of applying the iteratee to each element.
  _.map = _.collect = function(obj, iteratee, context) {
    iteratee = cb(iteratee, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length,
        results = Array(length);
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      results[index] = iteratee(obj[currentKey], currentKey, obj);
    }
    return results;
  };

  // Create a reducing function iterating left or right.
  function createReduce(dir) {
    // Optimized iterator function as using arguments.length
    // in the main function will deoptimize the, see #1991.
    function iterator(obj, iteratee, memo, keys, index, length) {
      for (; index >= 0 && index < length; index += dir) {
        var currentKey = keys ? keys[index] : index;
        memo = iteratee(memo, obj[currentKey], currentKey, obj);
      }
      return memo;
    }

    return function(obj, iteratee, memo, context) {
      iteratee = optimizeCb(iteratee, context, 4);
      var keys = !isArrayLike(obj) && _.keys(obj),
          length = (keys || obj).length,
          index = dir > 0 ? 0 : length - 1;
      // Determine the initial value if none is provided.
      if (arguments.length < 3) {
        memo = obj[keys ? keys[index] : index];
        index += dir;
      }
      return iterator(obj, iteratee, memo, keys, index, length);
    };
  }

  // **Reduce** builds up a single result from a list of values, aka `inject`,
  // or `foldl`.
  _.reduce = _.foldl = _.inject = createReduce(1);

  // The right-associative version of reduce, also known as `foldr`.
  _.reduceRight = _.foldr = createReduce(-1);

  // Return the first value which passes a truth test. Aliased as `detect`.
  _.find = _.detect = function(obj, predicate, context) {
    var key;
    if (isArrayLike(obj)) {
      key = _.findIndex(obj, predicate, context);
    } else {
      key = _.findKey(obj, predicate, context);
    }
    if (key !== void 0 && key !== -1) return obj[key];
  };

  // Return all the elements that pass a truth test.
  // Aliased as `select`.
  _.filter = _.select = function(obj, predicate, context) {
    var results = [];
    predicate = cb(predicate, context);
    _.each(obj, function(value, index, list) {
      if (predicate(value, index, list)) results.push(value);
    });
    return results;
  };

  // Return all the elements for which a truth test fails.
  _.reject = function(obj, predicate, context) {
    return _.filter(obj, _.negate(cb(predicate)), context);
  };

  // Determine whether all of the elements match a truth test.
  // Aliased as `all`.
  _.every = _.all = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length;
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      if (!predicate(obj[currentKey], currentKey, obj)) return false;
    }
    return true;
  };

  // Determine if at least one element in the object matches a truth test.
  // Aliased as `any`.
  _.some = _.any = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = !isArrayLike(obj) && _.keys(obj),
        length = (keys || obj).length;
    for (var index = 0; index < length; index++) {
      var currentKey = keys ? keys[index] : index;
      if (predicate(obj[currentKey], currentKey, obj)) return true;
    }
    return false;
  };

  // Determine if the array or object contains a given item (using `===`).
  // Aliased as `includes` and `include`.
  _.contains = _.includes = _.include = function(obj, item, fromIndex, guard) {
    if (!isArrayLike(obj)) obj = _.values(obj);
    if (typeof fromIndex != 'number' || guard) fromIndex = 0;
    return _.indexOf(obj, item, fromIndex) >= 0;
  };

  // Invoke a method (with arguments) on every item in a collection.
  _.invoke = function(obj, method) {
    var args = slice.call(arguments, 2);
    var isFunc = _.isFunction(method);
    return _.map(obj, function(value) {
      var func = isFunc ? method : value[method];
      return func == null ? func : func.apply(value, args);
    });
  };

  // Convenience version of a common use case of `map`: fetching a property.
  _.pluck = function(obj, key) {
    return _.map(obj, _.property(key));
  };

  // Convenience version of a common use case of `filter`: selecting only objects
  // containing specific `key:value` pairs.
  _.where = function(obj, attrs) {
    return _.filter(obj, _.matcher(attrs));
  };

  // Convenience version of a common use case of `find`: getting the first object
  // containing specific `key:value` pairs.
  _.findWhere = function(obj, attrs) {
    return _.find(obj, _.matcher(attrs));
  };

  // Return the maximum element (or element-based computation).
  _.max = function(obj, iteratee, context) {
    var result = -Infinity, lastComputed = -Infinity,
        value, computed;
    if (iteratee == null && obj != null) {
      obj = isArrayLike(obj) ? obj : _.values(obj);
      for (var i = 0, length = obj.length; i < length; i++) {
        value = obj[i];
        if (value > result) {
          result = value;
        }
      }
    } else {
      iteratee = cb(iteratee, context);
      _.each(obj, function(value, index, list) {
        computed = iteratee(value, index, list);
        if (computed > lastComputed || computed === -Infinity && result === -Infinity) {
          result = value;
          lastComputed = computed;
        }
      });
    }
    return result;
  };

  // Return the minimum element (or element-based computation).
  _.min = function(obj, iteratee, context) {
    var result = Infinity, lastComputed = Infinity,
        value, computed;
    if (iteratee == null && obj != null) {
      obj = isArrayLike(obj) ? obj : _.values(obj);
      for (var i = 0, length = obj.length; i < length; i++) {
        value = obj[i];
        if (value < result) {
          result = value;
        }
      }
    } else {
      iteratee = cb(iteratee, context);
      _.each(obj, function(value, index, list) {
        computed = iteratee(value, index, list);
        if (computed < lastComputed || computed === Infinity && result === Infinity) {
          result = value;
          lastComputed = computed;
        }
      });
    }
    return result;
  };

  // Shuffle a collection, using the modern version of the
  // [Fisher-Yates shuffle](http://en.wikipedia.org/wiki/Fisher–Yates_shuffle).
  _.shuffle = function(obj) {
    var set = isArrayLike(obj) ? obj : _.values(obj);
    var length = set.length;
    var shuffled = Array(length);
    for (var index = 0, rand; index < length; index++) {
      rand = _.random(0, index);
      if (rand !== index) shuffled[index] = shuffled[rand];
      shuffled[rand] = set[index];
    }
    return shuffled;
  };

  // Sample **n** random values from a collection.
  // If **n** is not specified, returns a single random element.
  // The internal `guard` argument allows it to work with `map`.
  _.sample = function(obj, n, guard) {
    if (n == null || guard) {
      if (!isArrayLike(obj)) obj = _.values(obj);
      return obj[_.random(obj.length - 1)];
    }
    return _.shuffle(obj).slice(0, Math.max(0, n));
  };

  // Sort the object's values by a criterion produced by an iteratee.
  _.sortBy = function(obj, iteratee, context) {
    iteratee = cb(iteratee, context);
    return _.pluck(_.map(obj, function(value, index, list) {
      return {
        value: value,
        index: index,
        criteria: iteratee(value, index, list)
      };
    }).sort(function(left, right) {
      var a = left.criteria;
      var b = right.criteria;
      if (a !== b) {
        if (a > b || a === void 0) return 1;
        if (a < b || b === void 0) return -1;
      }
      return left.index - right.index;
    }), 'value');
  };

  // An internal function used for aggregate "group by" operations.
  var group = function(behavior) {
    return function(obj, iteratee, context) {
      var result = {};
      iteratee = cb(iteratee, context);
      _.each(obj, function(value, index) {
        var key = iteratee(value, index, obj);
        behavior(result, value, key);
      });
      return result;
    };
  };

  // Groups the object's values by a criterion. Pass either a string attribute
  // to group by, or a function that returns the criterion.
  _.groupBy = group(function(result, value, key) {
    if (_.has(result, key)) result[key].push(value); else result[key] = [value];
  });

  // Indexes the object's values by a criterion, similar to `groupBy`, but for
  // when you know that your index values will be unique.
  _.indexBy = group(function(result, value, key) {
    result[key] = value;
  });

  // Counts instances of an object that group by a certain criterion. Pass
  // either a string attribute to count by, or a function that returns the
  // criterion.
  _.countBy = group(function(result, value, key) {
    if (_.has(result, key)) result[key]++; else result[key] = 1;
  });

  // Safely create a real, live array from anything iterable.
  _.toArray = function(obj) {
    if (!obj) return [];
    if (_.isArray(obj)) return slice.call(obj);
    if (isArrayLike(obj)) return _.map(obj, _.identity);
    return _.values(obj);
  };

  // Return the number of elements in an object.
  _.size = function(obj) {
    if (obj == null) return 0;
    return isArrayLike(obj) ? obj.length : _.keys(obj).length;
  };

  // Split a collection into two arrays: one whose elements all satisfy the given
  // predicate, and one whose elements all do not satisfy the predicate.
  _.partition = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var pass = [], fail = [];
    _.each(obj, function(value, key, obj) {
      (predicate(value, key, obj) ? pass : fail).push(value);
    });
    return [pass, fail];
  };

  // Array Functions
  // ---------------

  // Get the first element of an array. Passing **n** will return the first N
  // values in the array. Aliased as `head` and `take`. The **guard** check
  // allows it to work with `_.map`.
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null) return void 0;
    if (n == null || guard) return array[0];
    return _.initial(array, array.length - n);
  };

  // Returns everything but the last entry of the array. Especially useful on
  // the arguments object. Passing **n** will return all the values in
  // the array, excluding the last N.
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, Math.max(0, array.length - (n == null || guard ? 1 : n)));
  };

  // Get the last element of an array. Passing **n** will return the last N
  // values in the array.
  _.last = function(array, n, guard) {
    if (array == null) return void 0;
    if (n == null || guard) return array[array.length - 1];
    return _.rest(array, Math.max(0, array.length - n));
  };

  // Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
  // Especially useful on the arguments object. Passing an **n** will return
  // the rest N values in the array.
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, n == null || guard ? 1 : n);
  };

  // Trim out all falsy values from an array.
  _.compact = function(array) {
    return _.filter(array, _.identity);
  };

  // Internal implementation of a recursive `flatten` function.
  var flatten = function(input, shallow, strict, startIndex) {
    var output = [], idx = 0;
    for (var i = startIndex || 0, length = getLength(input); i < length; i++) {
      var value = input[i];
      if (isArrayLike(value) && (_.isArray(value) || _.isArguments(value))) {
        //flatten current level of array or arguments object
        if (!shallow) value = flatten(value, shallow, strict);
        var j = 0, len = value.length;
        output.length += len;
        while (j < len) {
          output[idx++] = value[j++];
        }
      } else if (!strict) {
        output[idx++] = value;
      }
    }
    return output;
  };

  // Flatten out an array, either recursively (by default), or just one level.
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, false);
  };

  // Return a version of the array that does not contain the specified value(s).
  _.without = function(array) {
    return _.difference(array, slice.call(arguments, 1));
  };

  // Produce a duplicate-free version of the array. If the array has already
  // been sorted, you have the option of using a faster algorithm.
  // Aliased as `unique`.
  _.uniq = _.unique = function(array, isSorted, iteratee, context) {
    if (!_.isBoolean(isSorted)) {
      context = iteratee;
      iteratee = isSorted;
      isSorted = false;
    }
    if (iteratee != null) iteratee = cb(iteratee, context);
    var result = [];
    var seen = [];
    for (var i = 0, length = getLength(array); i < length; i++) {
      var value = array[i],
          computed = iteratee ? iteratee(value, i, array) : value;
      if (isSorted) {
        if (!i || seen !== computed) result.push(value);
        seen = computed;
      } else if (iteratee) {
        if (!_.contains(seen, computed)) {
          seen.push(computed);
          result.push(value);
        }
      } else if (!_.contains(result, value)) {
        result.push(value);
      }
    }
    return result;
  };

  // Produce an array that contains the union: each distinct element from all of
  // the passed-in arrays.
  _.union = function() {
    return _.uniq(flatten(arguments, true, true));
  };

  // Produce an array that contains every item shared between all the
  // passed-in arrays.
  _.intersection = function(array) {
    var result = [];
    var argsLength = arguments.length;
    for (var i = 0, length = getLength(array); i < length; i++) {
      var item = array[i];
      if (_.contains(result, item)) continue;
      for (var j = 1; j < argsLength; j++) {
        if (!_.contains(arguments[j], item)) break;
      }
      if (j === argsLength) result.push(item);
    }
    return result;
  };

  // Take the difference between one array and a number of other arrays.
  // Only the elements present in just the first array will remain.
  _.difference = function(array) {
    var rest = flatten(arguments, true, true, 1);
    return _.filter(array, function(value){
      return !_.contains(rest, value);
    });
  };

  // Zip together multiple lists into a single array -- elements that share
  // an index go together.
  _.zip = function() {
    return _.unzip(arguments);
  };

  // Complement of _.zip. Unzip accepts an array of arrays and groups
  // each array's elements on shared indices
  _.unzip = function(array) {
    var length = array && _.max(array, getLength).length || 0;
    var result = Array(length);

    for (var index = 0; index < length; index++) {
      result[index] = _.pluck(array, index);
    }
    return result;
  };

  // Converts lists into objects. Pass either a single array of `[key, value]`
  // pairs, or two parallel arrays of the same length -- one of keys, and one of
  // the corresponding values.
  _.object = function(list, values) {
    var result = {};
    for (var i = 0, length = getLength(list); i < length; i++) {
      if (values) {
        result[list[i]] = values[i];
      } else {
        result[list[i][0]] = list[i][1];
      }
    }
    return result;
  };

  // Generator function to create the findIndex and findLastIndex functions
  function createPredicateIndexFinder(dir) {
    return function(array, predicate, context) {
      predicate = cb(predicate, context);
      var length = getLength(array);
      var index = dir > 0 ? 0 : length - 1;
      for (; index >= 0 && index < length; index += dir) {
        if (predicate(array[index], index, array)) return index;
      }
      return -1;
    };
  }

  // Returns the first index on an array-like that passes a predicate test
  _.findIndex = createPredicateIndexFinder(1);
  _.findLastIndex = createPredicateIndexFinder(-1);

  // Use a comparator function to figure out the smallest index at which
  // an object should be inserted so as to maintain order. Uses binary search.
  _.sortedIndex = function(array, obj, iteratee, context) {
    iteratee = cb(iteratee, context, 1);
    var value = iteratee(obj);
    var low = 0, high = getLength(array);
    while (low < high) {
      var mid = Math.floor((low + high) / 2);
      if (iteratee(array[mid]) < value) low = mid + 1; else high = mid;
    }
    return low;
  };

  // Generator function to create the indexOf and lastIndexOf functions
  function createIndexFinder(dir, predicateFind, sortedIndex) {
    return function(array, item, idx) {
      var i = 0, length = getLength(array);
      if (typeof idx == 'number') {
        if (dir > 0) {
            i = idx >= 0 ? idx : Math.max(idx + length, i);
        } else {
            length = idx >= 0 ? Math.min(idx + 1, length) : idx + length + 1;
        }
      } else if (sortedIndex && idx && length) {
        idx = sortedIndex(array, item);
        return array[idx] === item ? idx : -1;
      }
      if (item !== item) {
        idx = predicateFind(slice.call(array, i, length), _.isNaN);
        return idx >= 0 ? idx + i : -1;
      }
      for (idx = dir > 0 ? i : length - 1; idx >= 0 && idx < length; idx += dir) {
        if (array[idx] === item) return idx;
      }
      return -1;
    };
  }

  // Return the position of the first occurrence of an item in an array,
  // or -1 if the item is not included in the array.
  // If the array is large and already in sort order, pass `true`
  // for **isSorted** to use binary search.
  _.indexOf = createIndexFinder(1, _.findIndex, _.sortedIndex);
  _.lastIndexOf = createIndexFinder(-1, _.findLastIndex);

  // Generate an integer Array containing an arithmetic progression. A port of
  // the native Python `range()` function. See
  // [the Python documentation](http://docs.python.org/library/functions.html#range).
  _.range = function(start, stop, step) {
    if (stop == null) {
      stop = start || 0;
      start = 0;
    }
    step = step || 1;

    var length = Math.max(Math.ceil((stop - start) / step), 0);
    var range = Array(length);

    for (var idx = 0; idx < length; idx++, start += step) {
      range[idx] = start;
    }

    return range;
  };

  // Function (ahem) Functions
  // ------------------

  // Determines whether to execute a function as a constructor
  // or a normal function with the provided arguments
  var executeBound = function(sourceFunc, boundFunc, context, callingContext, args) {
    if (!(callingContext instanceof boundFunc)) return sourceFunc.apply(context, args);
    var self = baseCreate(sourceFunc.prototype);
    var result = sourceFunc.apply(self, args);
    if (_.isObject(result)) return result;
    return self;
  };

  // Create a function bound to a given object (assigning `this`, and arguments,
  // optionally). Delegates to **ECMAScript 5**'s native `Function.bind` if
  // available.
  _.bind = function(func, context) {
    if (nativeBind && func.bind === nativeBind) return nativeBind.apply(func, slice.call(arguments, 1));
    if (!_.isFunction(func)) throw new TypeError('Bind must be called on a function');
    var args = slice.call(arguments, 2);
    var bound = function() {
      return executeBound(func, bound, context, this, args.concat(slice.call(arguments)));
    };
    return bound;
  };

  // Partially apply a function by creating a version that has had some of its
  // arguments pre-filled, without changing its dynamic `this` context. _ acts
  // as a placeholder, allowing any combination of arguments to be pre-filled.
  _.partial = function(func) {
    var boundArgs = slice.call(arguments, 1);
    var bound = function() {
      var position = 0, length = boundArgs.length;
      var args = Array(length);
      for (var i = 0; i < length; i++) {
        args[i] = boundArgs[i] === _ ? arguments[position++] : boundArgs[i];
      }
      while (position < arguments.length) args.push(arguments[position++]);
      return executeBound(func, bound, this, this, args);
    };
    return bound;
  };

  // Bind a number of an object's methods to that object. Remaining arguments
  // are the method names to be bound. Useful for ensuring that all callbacks
  // defined on an object belong to it.
  _.bindAll = function(obj) {
    var i, length = arguments.length, key;
    if (length <= 1) throw new Error('bindAll must be passed function names');
    for (i = 1; i < length; i++) {
      key = arguments[i];
      obj[key] = _.bind(obj[key], obj);
    }
    return obj;
  };

  // Memoize an expensive function by storing its results.
  _.memoize = function(func, hasher) {
    var memoize = function(key) {
      var cache = memoize.cache;
      var address = '' + (hasher ? hasher.apply(this, arguments) : key);
      if (!_.has(cache, address)) cache[address] = func.apply(this, arguments);
      return cache[address];
    };
    memoize.cache = {};
    return memoize;
  };

  // Delays a function for the given number of milliseconds, and then calls
  // it with the arguments supplied.
  _.delay = function(func, wait) {
    var args = slice.call(arguments, 2);
    return setTimeout(function(){
      return func.apply(null, args);
    }, wait);
  };

  // Defers a function, scheduling it to run after the current call stack has
  // cleared.
  _.defer = _.partial(_.delay, _, 1);

  // Returns a function, that, when invoked, will only be triggered at most once
  // during a given window of time. Normally, the throttled function will run
  // as much as it can, without ever going more than once per `wait` duration;
  // but if you'd like to disable the execution on the leading edge, pass
  // `{leading: false}`. To disable execution on the trailing edge, ditto.
  _.throttle = function(func, wait, options) {
    var context, args, result;
    var timeout = null;
    var previous = 0;
    if (!options) options = {};
    var later = function() {
      previous = options.leading === false ? 0 : _.now();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    };
    return function() {
      var now = _.now();
      if (!previous && options.leading === false) previous = now;
      var remaining = wait - (now - previous);
      context = this;
      args = arguments;
      if (remaining <= 0 || remaining > wait) {
        if (timeout) {
          clearTimeout(timeout);
          timeout = null;
        }
        previous = now;
        result = func.apply(context, args);
        if (!timeout) context = args = null;
      } else if (!timeout && options.trailing !== false) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };
  };

  // Returns a function, that, as long as it continues to be invoked, will not
  // be triggered. The function will be called after it stops being called for
  // N milliseconds. If `immediate` is passed, trigger the function on the
  // leading edge, instead of the trailing.
  _.debounce = function(func, wait, immediate) {
    var timeout, args, context, timestamp, result;

    var later = function() {
      var last = _.now() - timestamp;

      if (last < wait && last >= 0) {
        timeout = setTimeout(later, wait - last);
      } else {
        timeout = null;
        if (!immediate) {
          result = func.apply(context, args);
          if (!timeout) context = args = null;
        }
      }
    };

    return function() {
      context = this;
      args = arguments;
      timestamp = _.now();
      var callNow = immediate && !timeout;
      if (!timeout) timeout = setTimeout(later, wait);
      if (callNow) {
        result = func.apply(context, args);
        context = args = null;
      }

      return result;
    };
  };

  // Returns the first function passed as an argument to the second,
  // allowing you to adjust arguments, run code before and after, and
  // conditionally execute the original function.
  _.wrap = function(func, wrapper) {
    return _.partial(wrapper, func);
  };

  // Returns a negated version of the passed-in predicate.
  _.negate = function(predicate) {
    return function() {
      return !predicate.apply(this, arguments);
    };
  };

  // Returns a function that is the composition of a list of functions, each
  // consuming the return value of the function that follows.
  _.compose = function() {
    var args = arguments;
    var start = args.length - 1;
    return function() {
      var i = start;
      var result = args[start].apply(this, arguments);
      while (i--) result = args[i].call(this, result);
      return result;
    };
  };

  // Returns a function that will only be executed on and after the Nth call.
  _.after = function(times, func) {
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };

  // Returns a function that will only be executed up to (but not including) the Nth call.
  _.before = function(times, func) {
    var memo;
    return function() {
      if (--times > 0) {
        memo = func.apply(this, arguments);
      }
      if (times <= 1) func = null;
      return memo;
    };
  };

  // Returns a function that will be executed at most one time, no matter how
  // often you call it. Useful for lazy initialization.
  _.once = _.partial(_.before, 2);

  // Object Functions
  // ----------------

  // Keys in IE < 9 that won't be iterated by `for key in ...` and thus missed.
  var hasEnumBug = !{toString: null}.propertyIsEnumerable('toString');
  var nonEnumerableProps = ['valueOf', 'isPrototypeOf', 'toString',
                      'propertyIsEnumerable', 'hasOwnProperty', 'toLocaleString'];

  function collectNonEnumProps(obj, keys) {
    var nonEnumIdx = nonEnumerableProps.length;
    var constructor = obj.constructor;
    var proto = (_.isFunction(constructor) && constructor.prototype) || ObjProto;

    // Constructor is a special case.
    var prop = 'constructor';
    if (_.has(obj, prop) && !_.contains(keys, prop)) keys.push(prop);

    while (nonEnumIdx--) {
      prop = nonEnumerableProps[nonEnumIdx];
      if (prop in obj && obj[prop] !== proto[prop] && !_.contains(keys, prop)) {
        keys.push(prop);
      }
    }
  }

  // Retrieve the names of an object's own properties.
  // Delegates to **ECMAScript 5**'s native `Object.keys`
  _.keys = function(obj) {
    if (!_.isObject(obj)) return [];
    if (nativeKeys) return nativeKeys(obj);
    var keys = [];
    for (var key in obj) if (_.has(obj, key)) keys.push(key);
    // Ahem, IE < 9.
    if (hasEnumBug) collectNonEnumProps(obj, keys);
    return keys;
  };

  // Retrieve all the property names of an object.
  _.allKeys = function(obj) {
    if (!_.isObject(obj)) return [];
    var keys = [];
    for (var key in obj) keys.push(key);
    // Ahem, IE < 9.
    if (hasEnumBug) collectNonEnumProps(obj, keys);
    return keys;
  };

  // Retrieve the values of an object's properties.
  _.values = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var values = Array(length);
    for (var i = 0; i < length; i++) {
      values[i] = obj[keys[i]];
    }
    return values;
  };

  // Returns the results of applying the iteratee to each element of the object
  // In contrast to _.map it returns an object
  _.mapObject = function(obj, iteratee, context) {
    iteratee = cb(iteratee, context);
    var keys =  _.keys(obj),
          length = keys.length,
          results = {},
          currentKey;
      for (var index = 0; index < length; index++) {
        currentKey = keys[index];
        results[currentKey] = iteratee(obj[currentKey], currentKey, obj);
      }
      return results;
  };

  // Convert an object into a list of `[key, value]` pairs.
  _.pairs = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var pairs = Array(length);
    for (var i = 0; i < length; i++) {
      pairs[i] = [keys[i], obj[keys[i]]];
    }
    return pairs;
  };

  // Invert the keys and values of an object. The values must be serializable.
  _.invert = function(obj) {
    var result = {};
    var keys = _.keys(obj);
    for (var i = 0, length = keys.length; i < length; i++) {
      result[obj[keys[i]]] = keys[i];
    }
    return result;
  };

  // Return a sorted list of the function names available on the object.
  // Aliased as `methods`
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };

  // Extend a given object with all the properties in passed-in object(s).
  _.extend = createAssigner(_.allKeys);

  // Assigns a given object with all the own properties in the passed-in object(s)
  // (https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/Object/assign)
  _.extendOwn = _.assign = createAssigner(_.keys);

  // Returns the first key on an object that passes a predicate test
  _.findKey = function(obj, predicate, context) {
    predicate = cb(predicate, context);
    var keys = _.keys(obj), key;
    for (var i = 0, length = keys.length; i < length; i++) {
      key = keys[i];
      if (predicate(obj[key], key, obj)) return key;
    }
  };

  // Return a copy of the object only containing the whitelisted properties.
  _.pick = function(object, oiteratee, context) {
    var result = {}, obj = object, iteratee, keys;
    if (obj == null) return result;
    if (_.isFunction(oiteratee)) {
      keys = _.allKeys(obj);
      iteratee = optimizeCb(oiteratee, context);
    } else {
      keys = flatten(arguments, false, false, 1);
      iteratee = function(value, key, obj) { return key in obj; };
      obj = Object(obj);
    }
    for (var i = 0, length = keys.length; i < length; i++) {
      var key = keys[i];
      var value = obj[key];
      if (iteratee(value, key, obj)) result[key] = value;
    }
    return result;
  };

   // Return a copy of the object without the blacklisted properties.
  _.omit = function(obj, iteratee, context) {
    if (_.isFunction(iteratee)) {
      iteratee = _.negate(iteratee);
    } else {
      var keys = _.map(flatten(arguments, false, false, 1), String);
      iteratee = function(value, key) {
        return !_.contains(keys, key);
      };
    }
    return _.pick(obj, iteratee, context);
  };

  // Fill in a given object with default properties.
  _.defaults = createAssigner(_.allKeys, true);

  // Creates an object that inherits from the given prototype object.
  // If additional properties are provided then they will be added to the
  // created object.
  _.create = function(prototype, props) {
    var result = baseCreate(prototype);
    if (props) _.extendOwn(result, props);
    return result;
  };

  // Create a (shallow-cloned) duplicate of an object.
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };

  // Invokes interceptor with the obj, and then returns obj.
  // The primary purpose of this method is to "tap into" a method chain, in
  // order to perform operations on intermediate results within the chain.
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };

  // Returns whether an object has a given set of `key:value` pairs.
  _.isMatch = function(object, attrs) {
    var keys = _.keys(attrs), length = keys.length;
    if (object == null) return !length;
    var obj = Object(object);
    for (var i = 0; i < length; i++) {
      var key = keys[i];
      if (attrs[key] !== obj[key] || !(key in obj)) return false;
    }
    return true;
  };


  // Internal recursive comparison function for `isEqual`.
  var eq = function(a, b, aStack, bStack) {
    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the [Harmony `egal` proposal](http://wiki.ecmascript.org/doku.php?id=harmony:egal).
    if (a === b) return a !== 0 || 1 / a === 1 / b;
    // A strict comparison is necessary because `null == undefined`.
    if (a == null || b == null) return a === b;
    // Unwrap any wrapped objects.
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className !== toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, regular expressions, dates, and booleans are compared by value.
      case '[object RegExp]':
      // RegExps are coerced to strings for comparison (Note: '' + /a/i === '/a/i')
      case '[object String]':
        // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
        // equivalent to `new String("5")`.
        return '' + a === '' + b;
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive.
        // Object(NaN) is equivalent to NaN
        if (+a !== +a) return +b !== +b;
        // An `egal` comparison is performed for other numeric values.
        return +a === 0 ? 1 / +a === 1 / b : +a === +b;
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values. Dates are compared by their
        // millisecond representations. Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a === +b;
    }

    var areArrays = className === '[object Array]';
    if (!areArrays) {
      if (typeof a != 'object' || typeof b != 'object') return false;

      // Objects with different constructors are not equivalent, but `Object`s or `Array`s
      // from different frames are.
      var aCtor = a.constructor, bCtor = b.constructor;
      if (aCtor !== bCtor && !(_.isFunction(aCtor) && aCtor instanceof aCtor &&
                               _.isFunction(bCtor) && bCtor instanceof bCtor)
                          && ('constructor' in a && 'constructor' in b)) {
        return false;
      }
    }
    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.

    // Initializing stack of traversed objects.
    // It's done here since we only need them for objects and arrays comparison.
    aStack = aStack || [];
    bStack = bStack || [];
    var length = aStack.length;
    while (length--) {
      // Linear search. Performance is inversely proportional to the number of
      // unique nested structures.
      if (aStack[length] === a) return bStack[length] === b;
    }

    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);

    // Recursively compare objects and arrays.
    if (areArrays) {
      // Compare array lengths to determine if a deep comparison is necessary.
      length = a.length;
      if (length !== b.length) return false;
      // Deep compare the contents, ignoring non-numeric properties.
      while (length--) {
        if (!eq(a[length], b[length], aStack, bStack)) return false;
      }
    } else {
      // Deep compare objects.
      var keys = _.keys(a), key;
      length = keys.length;
      // Ensure that both objects contain the same number of properties before comparing deep equality.
      if (_.keys(b).length !== length) return false;
      while (length--) {
        // Deep compare each member
        key = keys[length];
        if (!(_.has(b, key) && eq(a[key], b[key], aStack, bStack))) return false;
      }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();
    return true;
  };

  // Perform a deep comparison to check if two objects are equal.
  _.isEqual = function(a, b) {
    return eq(a, b);
  };

  // Is a given array, string, or object empty?
  // An "empty" object has no enumerable own-properties.
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (isArrayLike(obj) && (_.isArray(obj) || _.isString(obj) || _.isArguments(obj))) return obj.length === 0;
    return _.keys(obj).length === 0;
  };

  // Is a given value a DOM element?
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };

  // Is a given value an array?
  // Delegates to ECMA5's native Array.isArray
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) === '[object Array]';
  };

  // Is a given variable an object?
  _.isObject = function(obj) {
    var type = typeof obj;
    return type === 'function' || type === 'object' && !!obj;
  };

  // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp, isError.
  _.each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp', 'Error'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) === '[object ' + name + ']';
    };
  });

  // Define a fallback version of the method in browsers (ahem, IE < 9), where
  // there isn't any inspectable "Arguments" type.
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return _.has(obj, 'callee');
    };
  }

  // Optimize `isFunction` if appropriate. Work around some typeof bugs in old v8,
  // IE 11 (#1621), and in Safari 8 (#1929).
  if (typeof /./ != 'function' && typeof Int8Array != 'object') {
    _.isFunction = function(obj) {
      return typeof obj == 'function' || false;
    };
  }

  // Is a given object a finite number?
  _.isFinite = function(obj) {
    return isFinite(obj) && !isNaN(parseFloat(obj));
  };

  // Is the given value `NaN`? (NaN is the only number which does not equal itself).
  _.isNaN = function(obj) {
    return _.isNumber(obj) && obj !== +obj;
  };

  // Is a given value a boolean?
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) === '[object Boolean]';
  };

  // Is a given value equal to null?
  _.isNull = function(obj) {
    return obj === null;
  };

  // Is a given variable undefined?
  _.isUndefined = function(obj) {
    return obj === void 0;
  };

  // Shortcut function for checking if an object has a given property directly
  // on itself (in other words, not on a prototype).
  _.has = function(obj, key) {
    return obj != null && hasOwnProperty.call(obj, key);
  };

  // Utility Functions
  // -----------------

  // Run Underscore.js in *noConflict* mode, returning the `_` variable to its
  // previous owner. Returns a reference to the Underscore object.
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };

  // Keep the identity function around for default iteratees.
  _.identity = function(value) {
    return value;
  };

  // Predicate-generating functions. Often useful outside of Underscore.
  _.constant = function(value) {
    return function() {
      return value;
    };
  };

  _.noop = function(){};

  _.property = property;

  // Generates a function for a given object that returns a given property.
  _.propertyOf = function(obj) {
    return obj == null ? function(){} : function(key) {
      return obj[key];
    };
  };

  // Returns a predicate for checking whether an object has a given set of
  // `key:value` pairs.
  _.matcher = _.matches = function(attrs) {
    attrs = _.extendOwn({}, attrs);
    return function(obj) {
      return _.isMatch(obj, attrs);
    };
  };

  // Run a function **n** times.
  _.times = function(n, iteratee, context) {
    var accum = Array(Math.max(0, n));
    iteratee = optimizeCb(iteratee, context, 1);
    for (var i = 0; i < n; i++) accum[i] = iteratee(i);
    return accum;
  };

  // Return a random integer between min and max (inclusive).
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + Math.floor(Math.random() * (max - min + 1));
  };

  // A (possibly faster) way to get the current timestamp as an integer.
  _.now = Date.now || function() {
    return new Date().getTime();
  };

   // List of HTML entities for escaping.
  var escapeMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
    '`': '&#x60;'
  };
  var unescapeMap = _.invert(escapeMap);

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  var createEscaper = function(map) {
    var escaper = function(match) {
      return map[match];
    };
    // Regexes for identifying a key that needs to be escaped
    var source = '(?:' + _.keys(map).join('|') + ')';
    var testRegexp = RegExp(source);
    var replaceRegexp = RegExp(source, 'g');
    return function(string) {
      string = string == null ? '' : '' + string;
      return testRegexp.test(string) ? string.replace(replaceRegexp, escaper) : string;
    };
  };
  _.escape = createEscaper(escapeMap);
  _.unescape = createEscaper(unescapeMap);

  // If the value of the named `property` is a function then invoke it with the
  // `object` as context; otherwise, return it.
  _.result = function(object, property, fallback) {
    var value = object == null ? void 0 : object[property];
    if (value === void 0) {
      value = fallback;
    }
    return _.isFunction(value) ? value.call(object) : value;
  };

  // Generate a unique integer id (unique within the entire client session).
  // Useful for temporary DOM ids.
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = ++idCounter + '';
    return prefix ? prefix + id : id;
  };

  // By default, Underscore uses ERB-style template delimiters, change the
  // following template settings to use alternative delimiters.
  _.templateSettings = {
    evaluate    : /<%([\s\S]+?)%>/g,
    interpolate : /<%=([\s\S]+?)%>/g,
    escape      : /<%-([\s\S]+?)%>/g
  };

  // When customizing `templateSettings`, if you don't want to define an
  // interpolation, evaluation or escaping regex, we need one that is
  // guaranteed not to match.
  var noMatch = /(.)^/;

  // Certain characters need to be escaped so that they can be put into a
  // string literal.
  var escapes = {
    "'":      "'",
    '\\':     '\\',
    '\r':     'r',
    '\n':     'n',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  var escaper = /\\|'|\r|\n|\u2028|\u2029/g;

  var escapeChar = function(match) {
    return '\\' + escapes[match];
  };

  // JavaScript micro-templating, similar to John Resig's implementation.
  // Underscore templating handles arbitrary delimiters, preserves whitespace,
  // and correctly escapes quotes within interpolated code.
  // NB: `oldSettings` only exists for backwards compatibility.
  _.template = function(text, settings, oldSettings) {
    if (!settings && oldSettings) settings = oldSettings;
    settings = _.defaults({}, settings, _.templateSettings);

    // Combine delimiters into one regular expression via alternation.
    var matcher = RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');

    // Compile the template source, escaping string literals appropriately.
    var index = 0;
    var source = "__p+='";
    text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
      source += text.slice(index, offset).replace(escaper, escapeChar);
      index = offset + match.length;

      if (escape) {
        source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
      } else if (interpolate) {
        source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
      } else if (evaluate) {
        source += "';\n" + evaluate + "\n__p+='";
      }

      // Adobe VMs need the match returned to produce the correct offest.
      return match;
    });
    source += "';\n";

    // If a variable is not specified, place data values in local scope.
    if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

    source = "var __t,__p='',__j=Array.prototype.join," +
      "print=function(){__p+=__j.call(arguments,'');};\n" +
      source + 'return __p;\n';

    try {
      var render = new Function(settings.variable || 'obj', '_', source);
    } catch (e) {
      e.source = source;
      throw e;
    }

    var template = function(data) {
      return render.call(this, data, _);
    };

    // Provide the compiled source as a convenience for precompilation.
    var argument = settings.variable || 'obj';
    template.source = 'function(' + argument + '){\n' + source + '}';

    return template;
  };

  // Add a "chain" function. Start chaining a wrapped Underscore object.
  _.chain = function(obj) {
    var instance = _(obj);
    instance._chain = true;
    return instance;
  };

  // OOP
  // ---------------
  // If Underscore is called as a function, it returns a wrapped object that
  // can be used OO-style. This wrapper holds altered versions of all the
  // underscore functions. Wrapped objects may be chained.

  // Helper function to continue chaining intermediate results.
  var result = function(instance, obj) {
    return instance._chain ? _(obj).chain() : obj;
  };

  // Add your own custom functions to the Underscore object.
  _.mixin = function(obj) {
    _.each(_.functions(obj), function(name) {
      var func = _[name] = obj[name];
      _.prototype[name] = function() {
        var args = [this._wrapped];
        push.apply(args, arguments);
        return result(this, func.apply(_, args));
      };
    });
  };

  // Add all of the Underscore functions to the wrapper object.
  _.mixin(_);

  // Add all mutator Array functions to the wrapper.
  _.each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name === 'shift' || name === 'splice') && obj.length === 0) delete obj[0];
      return result(this, obj);
    };
  });

  // Add all accessor Array functions to the wrapper.
  _.each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return result(this, method.apply(this._wrapped, arguments));
    };
  });

  // Extracts the result from a wrapped and chained object.
  _.prototype.value = function() {
    return this._wrapped;
  };

  // Provide unwrapping proxy for some methods used in engine operations
  // such as arithmetic and JSON stringification.
  _.prototype.valueOf = _.prototype.toJSON = _.prototype.value;

  _.prototype.toString = function() {
    return '' + this._wrapped;
  };

  // AMD registration happens at the end for compatibility with AMD loaders
  // that may not enforce next-turn semantics on modules. Even though general
  // practice for AMD registration is to be anonymous, underscore registers
  // as a named module because, like jQuery, it is a base library that is
  // popular enough to be bundled in a third party lib, but not be part of
  // an AMD load request. Those cases could generate an error when an
  // anonymous define() is called outside of a loader request.
  if (true) {
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = function() {
      return _;
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  }
}.call(this));


/***/ }),

/***/ "./src/Apps/DOCUMENT/IHM/document.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/*global define, console*/
(function umdRequire(root, factory) {
    'use strict';

    if (true) {
        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__("external \"jQuery\""), __webpack_require__("./node_modules/underscore/underscore.js"), __webpack_require__("./src/Apps/DOCUMENT/IHM/widgets/widget.js")], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    } else {
        factory(window.jQuery, window._);
    }
})(window, function require_document($, _) {
    'use strict';

    var eventList = ["beforeRender", "ready", "change", "displayMessage", "displayError", "validate", "attributeBeforeRender", "attributeReady", "attributeHelperSearch", "attributeHelperResponse", "attributeHelperSelect", "attributeArrayChange", "actionClick", "attributeAnchorClick", "beforeClose", "close", "beforeSave", "afterSave", "attributeDownloadFile", "attributeUploadFile", "attributeUploadFileDone", "beforeDelete", "afterDelete", "beforeRestore", "afterRestore", "failTransition", "successTransition", "beforeDisplayTransition", "afterDisplayTransition", "beforeTransition", "beforeTransitionClose", "destroy", "attributeCreateDialogDocumentBeforeSetFormValues", "attributeCreateDialogDocumentBeforeSetTargetValue", "attributeCreateDialogDocumentReady", "attributeCreateDialogDocumentBeforeClose", "attributeCreateDialogDocumentBeforeDestroy"];

    //Create a new kind of event
    var ErrorNotLoaded = function dcpDocument_ErrorNotLoaded(message) {
        this.name = 'WidgetDocumentNotLoaded';
        this.message = message || 'The document widget is not loaded, wait for the documentloaded event';
    };
    ErrorNotLoaded.prototype = Object.create(Error.prototype);
    ErrorNotLoaded.prototype.constructor = ErrorNotLoaded;

    $.widget("dcp.document", {

        _template: _.template('<iframe class="dcpDocumentWrapper" name="<%- options.iframeName %>" style="border : 0;" data-src="api/v1/documents/0.html#widgetValue<%- options.json_encode %>"></iframe>'),

        defaults: {
            "resizeMarginHeight": 3,
            "resizeMarginWidth": 0,
            "resizeDebounceTime": 50,
            "withoutResize": false,
            "iframeName": _.uniqueId("documentFrame"),
            "noRouter": true,
            "eventPrefix": "document"
        },

        /**
         * Create the widget
         *
         * Check if initid is present
         */
        _create: function dcpDocument_create() {
            this.options = _.extend({}, this.defaults, this.options);
            this.options.eventListener = {};
            this.options.constraintList = {};
            this.options.cssToInject = [];
            this.options.jsToInject = [];
            this._render();
            this._bindEvents();
        },

        /**
         * Create the iframe with the content and register to load event
         */
        _render: function dcpDocument_render() {
            var $iframe,
                currentWidget = this,
                documentWindow,
                options_encode;
            //inject the iframe
            options_encode = JSON.stringify(_.omit(this.options, "resizeMarginHeight", "resizeMarginWidth", "resizeDebounceTime", "withoutResize", "iframeName", "eventPrefix", "eventListener", "constraintList"));
            this.element.empty().append(this._template({ options: {
                    iframeName: this.options.iframeName,
                    json_encode: options_encode
                } }));
            //bind the internal controller to the documentWidget
            $iframe = this.element.find(".dcpDocumentWrapper");
            //Listen the load to the iframe (initial JS added and page loaded)

            if ($iframe.length > 0) {
                documentWindow = $iframe[0].contentWindow;
                //Use this way to set url for firefox (when the document is in iframe in another document)
                documentWindow.location.href = $iframe.data("src");
                // This event is used when use a hard link (aka href anchor) to change document
                // It is load also the first time
                $iframe.on("load", function dcpDocument_setReadyEvent() {
                    documentWindow.documentLoaded = function dcpDocument_loadedCallback(domNode, viewData) {
                        // Re Bind the internalController function to the current widget
                        currentWidget._bindInternalWidget.call(currentWidget, domNode.data("dcpDocumentController"), viewData);
                        // voidLoaded is true when document 0 is loaded
                        currentWidget.element.data("voidLoaded", !viewData || !viewData.initid);
                    };

                    $(documentWindow).on("unload", function dcpDocument_setUnloadEvent() {
                        currentWidget._unbindInternalWidget.call(currentWidget);
                    });
                    if (documentWindow.dcp && documentWindow.dcp.triggerReload && documentWindow.dcp.documentReady === false) {
                        documentWindow.dcp.triggerReload();
                    }
                });
            }
            this._resize();
        },

        /**
         * Suppress internal widget reference
         */
        _unbindInternalWidget: function dcpDocument_unbindInternalWidget() {
            this.element.data("internalWidgetInitialised", false);
            this.element.data("internalWidget", false);
            this._trigger("internalWidgetUnloaded");
            this._trigger("unloaded");
        },

        rebindEvents: function dcpDocument_rebindEvents() {
            var internalController = this.element.data("internalWidget");
            if (internalController) {

                //Rebind event
                _.each(this.options.eventListener, function dcpDocument_bindEvent(currentEvent) {
                    internalController.addEventListener(currentEvent);
                });
                //Rebind constraint
                _.each(this.options.constraintList, function dcpDocument_bindEvent(currentConstaint) {
                    internalController.addConstraint(currentConstaint);
                });
            }
        },

        reinjectCSSAndJS: function dcpDocument_reinjectCSSAndJS() {
            var internalController = this.element.data("internalWidget");
            if (internalController) {
                try {
                    internalController.injectCSS(this.options.cssToInject);
                    internalController.injectJS(this.options.jsToInject);
                } catch (e) {}
            }
        },

        /**
         * Bind the internal controller to the current widget
         * Reinit the constraint and the event
         *
         * @param internalController
         * @param voidLoaded
         */
        _bindInternalWidget: function dcpDocument_bindInternalWidget(internalController, voidLoaded) {
            this.element.data("internalWidget", internalController);
            if (!voidLoaded) {
                this.rebindEvents();
                this.reinjectCSSAndJS();
            }
            this.element.data("internalWidgetInitialised", true);
            if (voidLoaded) {
                this._trigger("loaded", {}, { "isEmpty": true });
                return this;
            }
            this._trigger("loaded");
            return this;
        },

        /**
         * Add resize event
         */
        _bindEvents: function dcpDocument_bindEvents() {
            if (!this.options.withoutResize) {
                $(window).on("resize" + this.eventNamespace, _.debounce(_.bind(this._resize, this), parseInt(this.options.resizeDebounceTime, 10)));
                this._resize();
            }
        },

        /**
         * Compute the size of the widget
         */
        _resize: function dcpDocument_resize() {
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
                _.defer(function dcpDocument_computeHeight() {
                    //noinspection JSValidateTypes
                    $documentWrapper.height(element.innerHeight() - parseInt(currentWidget.options.resizeMarginHeight, 10));
                });
            }
        },

        tryToDestroy: function dcpDocument_tryToDestroy() {
            var currentWidget = this;
            return new Promise(function dcpDocument_tryToDestroy_promise(resolve, reject) {
                var internalWidget;
                if (currentWidget.isLoaded()) {
                    internalWidget = currentWidget.element.data("internalWidget");
                    internalWidget.tryToDestroy().then(function dcpDocument_destroy_then() {
                        resolve();
                        currentWidget._destroy();
                    })["catch"](function dcpDocument_destroy_catch(errorMessage) {
                        reject(errorMessage);
                    });
                    return;
                }
                resolve();
                currentWidget._destroy();
            });
        },

        /**
         * Destroy the widget
         */
        _destroy: function dcpDocument_destroy() {
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
        _checkEventName: function documentController_checkEventName(eventName) {
            if (_.isString(eventName) && (eventName.indexOf("custom:") === 0 || _.find(eventList, function documentController_CheckEventType(currentEventType) {
                return currentEventType === eventName;
            }))) {
                return true;
            }
            throw new Error("The event type " + eventName + " is not known. It must be one of " + eventList.join(" ,"));
        },

        /**
         * Update options
         */
        options: function dcpDocument_options() {
            throw new Error("You cannot modify the options, you need to suppress the widget");
        },

        /**
         * Fetch a new document
         *
         * Use internal controller if ready
         * Re-render the widget if internal is not ready
         *
         * @param values
         * @param options
         */
        fetchDocument: function dcpDocument_fetchDocument(values, options) {
            var internalWidget,
                currentWidget = this,
                fetchPromise = null,
                initWidget = function dpcDocument_successWidget() {
                currentWidget.rebindEvents.call(currentWidget);
                currentWidget.element.data("voidLoaded", false);
            };
            options = options || {};

            if (!values.initid) {
                throw new Error("You need to set the initid to fetch the document");
            }
            _.each(_.pick(values, "initid", "revision", "viewId", "customClientData"), function dcpDocument_setNewOptions(value, key) {
                currentWidget.options[key] = value;
            });

            if (this.element.data("internalWidgetInitialised")) {
                internalWidget = this.element.data("internalWidget");

                if (options.success) {
                    // @deprecated : use promise instead
                    options.success = _.wrap(options.success, function dcpDocument_success(success) {
                        initWidget.apply(this, _.rest(arguments));
                        return success.apply(this, _.rest(arguments));
                    });
                }
                if (options.error) {
                    // @deprecated : use promise instead
                    options.error = _.wrap(options.error, function dcpDocument_error(error) {
                        initWidget.apply(this, _.rest(arguments));
                        return error.apply(this, _.rest(arguments));
                    });
                }
                fetchPromise = internalWidget.fetchDocument.call(internalWidget, values, options);

                if (!options.success) {
                    fetchPromise.then(initWidget);
                }
                if (!options.error) {
                    fetchPromise.catch(initWidget);
                }
                return fetchPromise;
            } else {
                this._render();
            }
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
        addEventListener: function dcpDocument_addEventListener(eventType, options, callback) {
            var currentEvent,
                currentWidget = this;
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
                currentEvent.eventCallback = _.wrap(currentEvent.eventCallback, function dcpDocument_onceWrapper(callback) {
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
            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
                this.element.data("internalWidget").addEventListener(currentEvent);
            }
            return currentEvent.name;
        },

        /**
         * List of the events of the current widget
         *
         * @returns {*}
         */
        listEventListeners: function documentControllerListEvents() {
            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
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
        removeEventListener: function dcpDocument_removeEventListener(eventName) {
            var removed = [],
                testRegExp = new RegExp("\\" + eventName + "$"),
                newList,
                eventList;
            newList = _.filter(this.options.eventListener, function dcpDocument_removeCurrentEvent(currentEvent) {
                if (currentEvent.name === eventName || testRegExp.test(currentEvent.name)) {
                    removed.push(currentEvent);
                    return false;
                }
                return true;
            });
            eventList = {};
            _.each(newList, function dcp_documentIterateEach(currentEvent) {
                eventList[currentEvent.name] = currentEvent;
            });
            this.options.eventListener = eventList;
            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
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
        addConstraint: function dcpDocument_addConstraint(options, callback) {
            var parameters,
                currentWidget = this;
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
                    "documentCheck": function dcpDocument_defaultDocumentCheck() {
                        return true;
                    },
                    "attributeCheck": function dcpDocument_defaultAttributeCheck() {
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
                parameters.eventCallback = _.wrap(parameters.constraintCheck, function dcpDocument_onceWrapper(callback) {
                    try {
                        callback.apply(this, _.rest(arguments));
                    } catch (e) {
                        console.error(e);
                    }
                    currentWidget.removeConstraint(parameters.name, parameters.externalConstraint);
                });
            }
            this.options.constraintList[parameters.name] = parameters;
            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
                this.element.data("internalWidget").addConstraint(parameters);
            }
            return parameters.name;
        },
        /**
         * List the constraint of the widget
         *
         * @returns {*}
         */
        listConstraints: function documentControllerListConstraint() {
            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
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
        removeConstraint: function dcpDocument_removeConstraint(constraintName) {
            var removed = [],
                newConstraintList,
                constraintList,
                testRegExp = new RegExp("\\" + constraintName + "$");
            newConstraintList = _.filter(this.options.constraintList, function dcpDocument_removeConstraint(currentConstraint) {
                if (currentConstraint.name === constraintName || testRegExp.test(currentConstraint.name)) {
                    removed.push(currentConstraint);
                    return false;
                }
                return true;
            });
            constraintList = {};
            _.each(newConstraintList, function dcpDocument_reinitConstraint(currentConstraint) {
                constraintList[currentConstraint.name] = currentConstraint;
            });
            this.options.constraintList = constraintList;
            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
                this.element.data("internalWidget").removeConstraint(constraintName, true);
            }
            return removed;
        },

        isLoaded: function dcpDocument_isLoaded() {
            return this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded");
        },

        injectCSS: function documentController_injectCSS(cssToInject) {
            if (!_.isArray(cssToInject) && !_.isString(cssToInject)) {
                throw new Error("The css to inject must be an array string or a string");
            }
            if (_.isString(cssToInject)) {
                cssToInject = [cssToInject];
            }

            this.options.cssToInject = _.union(this.options.cssToInject, cssToInject);

            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
                this.element.data("internalWidget").injectCSS(cssToInject);
            }
        },

        injectJS: function documentController_injectCSS(jsToInject) {
            if (!_.isArray(jsToInject) && !_.isString(jsToInject)) {
                throw new Error("The js to inject must be an array string or a string");
            }
            if (_.isString(jsToInject)) {
                jsToInject = [jsToInject];
            }

            this.options.jsToInject = _.union(this.options.jsToInject, jsToInject);

            if (this.element.data("internalWidgetInitialised") && !this.element.data("voidLoaded")) {
                this.element.data("internalWidget").injectJS(jsToInject);
            }
        }

    });

    //noinspection JSValidateJSDoc
    /**
     * Wrap the bridge that find the function to be executed
     * Search in the current widget if the function is here
     * Search in the internal widget (if ready to find the widget)
     *
     * @type {Function|function(): Function|function(): _Chain<T>|*}
     */
    //noinspection JSUnresolvedVariable
    $.fn.document = _.wrap($.fn.document, function dcpDocument_wrap(initialDocumentBridge, methodName) {
        // jshint ignore:line
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

/***/ }),

/***/ "./src/Apps/DOCUMENT/IHM/smartElement.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__("./src/Apps/DOCUMENT/IHM/document.js");

/***/ }),

/***/ "./src/Apps/DOCUMENT/IHM/widgets/widget.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/*global define*/

(function umdRequire(root, factory) {
    'use strict';

    if (true) {
        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__("external \"jQuery\"")], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    } else {
        factory(window.jQuery);
    }
})(window, function documentCatalog(jQuery) {
    "use strict";

    var ErrorNoSuchMethod = function widget_ErrorNoSuchMethod(message) {
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
            var events,
                elem = null,
                i;
            for (i = 0, elem; (elem = elems[i]) != null; i++) {
                // jshint ignore:line
                try {
                    // Only trigger remove when necessary to save time
                    events = $._data(elem, "events");
                    if (events && events.remove) {
                        $(elem).triggerHandler("remove");
                    }
                } catch (e) {}
            }
            _cleanData(elems);
        };

        $.widget = function widget_initWidget(name, Base, prototype) {
            var fullName,
                existingConstructor,
                Constructor,
                basePrototype,

            // proxiedPrototype allows the provided prototype to remain unmodified
            // so that it can be used as a mixin for multiple widgets (#8876)
            proxiedPrototype = {},
                namespace = name.split(".")[0];

            name = name.split(".")[1];
            fullName = namespace + "-" + name;

            if (!prototype) {
                prototype = Base;
                Base = $.Widget;
            }

            if ($.isArray(prototype)) {
                prototype = $.extend.apply(null, [{}].concat(prototype));
            }

            // create selector for plugin
            // $.expr[ ":" ] is deprecated with jQuery 3.0.0+ in favor of $.expr.pseudos
            // $.expr[ ":" ][ fullName.toLowerCase() ] = function widget_createSelector(elem) {
            //     return Boolean($(elem).data(fullName));
            // };
            $.expr.pseudos[fullName.toLowerCase()] = function widget_createSelector(elem) {
                return Boolean($(elem).data(fullName));
            };

            $[namespace] = $[namespace] || {};
            existingConstructor = $[namespace][name];
            Constructor = $[namespace][name] = function widget_Constructor(options, element) {
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
                version: prototype.version,
                // copy the object used to create the prototype in case we need to
                // redefine the widget later
                _proto: $.extend({}, prototype),
                // track widgets that inherit from this widget in case this widget is
                // redefined after a widget inherits from it
                _childConstructors: []
            });

            basePrototype = new Base();
            // we need to make the options hash a property directly on the new instance
            // otherwise we'll modify the options hash on the prototype that we're
            // inheriting from
            basePrototype.options = $.widget.extend({}, basePrototype.options);
            $.each(prototype, function widget_proxiedElements(prop, value) {
                if (!$.isFunction(value)) {
                    proxiedPrototype[prop] = value;
                    return;
                }
                proxiedPrototype[prop] = function widget_proxiedProperties() {
                    var _super = function widget_super() {
                        return Base.prototype[prop].apply(this, arguments);
                    },
                        _superApply = function widget_superApply(args) {
                        return Base.prototype[prop].apply(this, args);
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
                }();
            });
            Constructor.prototype = $.widget.extend(basePrototype, {}, proxiedPrototype, {
                constructor: Constructor,
                namespace: namespace,
                widgetName: name,
                widgetFullName: fullName
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
                for (key in input[inputIndex]) {
                    // jshint ignore:line
                    //noinspection JSUnfilteredForInLoop
                    value = input[inputIndex][key];
                    if (input[inputIndex].hasOwnProperty(key) && value !== undefined) {
                        // Clone objects
                        if ($.isPlainObject(value)) {
                            target[key] = $.isPlainObject(target[key]) ? $.widget.extend({}, target[key], value) :
                            // Don't extend strings, arrays, etc. with objects
                            $.widget.extend({}, value);
                            // Copy everything else by reference
                        } else {
                            target[key] = value;
                        }
                    }
                }
            }
            return target;
        };

        $.widget.bridge = function widget_bridge(name, Object) {
            var fullName = Object.prototype.widgetFullName || name;
            $.fn[name] = function widget_callElement(options) {
                var isMethodCall = typeof options === "string",
                    args = slice.call(arguments, 1),
                    returnValue = this;

                // allow multiple hashes to be passed on init
                options = !isMethodCall && args.length ? $.widget.extend.apply(null, [options].concat(args)) : options;

                if (isMethodCall) {
                    this.each(function widget_eachMethodCall() {
                        var methodValue,
                            instance = $(this).data(fullName);
                        if (options === "instance") {
                            returnValue = instance;
                            return false;
                        }
                        if (!instance) {
                            return $.error("cannot call methods on " + name + " prior to initialization; " + "attempted to call method '" + options + "'");
                        }
                        if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                            throw new ErrorNoSuchMethod("no such method '" + options + "' for " + name + " widget instance");
                        }
                        methodValue = instance[options].apply(instance, args);
                        if (methodValue !== instance && methodValue !== undefined) {
                            returnValue = methodValue && methodValue.jquery ? returnValue.pushStack(methodValue.get()) : methodValue;
                            return false;
                        }
                    });
                } else {

                    // Allow multiple hashes to be passed on init
                    if (args.length) {
                        options = $.widget.extend.apply(null, [options].concat(args));
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

        $.Widget = function widget_Widget() /* options, element */{};
        $.Widget._childConstructors = [];

        $.Widget.prototype = {
            widgetName: "widget",
            defaultElement: "<div>",
            options: {
                classes: {},
                disabled: false,
                eventPrefix: null,
                // callbacks
                create: null
            },
            _createWidget: function widget_createWidget(options, element) {
                element = $(element || this.defaultElement || this)[0];
                this.element = $(element);
                this.uuid = widgetUuid++;
                this.eventNamespace = "." + this.widgetName + this.uuid;
                this.options = $.widget.extend({}, this.options, this._getCreateOptions(), options);

                this.bindings = $();
                this.classesElementLookup = {};
                if (this.options.eventPrefix === null) {
                    this.options.eventPrefix = this.widgetName;
                }

                if (element !== this) {
                    $(element).data(this.widgetFullName, this);
                    this._on(true, this.element, {
                        remove: function widget_remove(event) {
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
                this.options = $.widget.extend({}, this.options, this._getCreateOptions(), options);

                this._create();

                if (this.options.disabled) {
                    this._setOptionDisabled(this.options.disabled);
                }

                this._trigger("create", null, this._getCreateEventData());
                this._init();
            },
            _getCreateOptions: function widget_getCreateOptions() {
                return {};
            },
            _getCreateEventData: $.noop,
            _create: $.noop,
            _init: $.noop,

            destroy: function widget_destroy() {
                var that = this;

                this._destroy();
                $.each(this.classesElementLookup, function widget_destroyClass(key, value) {
                    that._removeClass(value, key);
                });

                // We can probably remove the unbind calls in 2.0
                // all event bindings should go through this._on()
                this.element.off(this.eventNamespace).removeData(this.widgetFullName);
                this.widget().off(this.eventNamespace).removeAttr("aria-disabled");

                // Clean up events and states
                this.bindings.off(this.eventNamespace);
            },
            _destroy: $.noop,

            widget: function widget_widget() {
                return this.element;
            },

            option: function widget_option(key, value) {
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
                        curOption = options[key] = $.widget.extend({}, this.options[key]);
                        for (i = 0; i < parts.length - 1; i++) {
                            curOption[parts[i]] = curOption[parts[i]] || {};
                            curOption = curOption[parts[i]];
                        }
                        key = parts.pop();
                        if (arguments.length === 1) {
                            return curOption[key] === undefined ? null : curOption[key];
                        }
                        curOption[key] = value;
                    } else {
                        if (arguments.length === 1) {
                            return this.options[key] === undefined ? null : this.options[key];
                        }
                        options[key] = value;
                    }
                }

                this._setOptions(options);

                return this;
            },
            _setOptions: function widget__setOptions(options) {
                var key;

                for (key in options) {
                    // jshint ignore:line
                    //noinspection JSUnfilteredForInLoop
                    this._setOption(key, options[key]);
                }

                return this;
            },
            _setOption: function widget__setOption(key, value) {
                this.options[key] = value;

                return this;
            },

            _setOptionClasses: function widget__setOptionClasses(value) {
                var classKey, elements, currentElements;

                for (classKey in value) {
                    // jshint ignore:line
                    //noinspection JSUnfilteredForInLoop
                    currentElements = this.classesElementLookup[classKey];
                    //noinspection JSUnfilteredForInLoop
                    if (value[classKey] === this.options.classes[classKey] || !currentElements || !currentElements.length) {
                        continue;
                    }

                    // We are doing this to create a new jQuery object because the _removeClass() call
                    // on the next line is going to destroy the reference to the current elements being
                    // tracked. We need to save a copy of this collection so that we can add the new classes
                    // below.
                    elements = $(currentElements.get());
                    //noinspection JSUnfilteredForInLoop
                    this._removeClass(currentElements, classKey);

                    // We don't use _addClass() here, because that uses this.options.classes
                    // for generating the string of classes. We want to use the value passed in from
                    // _setOption(), this is the new value of the classes option which was passed to
                    // _setOption(). We pass this value directly to _classes().
                    //noinspection JSUnfilteredForInLoop
                    elements.addClass(this._classes({
                        element: elements,
                        keys: classKey,
                        classes: value,
                        add: true
                    }));
                }
            },

            _classes: function widget__classes(options) {
                var full = [];
                var that = this,
                    processClassString;

                options = $.extend({
                    element: this.element,
                    classes: this.options.classes || {}
                }, options);

                processClassString = function widget_processClassString(classes, checkOption) {
                    var current, i;
                    for (i = 0; i < classes.length; i++) {
                        current = that.classesElementLookup[classes[i]] || $();
                        if (options.add) {
                            // unique is deprecated in jQuery 3.0.0+ renamed to uniqueSort
                            var jqueryVersion = +$().jquery.split('.')[0];
                            if (jqueryVersion >= 3) {
                                current = $($.uniqueSort(current.get().concat(options.element.get())));
                            } else {
                                current = $($.unique(current.get().concat(options.element.get())));
                            }
                        } else {
                            current = $(current.not(options.element).get());
                        }
                        that.classesElementLookup[classes[i]] = current;
                        full.push(classes[i]);
                        if (checkOption && options.classes[classes[i]]) {
                            full.push(options.classes[classes[i]]);
                        }
                    }
                };

                if (options.keys) {
                    processClassString(options.keys.match(/\S+/g) || [], true);
                }
                if (options.extra) {
                    processClassString(options.extra.match(/\S+/g) || []);
                }

                return full.join(" ");
            },

            _removeClass: function widget__removeClass(element, keys, extra) {
                return this._toggleClass(element, keys, extra, false);
            },

            _addClass: function widget__addClass(element, keys, extra) {
                return this._toggleClass(element, keys, extra, true);
            },

            _toggleClass: function widget__toggleClass(element, keys, extra, add) {
                add = typeof add === "boolean" ? add : extra;
                var shift = typeof element === "string" || element === null,
                    options = {
                    extra: shift ? keys : extra,
                    keys: shift ? element : keys,
                    element: shift ? this.element : element,
                    add: add
                };
                options.element.toggleClass(this._classes(options), add);
                return this;
            },

            _on: function widget__on(suppressDisabledCheck, element, handlers) {
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
                        return (typeof handler === "string" ? instance[handler] : handler).apply(instance, arguments);
                    };

                    // copy the guid so direct unbinding works
                    if (typeof handler !== "string") {
                        handlerProxy.guid = handler.guid = handler.guid || handlerProxy.guid || $.guid++;
                    }

                    var match = event.match(/^(\w+)\s*(.*)$/),
                        eventName = match[1] + instance.eventNamespace,
                        selector = match[2];
                    // delegate is deprecated in jQuery 3.0.0+ in favor of on method
                    var jqueryVersion = +$().jquery.split('.')[0];
                    if (selector) {
                        if (jqueryVersion >= 3) {
                            delegateElement.on(eventName, selector, handlerProxy);
                        } else {
                            delegateElement.delegate(selector, eventName, handlerProxy);
                        }
                    } else {
                        // bind is deprecated in jQuery 3.0.0+ in favor of on method
                        if (jqueryVersion >= 3) {
                            element.on(eventName, handlerProxy);
                        } else {
                            element.bind(eventName, handlerProxy);
                        }
                    }
                });
            },

            _off: function widget__off(element, eventName) {
                eventName = (eventName || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace;
                element.unbind(eventName).undelegate(eventName);
            },

            _delay: function widget__delay(handler, delay) {
                var handlerProxy = function handlerProxy() {
                    return (typeof handler === "string" ? instance[handler] : handler).apply(instance, arguments);
                };

                var instance = this;
                return setTimeout(handlerProxy, delay || 0);
            },

            _trigger: function widget__trigger(type, event, data) {
                var prop,
                    orig,
                    callback = this.options[type];

                data = data || {};
                event = $.Event(event);
                event.type = this.options.eventPrefix ? this.options.eventPrefix + type : type;
                event.type = event.type.toLocaleLowerCase();
                // the original event may come from any element
                // so we need to reset the target on the new event
                event.target = this.element[0];

                // copy original event properties over to the new event
                orig = event.originalEvent;
                if (orig) {
                    for (prop in orig) {
                        // jshint ignore:line
                        if (!(prop in event)) {
                            //noinspection JSUnfilteredForInLoop
                            event[prop] = orig[prop];
                        }
                    }
                }

                this.element.trigger(event, data);
                return !($.isFunction(callback) && callback.apply(this.element[0], [event].concat(data)) === false || event.isDefaultPrevented());
            }
        };
    })(jQuery);
});

/***/ }),

/***/ "external \"jQuery\"":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_external__jQuery___;

/***/ })

/******/ });
});
//# sourceMappingURL=smartElement.js.map