/*global require*/
require([
    'dcpDocument/widgets/attributes/password/loaderPassword',
    'dcpDocument/widgets/attributes/suiteDefaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("password : read", widget, {}, {value : "secret"});
    defaultTestSuite("password : write", widget, {mode : "write"}, {value : "James Bond"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});