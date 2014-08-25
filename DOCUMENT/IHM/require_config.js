/*global require*/

(function() {
    "use strict";
    var config = {
        baseUrl : "DOCUMENT/",
        // The shim config allows us to configure dependencies for
        // scripts that do not call define() to register a module
        shim :    {
            "bootstrap" :       [ 'jquery' ],
            "kendo" :           [ 'jquery' ],
            "kendo-culture" :   [ 'kendo' ],
            "ckeditor-jquery" : [ 'jquery', 'ckeditor' ]
        },
        paths :   {
            "template":          "../",
            "widgets" :          "IHM/widgets",
            "collections" :      "IHM/collections",
            "models" :           "IHM/models",
            "views" :            "IHM/views",
            "routers" :          "IHM/routers",
            "text" :             '../lib/RequireJS/text',
            "jquery" :           "../lib/KendoUI/js/jquery",
            "underscore" :       "../lib/underscore/underscore",
            "backbone" :         "../lib/backbone/backbone",
            "mustache" :         "../lib/mustache.js/mustache",
            "bootstrap" :        "../lib/bootstrap/js/bootstrap",
            "kendo" :            "../lib/KendoUI/js/",
            "kendo-culture-fr" : "../lib/KendoUI/js/cultures/kendo.culture.fr-FR",
            "ckeditor" :         "../lib/ckeditor/ckeditor",
            "ckeditor-jquery" :  "../lib/ckeditor/adapters/jquery"
        }
    };
    if (window.dcp.ws) {
        config.urlArgs = "ws="+window.dcp.ws;
    }
    require.config(config);
})();
