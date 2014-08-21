/*global require*/
require([
    'widgets/attributes/longtext/loaderLongtext',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("longtext : read", widget, {}, {value : "Lorem Ispum"});
    defaultTestSuite("longtext : write", widget, {mode : "write"}, {value : "Lorem Ispum"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});