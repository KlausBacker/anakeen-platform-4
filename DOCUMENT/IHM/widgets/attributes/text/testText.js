/*global require*/
require([
    'widgets/attributes/text/loaderText',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/text/textTestAttribute'
], function (widget, defaultTestSuite, textTextSuite) {
    "use strict";

    defaultTestSuite("text : read", widget, {}, {value : "Éric <strong>Brison</strong>"});
    defaultTestSuite("text : write", widget, {mode : "write"}, {value : "Charles Bonnissent"});
    textTextSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            placeHolder:"Hello <big>"
        }

    }, {value : "John Doe"},{value:"John Doe"});

    textTextSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            placeHolder:null,
            maxLength : 10
        }
    }, {value : "John Doe the anonymous guy"},{value:"John Doe the anonymous guy"});

    textTextSuite("text : read", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:'Before {{displayValue}} After'
        }
    }, {value : "John Doe the anonymous guy"},{formatValue:"Before John Doe the anonymous guy After"});

    textTextSuite("text : read", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:'<strong>{{displayValue}}</strong> : <em>{{value}}</em>'
        }
    }, {value : "John Doe"},{formatValue:"<strong>John Doe</strong> : <em>John Doe</em>"});


    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});