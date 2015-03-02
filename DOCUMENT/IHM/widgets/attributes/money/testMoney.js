/*global require*/
require([
    'dcpDocument/widgets/attributes/money/loaderMoney',
    'dcpDocument/widgets/attributes/defaultTestAttribute',
    'dcpDocument/widgets/attributes/int/intTestAttribute',
    'dcpDocument/widgets/attributes/double/doubleTestAttribute',
    'dcpDocument/widgets/attributes/money/moneyTestAttribute',
    'kendo-culture-fr'
], function (widget, defaultTestSuite, intTestSuite, doubleTestSuite, moneyTestSuite, kendo) {
    "use strict";

    defaultTestSuite("money : read", widget, {}, {value : 123.32});
    defaultTestSuite("money : write", widget, {mode : "write"}, {value : 456.65});

    kendo.culture("fr-FR");
    intTestSuite("money : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:"<h1>{{value}}</h1>"
        }

    }, {value : 12},{formatValue:"<h1>12</h1>"});

    intTestSuite("money : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
        }
    }, {value : 200},{formatValue:"200,00 €"});

    intTestSuite("money : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
        }
    }, {value : 12.3456},{formatValue:"12,35 €"});

    intTestSuite("money : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
        }
    }, {value : 8912.345},{formatValue:"8&nbsp;912,35 €"});

    intTestSuite("money : spec", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            currency:'£'
        }
    }, {value : 8912.3446},{formatValue:"8&nbsp;912,34 £"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});