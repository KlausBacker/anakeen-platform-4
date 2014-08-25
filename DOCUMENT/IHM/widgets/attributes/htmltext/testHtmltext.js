/*global require*/
require([
    'widgets/attributes/htmltext/loaderHtmltext',
    'widgets/attributes/defaultTestAttribute'
], function (widget, defaultTestSuite) {
    "use strict";

    //defaultTestSuite("htmltext : read", widget, {}, {value : "Lorem <strong>Ispum</strong>"});
    //defaultTestSuite("htmltext : write", widget, {mode : "write"}, {value : "Lorem <strong>Ispum</strong>"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});