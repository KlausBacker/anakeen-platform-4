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
        "dcpDocument/i18n/documentCatalog": "empty:",
        "jquery": "empty:",
        "underscore": "empty:",
        "backbone": "empty:",
        "mustache": "empty:",
        "bootstrap": "empty:",
        "tooltip": "empty:",
        "kendo": "empty:",
        "kendo-culture-fr": "empty:",
        "ckeditor": "empty:",
        "ckeditor-jquery": "empty:",
        "datatables.net": "empty:",
        "datatables": "empty:"
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