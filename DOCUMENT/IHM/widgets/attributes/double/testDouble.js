/*global require*/
require([
    'widgets/attributes/double/loaderDouble',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/int/intTestAttribute',
    'widgets/attributes/double/doubleTestAttribute',
    'kendo-culture-fr'
], function (widget, defaultTestSuite, intTestSuite, doubleTestSuite, kendo) {
    "use strict";

    defaultTestSuite("double : read", widget, {}, {value : 3.14});
    defaultTestSuite("double : write", widget, {mode : "write"}, {value : -202.0912354});


    kendo.culture("fr-FR");
    intTestSuite("double : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{value}}</h1>"
        }

    }, {value : 12},{formatValue:"<h1>12</h1>"});

    intTestSuite("double : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
        }
    }, {value : 12.3456789},{formatValue:"12,3456789"});

    intTestSuite("double : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            decimalPrecision:4
        }
    }, {value : 8912.3456789},{formatValue:"8&nbsp;912,3457"});


    intTestSuite("double : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{value}}</h1>"
        }

    }, {value : 1234.56},{formatValue:"<h1>1234.56</h1>"});

    intTestSuite("double : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{displayValue}}</h1>"
        }

    }, {value : 1234.567},{formatValue:"<h1>1&nbsp;234,567</h1>"});


    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});