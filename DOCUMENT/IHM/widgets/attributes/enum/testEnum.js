/*global require*/
require([
    'widgets/attributes/enum/loaderEnum',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("enum : read", widget, {}, {value : "g", displayValue : "Sol"});
    defaultTestSuite("enum : write", widget, {
            mode : "write",
            sourceValues:{a:"La",b:"Si","bb":"Sib"}
        },
        {value : "b", displayValue : "Si"});

    defaultTestSuite("enum multiple : write", widget, {
            mode : "write",
            options : {
                multiple:"yes"
            },
            sourceValues:{a:"La",b:"Si","bb":"Sib","c":"Do"}
        },
        [{value : "b", displayValue : "Si"},{value : "c", displayValue : "Do"}]);

    defaultTestSuite("enum horizontal : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            sourceValues:{a:"La",b:"Si","bb":"Sib"}
        },
        {value : "b", displayValue : "Si"});



    defaultTestSuite("enum horizontal multiple : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"horizontal"
            },
            options : {
                multiple:"yes"
            },
            sourceValues:{a:"La",b:"Si","bb":"Sib","c":"Do"}
        },
        [{value : "b", displayValue : "Si"},{value : "c", displayValue : "Do"}]);

    defaultTestSuite("enum vertical : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            sourceValues:{a:"La",b:"Si","bb":"Sib"}
        },
        {value : "b", displayValue : "Si"});



    defaultTestSuite("enum vertical multiple : write", widget, {
            mode : "write",
            renderOptions : {
                editDisplay:"vertical"
            },
            options : {
                multiple:"yes"
            },
            sourceValues:{a:"La",b:"Si","bb":"Sib","c":"Do"}
        },
        [{value : "b", displayValue : "Si"},{value : "c", displayValue : "Do"}]);

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});