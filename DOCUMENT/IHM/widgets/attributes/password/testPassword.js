/*global require*/
require([
    'widgets/attributes/password/loaderPassword',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("password : read", widget, {}, {value : "secret"});
    defaultTestSuite("password : write", widget, {mode : "write"}, {value : "James Bond"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});