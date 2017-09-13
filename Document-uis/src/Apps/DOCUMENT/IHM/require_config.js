/*global require*/

(function ()
{
    "use strict";
    var config = {
        baseUrl: "DOCUMENT/",
        // The shim config allows us to configure dependencies for
        // scripts that do not call define() to register a module
        shim: {
            "tooltip": ['jquery'],
            "ckeditor-jquery": ['jquery', 'ckeditor']
        },
        paths: {
            "dcpContextRoot": "..",
            "dcpDocument": "IHM",
            "dcpDocumentTest": "IHM/test"
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
