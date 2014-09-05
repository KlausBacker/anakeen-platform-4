var allTestFiles = ['jasmine-jquery'];
var TEST_REGEXP = /\/(test)[a-zA-Z]*\.js$/i;

var pathToModule = function (path) {
    return path.replace(/^\/.*IHM\//, "").replace(/\.js$/, '');
};

Object.keys(window.__karma__.files).forEach(function (file) {
    if (TEST_REGEXP.test(file)) {
        // Normalize paths to RequireJS module names.
        allTestFiles.push(pathToModule(file));
    }
});

window.dcp = window.dcp || {};

window.dcp.executeTests = (function () {
    "use strict";
    var nbTest = allTestFiles.length;
    return function () {
        if (--nbTest < 2) {
            return window.__karma__.start();
        }
    };
})();

require.config({
    // Karma serves files under /base, which is the basePath from your config file
    baseUrl : 'http://localhost:9876/dynacase/DOCUMENT/IHM/',
    shim :    {
        "bootstrap" :       [ 'jquery' ],
        "kendo-culture" :   [ 'kendo' ],
        "jasmine-jquery" :  ['jquery'],
        "ckeditor-jquery" : [ 'jquery', 'ckeditor' ]
    },
    paths :   {
        'template' :         '../../',
        "widgets" :          "../IHM/widgets",
        "collections" :      "../IHM/collections",
        "models" :           "../IHM/models",
        "views" :            "../IHM/views",
        "routers" :          "../IHM/routers",
        "dcpDocumentTest" :  "../IHM/test",
        "text" :             '../../lib/RequireJS/text',
        "jquery" :           "../../lib/KendoUI/js/jquery",
        "underscore" :       "../../lib/underscore/underscore",
        "backbone" :         "../../lib/backbone/backbone",
        "mustache" :         "../../lib/mustache.js/mustache",
        "bootstrap" :        "../../lib/bootstrap/js/bootstrap",
        "kendo" :            "../../lib/KendoUI/js/",
        "kendo/kendo.core" : "../../guest.php?jsFile=kendo.core&app=DOCUMENT&action=WRAP_KENDO",
        "kendo-culture-fr" : "../../lib/KendoUI/js/cultures/kendo.culture.fr-FR",
        "ckeditor" :         "../../lib/ckeditor/4/ckeditor",
        "ckeditor-jquery" :  "../../lib/ckeditor/4/adapters/jquery",
        "jasmine-jquery" :   '../../lib/jasmine/jasmine-jquery'
    },
    // dynamically load all test files
    deps :    allTestFiles,

    "config" : {
        "text" : {
            env :    'xhr',
            useXhr : function (url, protocol, hostname, port) {
                // allow cross-domain requests
                // remote server allows CORS
                return true;
            }
        }
    },

    urlArgs : "bust=" + (new Date()).getTime()
    // we have to kickoff jasmine, as it is asynchronous
    //callback : window.__karma__.start
});
