({
    shim :    {
        "bootstrap" :       [ 'jquery' ],
        "kendo" :           [ 'jquery' ],
        "kendo-culture" :   [ 'kendo/kendo.core' ],
        "ckeditor-jquery" : [ 'jquery', 'ckeditor' ]
    },
    baseUrl : "../",
    paths :   {
        "widgets" :          "IHM/widgets",
        "collections" :      "IHM/collections",
        "models" :           "IHM/models",
        "views" :            "IHM/views",
        "routers" :          "IHM/routers",
        "jquery" :           "/home/charles/vagrants/doc2/lib/KendoUI/js/jquery",
        "underscore" :       "/home/charles/vagrants/doc2/lib/underscore/underscore",
        "backbone" :         "/home/charles/vagrants/doc2/lib/backbone/backbone",
        "mustache" :         "/home/charles/vagrants/doc2/lib/mustache.js/mustache",
        "bootstrap" :        "/home/charles/vagrants/doc2/lib/bootstrap/js/bootstrap",
        "kendo" :            "/home/charles/vagrants/doc2/lib/KendoUI/js/kendo.ui.core",
        "kendo-culture-fr" : "/home/charles/vagrants/doc2/lib/KendoUI/js/cultures/kendo.culture.fr-FR",
        "ckeditor" :         "/home/charles/vagrants/doc2/lib/ckeditor/ckeditor",
        "ckeditor-jquery" :  "/home/charles/vagrants/doc2/lib/ckeditor/adapters/jquery"
    },
    name :    "IHM/main",
    out :     "main-built.js"
})