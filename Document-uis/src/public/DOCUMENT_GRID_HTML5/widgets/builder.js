//noinspection BadExpressionStatementJS
({
    baseUrl: "../",
    paths :         {
        "dcpDocGrid" :           "widgets/",
        "dcpDocument/widgets" :      "empty:",
        "jquery" :               "empty:",
        "kendo/jquery" :         "empty:",
        "underscore" :           "empty:",
        "kendo" :                "empty:",
        "datatables" :           "empty:",
        "datatables-bootstrap" : "empty:"
    },
    //Inlines the text for any text! dependencies, to avoid the separate
    //async XMLHttpRequest calls to load those dependencies.
    inlineText: true,
    generateSourceMaps: true,
    preserveLicenseComments: false,
    optimize: "uglify2",
    name: "widgets/documentGrid",
    out: "documentGrid-built.js"
})