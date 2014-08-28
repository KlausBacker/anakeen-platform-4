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

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});