/*global require*/
require([
    'widgets/attributes/enum/loaderEnum',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("enum : read", widget, {}, {value : "g", displayValue : "Sol"});
    defaultTestSuite("enum : write", widget, {
            mode : "write",
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "blue", displayValue : ""});

    defaultTestSuite("enum multiple : write", widget, {
            mode : "write",
            options : {
                multiple:"yes"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        [{value : "blue", displayValue : "Si"},{value : "c", displayValue : "Do"}]);

    defaultTestSuite("enum horizontal : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "navyblue", displayValue : "Si"});



    defaultTestSuite("enum horizontal multiple : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            options : {
                multiple:"yes"
            },
            ssourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        [{value : "navyblue", displayValue : "Si"},{value : "c", displayValue : "Do"}]);

    defaultTestSuite("enum vertical : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "blue", displayValue : "Si"});



    defaultTestSuite("enum vertical multiple : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            options : {
                multiple:"yes"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        [{value : "blue", displayValue : "Si"},{value : "red", displayValue : "Do"}]);

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});