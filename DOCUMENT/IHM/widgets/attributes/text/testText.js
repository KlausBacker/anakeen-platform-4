/*global require*/
require([
    'widgets/attributes/text/loaderText',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("text : read", widget, {}, {value : "Éric <strong>Brison</strong>"});
    defaultTestSuite("text : write", widget, {mode : "write"}, {value : "Charles Bonnissent"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});