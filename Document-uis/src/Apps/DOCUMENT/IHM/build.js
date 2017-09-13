({
    shim: {
        "bootstrap": ['jquery'],
        "kendo": ['jquery'],
        "kendo-culture": ['kendo'],
        "ckeditor-jquery": ['jquery', 'ckeditor']
    },
    baseUrl: "../",
    paths: {
        "dcpDocument": "IHM",
        //use empty: to indicate deps that cannot/should not be concatened/minified
        "dcpDocument/i18n": "IHM/i18n",
        "dcpDocument/i18n/documentCatalog": "IHM/i18n/documentCatalog",
        "jquery": "../externals/jquery/jquery",
        "text": "../externals/RequireJS/text",
        "underscore": "../externals/underscore/underscore",
        "backbone": "../externals/backbone/backbone",
        "mustache": "../externals/mustache/mustache",
        "bootstrap": "../externals/bootstrap/js/bootstrap",
        "tooltip": "empty:",
        "kendo": "../externals/KendoUI/js",
        "kendo-ddui": "../externals/KendoUI/js/kendo-ddui-builded",
        "kendo-culture-fr": "../externals/KendoUI/js/cultures/kendo.culture.fr-FR",
        "ckeditor": "../externals/ckeditor/4/ckeditor",
        "ckeditor-jquery": "../externals/ckeditor/4/adapters/jquery",
        "datatables": "../externals/jquery-dataTables/js/jquery.dataTables",
        "datatables.net": "../externals/jquery-dataTables/js/dataTables.bootstrap",
        "es6-promise" : "../externals/es6-promise/es6-promise",
        "dcpDocument/libs/promise" : "IHM/libs/promise"
    },
    generateSourceMaps: true,
    inlineText: true,
    preserveLicenseComments: false,
    optimize: "uglify2",
    name: "IHM/main",
    mainConfigFile: "main.js",
    out: "main-built.js"
})