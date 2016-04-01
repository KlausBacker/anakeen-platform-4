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
        "dcpDocument/i18n": "empty:",
        "dcpDocument/i18n/documentCatalog": "empty:",
        "jquery": "empty:",
        "underscore": "empty:",
        "backbone": "empty:",
        "mustache": "empty:",
        "bootstrap": "empty:",
        "tooltip": "empty:",
        "kendo": "empty:",
        "kendo-ddui": "empty:",
        "kendo-culture-fr": "empty:",
        "ckeditor": "empty:",
        "ckeditor-jquery": "empty:",
        "datatables": "empty:",
        "datatables-bootstrap": "empty:",
        "es6-promise" : "empty:",
        "dcpDocument/libs/promise" : "empty:"
    },
    generateSourceMaps: true,
    preserveLicenseComments: false,
    optimize: "uglify2",
    name: "IHM/main",
    out: "main-built.js"
})