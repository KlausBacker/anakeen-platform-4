/*global require*/
require([
    'widgets/attributes/double/loaderDouble',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("double : read", widget, {}, {value : 3.14});
    defaultTestSuite("double : write", widget, {mode : "write"}, {value : -202.0912354});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});