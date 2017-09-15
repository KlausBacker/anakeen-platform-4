/*global require*/

(function ()
{
    "use strict";
    var config = {
        baseUrl: "..",
        // The shim config allows us to configure dependencies for
        // scripts that do not call define() to register a module
        shim: {
            "tooltip": ['jquery'],
            "ckeditor-jquery": ['jquery', 'ckeditor']
        },
        paths: {
            "dcpContextRoot": "..",
            "dcpDocument": "../uiAssets/anakeen/IHM",
            "dcpDocumentTest": "../uiAssets/IHM/test",
            "jquery": "../uiAssets/externals/jquery/jquery",
            "text": "../uiAssets/externals/RequireJS/text",
            "underscore": "../uiAssets/externals/underscore/underscore",
            "backbone": "../uiAssets/externals/backbone/backbone",
            "mustache": "../uiAssets/externals/mustache.js/mustache",
            "bootstrap": "../uiAssets/externals/bootstrap/js/bootstrap",
            "tooltip": "../uiAssets/externals/bootstrap/js/tooltip",
//          "dcpContextRoot": "empty:",
            "kendo": "../uiAssets/externals/KendoUI/js",
            "kendo-ddui": "../uiAssets/externals/KendoUI/js/kendo-ddui-builded",
            "kendo-culture-fr": "../uiAssets/externals/KendoUI/js/cultures/kendo.culture.fr-FR",
            "ckeditor": "../uiAssets/externals/ckeditor/4/ckeditor",
            "ckeditor-jquery": "../uiAssets/externals/ckeditor/4/adapters/jquery",
            "datatables": "../uiAssets/externals/jquery-dataTables/js/jquery.dataTables",
            "datatables.net": "../uiAssets/uiAssets/externals/jquery-dataTables/js/dataTables.bootstrap",
            "es6-promise" : "../uiAssets/externals/es6-promise/es6-promise",
            "dcpDocument/libs/promise" : "uiAssets/anakeen/IHM/libs/promise"
        },
        map: {

        },
        "waitSeconds": 60
    };
    if (window.dcp.ws) {
        config.urlArgs = "ws=" + window.dcp.ws;
    }
    require.config(config);
})();
