/*global require*/
require([
    'widgets/attributes/time/loaderTime',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("time : read", widget, {}, {value : "03:00"});
    defaultTestSuite("time : write", widget, {mode : "write"}, {value : "13:33"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});