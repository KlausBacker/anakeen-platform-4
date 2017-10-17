({
    baseUrl: "../",
    paths: {
        "dcpDocument": "IHM",
        //use empty: to indicate deps that cannot/should not be concatened/minified
        "dcpDocument/i18n": "IHM/i18n",
        "dcpDocument/i18n/documentCatalog": "empty:",
        "jquery": "empty:",
        "text": "empty:",
        "underscore": "empty:",
        "backbone": "empty:",
        "mustache": "empty:",
        "tooltip": "empty:",
        "kendo": "empty:",
        "kendo-culture-fr": "../externals/KendoUI/cultures/kendo.culture.fr-FR",
        "ckeditor": "empty:",
        "datatables": "empty:"
    },
    generateSourceMaps: true,
    inlineText: true,
    preserveLicenseComments: false,
    optimize: "uglify2",
    name: "IHM/main",
    mainConfigFile: "main.js",
    out: "main-built.js"
})
