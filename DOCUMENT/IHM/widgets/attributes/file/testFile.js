/*global require*/
require([
    'widgets/attributes/file/loaderFile',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("file : read", widget, {}, {value : "212", displayValue : "toto"});
    defaultTestSuite("file : write", widget, {mode : "write"}, {value : "212", displayValue : "toto"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});