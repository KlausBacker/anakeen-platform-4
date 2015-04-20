/*global require*/
require([
    'dcpDocument/widgets/attributes/enum/loaderEnum',
    'dcpDocument/widgets/attributes/suiteDefaultTestAttribute',
    'dcpDocument/widgets/attributes/enum/enumTestAttribute'
], function (widget, defaultTestSuite, enumTestSuite) {
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
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
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


    enumTestSuite("enum vertical spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            options : {
                multiple:"no"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "blue", displayValue : "Bleu"});

    enumTestSuite("enum vertical spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            options : {
                multiple:"no"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "nothing", displayValue : "Nothing"});

    enumTestSuite("enum horizontal spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            options : {
                multiple:"no"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "blue", displayValue : "Bleu"});

    enumTestSuite("enum horizontal spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            options : {
                multiple:"no"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"navyblue", label:"Bleu Marine"}]
        },
        {value : "nothing", displayValue : "Nothing"});


    enumTestSuite("enum bool spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"bool"
            },
            options : {
                multiple:"no"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"}]
        },
        {value : "blue", displayValue : "Bleu"});


    enumTestSuite("enum vertical multiple spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            options : {
                multiple:"yes"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"green", label:"Vert"},{key:"navyblue", label:"Bleu Marine"}]
        },
        [{value : "red", displayValue : "Rouge"},{value:"green"}]);

    enumTestSuite("enum horizontal multiple spec : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            options : {
                multiple:"yes"
            },
            sourceValues:[{key:"red", label:"Rouge"},{key:"blue", label:"Bleu"},{key:"green", label:"Vert"},{key:"navyblue", label:"Bleu Marine"}]
        },
        [{value : "red", displayValue : "Rouge"},{value:"green"}]);

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});