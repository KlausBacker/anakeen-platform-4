/*global require*/
require([
    'widgets/attributes/money/loaderMoney',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("money : read", widget, {}, {value : 123.32});
    defaultTestSuite("money : write", widget, {mode : "write"}, {value : 456.65});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});