/*global require*/

(function ()
{
    "use strict";
    var config = {
        baseUrl: "..",
        shim: {
            "kendo-culture-fr": ['kendo'],
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
            "mustache": "../uiAssets/externals/mustache/mustache",
            "tooltip": "../uiAssets/externals/tooltip/tooltip",
            "kendo": "../uiAssets/externals/KendoUI/KendoUI",
            "kendo-culture-fr": "../uiAssets/externals/KendoUI/cultures/kendo.culture.fr-FR",
            "ckeditor": "../uiAssets/externals/ckeditor/ckeditor",
            "ckeditor-jquery": "../uiAssets/externals/ckeditor/adapters/jquery",
            "datatables": "../uiAssets/externals/datatables/jquery.dataTables"
        },
        map: {
            'kendo': {
                'jQuery': 'jquery'
            }
        },
        "waitSeconds": 60
    };
    if (window.dcp.ws) {
        config.urlArgs = "ws=" + window.dcp.ws;
    }
    require.config(config);
})();
