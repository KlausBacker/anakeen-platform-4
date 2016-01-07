/*global require*/
require([
    'dcpDocument/widgets/attributes/time/loaderTime',
    'dcpDocument/widgets/attributes/suiteDefaultTestAttribute',
    'dcpDocument/widgets/attributes/date/dateTestAttribute',
    'kendo-culture-fr'
], function (widget, defaultTestSuite, dateTestSuite, kendo) {
    "use strict";

    kendo.culture("fr-FR");
    defaultTestSuite("time : read", widget, {}, {value : "03:00"});
    defaultTestSuite("time : write", widget, {mode : "write"}, {value : "13:33"});


    dateTestSuite("time : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {

        }
    }, {value: '12:03:45'}, {formatValue: "12:03:45"});

    dateTestSuite("time : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            format:"<b>{{value}}</b>"
        }
    }, {value: '12:03:45'}, {formatValue: "<b>12:03:45</b>"});


    dateTestSuite("time : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            kendoTimeConfiguration: {
                format: "H"
            }
        }
    }, {value: '12:03:45'}, {formatValue: "12"});

    dateTestSuite("time : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            format:"<b>{{displayValue}}</b>",
            kendoTimeConfiguration: {
                format: "H"
            }
        }
    }, {value: '12:03:45'}, {formatValue: "<b>12</b>"});


    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});