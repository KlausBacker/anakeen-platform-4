/*global require, describe, beforeEach, setFixtures, expect, it, sandbox, jasmine, afterEach*/

require([
    'dcpDocument/test/testDocumentController'
], function (testDocumentController)
{
    "use strict";

    testDocumentController();

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});
