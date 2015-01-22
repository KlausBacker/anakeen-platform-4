/*global require*/
require([
    'widgets/attributes/timestamp/loaderTimestamp',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/date/dateTestAttribute',
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
    }, {value: '2015-02-28 12:05:00'}, {formatValue: "28/02/2015 12:05"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});