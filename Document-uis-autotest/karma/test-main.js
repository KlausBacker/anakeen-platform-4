/*********************************************************************************************************************
 *
 * Main launcher of the test by karma
 * Search all the file with test*.js in the source, add it to the launch list and launch it
 * The file are get from the
 *
 ********************************************************************************************************************/


//Add this elements for old browsers
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

//Add this files (inject css and jasmine deps)
var allTestFiles = ['test-css', 'jasmine-jquery'];
var TEST_REGEXP = /\/(test)[a-zA-Z]*\.js$/i;
var pathToModule = function (path)
{
    'use strict';
    return path.replace(/^\/.*IHM\//, "").replace(/\.js$/, '');
};


Object.keys(window.__karma__.files).forEach(function (file)
{
    'use strict';
    if (TEST_REGEXP.test(file)) {
        // Normalize paths to RequireJS module names.
        allTestFiles.push(pathToModule(file));
    }
});

window.dcp = window.dcp || {};

//This function is called by each test file, the test are launched when all the files are loaded
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


require.config({ // jshint ignore:line
    // Karma serves files under /base, which is the basePath from your config file
    baseUrl: 'http://localhost:9876/dynacase/DOCUMENT/IHM/',
    shim: {
        "bootstrap": ['jquery'],
        "jasmine-jquery": ['jquery'],
        "test-css": ['jquery'],
        "ckeditor-jquery": ['jquery', 'ckeditor']
    },
    paths: {
        //noinspection Annotator
        'dcpContextRoot': '../../',
        "dcpDocument": "../IHM",
        "dcpDocumentTest": "../IHM/test",
        "text": '../../lib/RequireJS/text',
        "jquery": "../../lib/jquery/ddui/jquery",
        "underscore": "../../lib/underscore/underscore",
        "backbone": "../../lib/backbone/backbone",
        "tooltip": "../../lib/bootstrap/3/js/tooltip",
        "mustache": "../../lib/mustache.js/mustache",
        "bootstrap": "../../lib/bootstrap/3/js/bootstrap",
        "kendo-ddui" : "../../lib/KendoUI/ddui/js/kendo-ddui-builded",
        "kendo-culture-fr": "../../lib/KendoUI/ddui/js/cultures/kendo.culture.fr-FR",
        "ckeditor": "../../lib/ckeditor/4/ckeditor",
        "ckeditor-jquery": "../../lib/ckeditor/4/adapters/jquery",
        "jasmine-jquery": '../../lib/jasmine/jasmine-jquery',
        "test-css": '../../../base/test-css',
        "datatables": "../../lib/jquery-dataTables/1.10/js/jquery.dataTables",
        "datatables-bootstrap": "../../lib/jquery-dataTables/1.10/js/dataTables.bootstrap",
        "es6-promise" : "../../lib/es6-promise/es6-promise"
    },
    map: {
        "datatables-bootstrap" : {
            "datatables.net": "datatables"
        }
    },

    "config": {
        "text": {
            env: 'xhr',
            useXhr: function (url, protocol, hostname, port)
            {
                "use strict";
                return true;
            }
        }
    },

    urlArgs: "bust=" + (new Date()).getTime()
});

// dynamically load all test files after kendo
require(["kendo-ddui"], function() {
    require(allTestFiles, function() {

    });
})
