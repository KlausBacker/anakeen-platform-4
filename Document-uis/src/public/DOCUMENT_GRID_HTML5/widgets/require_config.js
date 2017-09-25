/*global require*/

(function () {
    "use strict";
    var config = {
        baseUrl :       "DOCUMENT_GRID_HTML5/",
        // The shim config allows us to configure dependencies for
        // scripts that do not call define() to register a module
        shim :          {
            "kendo" :           ['jquery'],
            "kendo-culture" :   ['kendo']
        },
        paths :         {
            "dcpDocGrid" :           "widgets/",
            "document/widget":       "../DOCUMENT/IHM/widgets/widget",
            "jquery" :               "../uiAssets/externals/KendoUI/js/jquery.min",
            "kendo/jquery" :         "../uiAssets/externals/KendoUI/js/jquery.min",
            "underscore" :           "../uiAssets/externals/underscore/underscore.min",
            "kendo" :                "../uiAssets/externals/KendoUI/js/",
            "dcpDocument":          "../DOCUMENT/IHM",
            "datatables" :           "../uiAssets/externals/jquery-dataTables/js/jquery.dataTables.min",
            "datatables.net" :       "../uiAssets/externals/jquery-dataTables/js/jquery.dataTables.min",
            "datatables-bootstrap" : "../uiAssets/externals/jquery-dataTables/js/dataTables.bootstrap.min",
            "mustache":              "../uiAssets/externals/mustache.js/mustache.min"
        },
        "waitSeconds" : 60
    };
    if (window.dcp && window.dcp.ws) {
        config.urlArgs = "ws=" + window.dcp.ws;
    }
    require.config(config);
})();
