/*global require*/
require([
    'widgets/attributes/image/loaderImage',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("image : read", widget, {}, {value : "212", displayValue : "toto"});
    defaultTestSuite("image : write", widget, {mode : "write"}, {value : "212", displayValue : "toto"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});