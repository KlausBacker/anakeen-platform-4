/*global require*/
require([
    'widgets/attributes/int/loaderInt',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("int : read", widget, {}, {value : 10});
    defaultTestSuite("int : write", widget, {mode : "write"}, {value : 10});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});