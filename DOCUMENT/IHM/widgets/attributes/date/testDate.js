/*global require*/
require([
    'widgets/attributes/date/loaderDate',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/date/dateTestAttribute',
    'kendo-culture-fr'
], function (widget, defaultTestSuite, dateTestSuite, kendo) {
    "use strict";

    kendo.culture("fr-FR");
    defaultTestSuite("date : read", widget, {}, {value: "2012-12-14"});
    defaultTestSuite("date : write", widget, {mode: "write"}, {value: "2012-12-14"});

    dateTestSuite("date : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            kendoDateConfiguration: {
                format: "d"
            }
        }
    }, {value: '2015-02-28'}, {formatValue: "28/02/2015"});

    dateTestSuite("date : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            kendoDateConfiguration: {
                format: "d"
            },
            format: "<strong>{{value}}</strong>"
        }
    }, {value: '2015-02-28'}, {formatValue: "<strong>2015-02-28</strong>"});

    dateTestSuite("date : spec", widget, {
        mode: "read",
        deleteButton: true,
        renderOptions: {
            kendoDateConfiguration: {
                format: "d"
            },
            format: "<strong>{{displayValue}}</strong>"
        }
    }, {value: '2015-02-28'}, {formatValue: "<strong>28/02/2015</strong>"});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});