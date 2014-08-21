/*global require*/
require([
    'widgets/attributes/docid/loaderDocid',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("docid : read", widget, {}, {value : "212", displayValue : "toto"});
    defaultTestSuite("docid : write", widget, {mode : "write"}, {value : "212", displayValue : "toto"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});