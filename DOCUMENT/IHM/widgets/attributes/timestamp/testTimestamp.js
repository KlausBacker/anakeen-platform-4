/*global require*/
require([
    'dcpDocument/widgets/attributes/timestamp/loaderTimestamp',
    'dcpDocument/widgets/attributes/suiteDefaultTestAttribute',
    'dcpDocument/widgets/attributes/date/dateTestAttribute',
    'kendo-culture-fr'
], function (widget, defaultTestSuite, dateTestSuite, kendo) {
    "use strict";

    kendo.culture("fr-FR");
    defaultTestSuite("timestamp : read", widget, {}, {value: "1985-05-12 18:00:05"});
    defaultTestSuite("timestamp : write", widget, {mode: "write"}, {value: "1985-05-12 18:00:05"});

    dateTestSuite("timestamp : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            kendoDateConfiguration: {
                format: "g"
            }
        }
    }, {value: '2015-02-28T12:05:00'}, {formatValue: "28/02/2015 12:05"});

    dateTestSuite("timestamp : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            kendoDateConfiguration: {
                format: "g"
            }
        }
    }, {value: '2015-02-28 12:05'}, {value:"2015-02-28T12:05", formatValue: "28/02/2015 12:05"});

    dateTestSuite("timestamp : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            format: "<b>{{displayValue}}</b>",
            kendoDateConfiguration: {
                format: "F"
            }
        }
    }, {value: '2015-02-28 12:05:10'}, {value: '2015-02-28T12:05:10',formatValue: "<b>samedi 28 février 2015 12:05:10</b>"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});