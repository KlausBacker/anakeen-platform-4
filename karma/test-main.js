var allTestFiles = ['test-css', 'jasmine-jquery'];
var TEST_REGEXP = /\/(test)[a-zA-Z]*\.js$/i;
var pathToModule = function (path)
{
    return path.replace(/^\/.*IHM\//, "").replace(/\.js$/, '');
};

if (!window.console) {
    window.console = {};
    window.console.log = function (x)
    {
    };
}
if (!window.console.error) {
    window.console.error = window.console.log;
}
if (!(window.console.time)) {
    window.console.timeEnd = function (x)
    {
    };
    window.console.time = function (x)
    {
    };
}


if (!Object.keys) {
    Object.keys = (function ()
    {
        'use strict';
        var hasOwnProperty = Object.prototype.hasOwnProperty,
            hasDontEnumBug = !({toString: null}).propertyIsEnumerable('toString'),
            dontEnums = [
                'toString',
                'toLocaleString',
                'valueOf',
                'hasOwnProperty',
                'isPrototypeOf',
                'propertyIsEnumerable',
                'constructor'
            ],
            dontEnumsLength = dontEnums.length;

        return function (obj)
        {
            if (typeof obj !== 'object' && (typeof obj !== 'function' || obj === null)) {
                throw new TypeError('Object.keys called on non-object');
            }

            var result = [], prop, i;

            for (prop in obj) {
                if (hasOwnProperty.call(obj, prop)) {
                    result.push(prop);
                }
            }

            if (hasDontEnumBug) {
                for (i = 0; i < dontEnumsLength; i++) {
                    if (hasOwnProperty.call(obj, dontEnums[i])) {
                        result.push(dontEnums[i]);
                    }
                }
            }
            return result;
        };
    }());
}
if (!Array.prototype.forEach) {
    Array.prototype.forEach = function (fn, scope)
    {
        'use strict';
        var i, len;
        for (i = 0, len = this.length; i < len; ++i) {
            if (i in this) {
                fn.call(scope, this[i], i, this);
            }
        }
    };
}
Object.keys(window.__karma__.files).forEach(function (file)
{
    if (TEST_REGEXP.test(file)) {
        // Normalize paths to RequireJS module names.
        allTestFiles.push(pathToModule(file));
    }
});

window.dcp = window.dcp || {};

window.dcp.executeTests = (function ()
{
    "use strict";
    var nbTest = allTestFiles.length;
    return function ()
    {
        if (--nbTest < 3) {
            return window.__karma__.start();
        }
    };
})();


require.config({
    // Karma serves files under /base, which is the basePath from your config file
    baseUrl: 'http://localhost:9876/dynacase/DOCUMENT/IHM/',
    shim: {
        "bootstrap": ['jquery'],
        "kendo-culture": ['kendo'],
        "jasmine-jquery": ['jquery'],
        "test-css": ['jquery'],
        "ckeditor-jquery": ['jquery', 'ckeditor']
    },
    paths: {
        //noinspection Annotator
        'dcpDocumentTemplate': '../../',
        "dcpDocument": "../IHM",
        "dcpDocumentTest": "../IHM/test",
        "text": '../../lib/RequireJS/text',
        "jquery": "../../lib/KendoUI/2014.3/js/jquery",
        "underscore": "../../lib/underscore/underscore",
        "backbone": "../../lib/backbone/backbone",
        "mustache": "../../lib/mustache.js/mustache",
        "bootstrap": "../../lib/bootstrap/3/js/bootstrap",
        "kendo": "../../lib/KendoUI/2014.3/js/",
        "kendo/kendo.core": "../../guest.php?jsFile=2014.3/js/kendo.core&app=DOCUMENT&action=WRAP_KENDO",
        "kendo-culture-fr": "../../lib/KendoUI/2014.3/js/cultures/kendo.culture.fr-FR",
        "ckeditor": "../../lib/ckeditor/4/ckeditor",
        "ckeditor-jquery": "../../lib/ckeditor/4/adapters/jquery",
        "jasmine-jquery": '../../lib/jasmine/jasmine-jquery',
        "test-css": '../../../base/test-css',
        "datatables": "../../lib/jquery-dataTables/1.10/js/jquery.dataTables",
        "datatables-bootstrap": "../../lib/jquery-dataTables/1.10/bootstrap/3"
    },
    // dynamically load all test files
    deps: allTestFiles,

    "config": {
        "text": {
            env: 'xhr',
            useXhr: function (url, protocol, hostname, port)
            {
                return true;
            }
        }
    },

    urlArgs: "bust=" + (new Date()).getTime()
    // we have to kickoff jasmine, as it is asynchronous
    //,callback : window.__karma__.start
});
