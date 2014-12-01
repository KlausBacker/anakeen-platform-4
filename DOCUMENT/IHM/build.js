({
    shim : {
        "bootstrap" :       ['jquery'],
        "kendo" :           ['jquery'],
        "kendo-culture" :   ['kendo'],
        "ckeditor-jquery" : ['jquery', 'ckeditor']
    },
    baseUrl : "../",
    paths :   {
        "widgets" :          "IHM/widgets",
        "collections" :      "IHM/collections",
        "models" :           "IHM/models",
        "views" :            "IHM/views",
        "routers" :          "IHM/routers",
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