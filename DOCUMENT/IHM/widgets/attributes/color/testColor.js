/*global require*/
require([
    'dcpDocument/widgets/attributes/color/loaderColor',
    'dcpDocument/widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    defaultTestSuite("color : read", widget, {}, {value : "#09E316", displayValue : "#09E316"});
    defaultTestSuite("color : write", widget, {mode : "write"}, {value : "#ee6510", displayValue : "#ee6510"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});