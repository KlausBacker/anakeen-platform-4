/*global require*/
require([
    'widgets/attributes/time/loaderTime',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("timestamp : read", widget, {}, {value : "1985-05-12 18:00:05"});
    defaultTestSuite("timestamp : write", widget, {mode : "write"}, {value : "1985-05-12 18:00:05"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});