/*global require*/
require([
    'dcpDocument/widgets/attributes/htmltext/loaderHtmltext',
    'dcpDocument/widgets/attributes/defaultTestAttribute',
    'dcpDocument/widgets/attributes/htmltext/htmltextTestDestroy'
], function (widget, defaultTestSuite,  htmltextTestDestroy) {
    "use strict";

    /*
    htmltextTestDestroy("htmltext : spec",  widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            height:"200px",
            toolbar : "Basic"
        }
    }, {value : "<h1>TO DESTROY</h1>"},{});
*/
    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});