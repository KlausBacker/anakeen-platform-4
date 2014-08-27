/*global require*/
require([
    'widgets/attributes/money/loaderMoney',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("money : read", widget, {}, {value : "Lorem Ispum"});
    defaultTestSuite("money : write", widget, {mode : "write"}, {value : "Lorem Ispum"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});