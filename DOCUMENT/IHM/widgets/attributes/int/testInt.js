/*global require*/
require([
    'dcpDocument/widgets/attributes/int/loaderInt',
    'dcpDocument/widgets/attributes/defaultTestAttribute',
    'dcpDocument/widgets/attributes/int/intTestAttribute',
    'kendo-culture-fr'
], function (widget, defaultTestSuite, intTestSuite, kendo) {
    "use strict";

    defaultTestSuite("int : read", widget, {}, {value : 10});
    defaultTestSuite("int : write", widget, {mode : "write"}, {value : 10});
    kendo.culture("fr-FR");
    intTestSuite("int : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{value}}</h1>"
        }

    }, {value : 12},{formatValue:"<h1>12</h1>"});

    intTestSuite("int : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {

        }
    }, {value : 123456},{formatValue:"123&nbsp;456"});

    intTestSuite("int : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{value}}</h1>"
        }

    }, {value : 1234},{formatValue:"<h1>1234</h1>"});

    intTestSuite("int : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{displayValue}}</h1>"
        }

    }, {value : 1234},{formatValue:"<h1>1&nbsp;234</h1>"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});