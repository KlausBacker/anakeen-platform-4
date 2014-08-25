/*global require*/
require([
    'widgets/attributes/date/loaderDate',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("date : read", widget, {}, {value : "2012-12-14"});
    defaultTestSuite("date : write", widget, {mode : "write"}, {value : "2012-12-14"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});