/*global require*/

(function ()
{
    "use strict";
    var config = {
        baseUrl: "DOCUMENT/",
        // The shim config allows us to configure dependencies for
        // scripts that do not call define() to register a module
        shim: {
            "bootstrap": ['jquery'],
            "tooltip": ['bootstrap'],
            "kendo-ddui": ['jquery'],
            "kendo/kendo.core": ['jquery'],
            "kendo-culture": ['kendo'],
            "ckeditor-jquery": ['jquery', 'ckeditor']
        },
        paths: {
            "dcpContextRoot": "..",
            "dcpDocument": "IHM",
            "dcpDocumentTest": "IHM/test",
            "text": '../lib/RequireJS/text',
            "jquery": "../lib/jquery/ddui/jquery",
            "underscore": "../lib/underscore/underscore",
            "backbone": "../lib/backbone/backbone",
            "mustache": "../lib/mustache.js/mustache",
            "bootstrap": "../lib/bootstrap/3/js/bootstrap",
            "tooltip": "../lib/bootstrap/3/js/tooltip",
            "kendo": "../lib/KendoUI/ddui/js/",
            "kendo-ddui" : "../lib/KendoUI/ddui/js/kendo-ddui-builded.min",
            "kendo-culture-fr": "../lib/KendoUI/ddui/js/cultures/kendo.culture.fr-FR",
            "ckeditor": "../lib/ckeditor/4/ckeditor",
            "ckeditor-jquery": "../lib/ckeditor/4/adapters/jquery",
            "datatables.net": "../lib/jquery-dataTables/1.10/js/jquery.dataTables",
            "datatables": "../lib/jquery-dataTables/1.10/js/jquery.dataTables",
            "datatables-bootstrap": "../lib/jquery-dataTables/1.10/js/dataTables.bootstrap"
        },
        map: {
            "datatables-bootstrap" : {
                "datatables.net": "datatables"
            }
        },

        "waitSeconds": 60
    };
    if (window.dcp.ws) {
        config.urlArgs = "ws=" + window.dcp.ws;
    }
    require.config(config);
})();
