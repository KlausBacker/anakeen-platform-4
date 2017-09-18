/*global require, describe, beforeEach, setFixtures, expect, it, sandbox, jasmine, afterEach*/

require([
    'dcpDocument/test/suiteDocumentController'
], function require_testDocumentController(testDocumentController)
{
    "use strict";

    testDocumentController({"noFixture": true, "name" : "consultation"}, {});
    testDocumentController({"noFixture": true, "name" : "edition"}, { viewId: "!defaultEdition" });

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});
