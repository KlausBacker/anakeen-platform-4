/*global require*/
require([
    'widgets/attributes/enum/loaderEnum',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("enum : read", widget, {}, {value : "212", displayValue : "toto"});
    defaultTestSuite("enum : write", widget, {mode : "write"}, {value : "212", displayValue : "toto"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});