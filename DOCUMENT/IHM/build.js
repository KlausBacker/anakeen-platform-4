({
    shim : {
        "bootstrap" :       ['jquery'],
        "kendo" :           ['jquery'],
        "kendo-culture" :   ['kendo'],
        "ckeditor-jquery" : ['jquery', 'ckeditor']
    },
    baseUrl : "../",
    paths :   {
        "dcpDocument": "IHM",
        "text" :                 'empty:',
        "jquery" :               "empty:",
        "underscore" :           "empty:",
        "backbone" :             "empty:",
        "mustache" :             "empty:",
        "bootstrap" :            "empty:",
        "kendo" :                "empty:",
        "kendo-culture-fr" :     "empty:",
        "ckeditor" :             "empty:",
        "ckeditor-jquery" :      "empty:",
        "datatables" :           "empty:",
        "datatables-bootstrap" : "empty:"
    },
    generateSourceMaps : true,
    preserveLicenseComments : false,
    optimize : "uglify2",
    name :    "IHM/main",
    out :     "main-built.js"
})