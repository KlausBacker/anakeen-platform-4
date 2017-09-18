//noinspection BadExpressionStatementJS
({
    shim: {
        "bootstrap": ['jquery'],
        "ckeditor-jquery": ['jquery', 'ckeditor']
    },
    baseUrl: "../../",
    paths: {
        "dcpDocument": "IHM",
        //use empty: to indicate deps that cannot/should not be concatened/minified
        "dcpDocument/i18n/documentCatalog": "IHM/i18n/documentCatalog",
        "jquery": "../externals/jquery/jquery",
        "underscore": "../externals/underscore/underscore",
        "backbone": "../externals/backbone/backbone",
        "mustache": "../externals/mustache/mustache",
        "bootstrap": "../externals/bootstrap/js/bootstrap",
        "tooltip": "empty:",
        "kendo": "../externals/KendoUI/js",
        "kendo-culture-fr": "../externals/KendoUI/js/cultures/kendo.culture.fr-FR",
        "ckeditor": "../externals/ckeditor/4/ckeditor",
        "ckeditor-jquery": "../externals/ckeditor/4/adpaters/jquery",
        "datatables.net": "../externals/jquery-dataTables/js/dataTables.bootstrap",
        "datatables": "../externals/jquery-dataTables/js/jquery.dataTables"
    },
    generateSourceMaps: true,
    preserveLicenseComments: false,
    wrap: {
        startFile: 'start.js.frag',
        endFile: 'end.js.frag'
    },
    optimize: "none",
    name: "IHM/widgets/mainWidget",
    out: "mainWidget-min.js"
})