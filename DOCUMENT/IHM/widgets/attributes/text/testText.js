/*global require*/
require([
    'dcpDocument/widgets/attributes/text/loaderText',
    'dcpDocument/widgets/attributes/suiteDefaultTestAttribute',
    'dcpDocument/widgets/attributes/text/textTestAttribute'
], function (widget, defaultTestSuite, textTestSuite) {
    "use strict";

    defaultTestSuite("text : read", widget, {}, {value : "Éric <strong>Brison</strong>"});
    defaultTestSuite("text : write", widget, {mode : "write"}, {value : "Charles Bonnissent"});
    textTestSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            placeHolder:"Hello <big>"
        }

    }, {value : "John Doe"},{value:"John Doe"});

    textTestSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            placeHolder:null,
            maxLength : 10
        }
    }, {value : "John Doe the anonymous guy"},{value:"John Doe the anonymous guy"});

    textTestSuite("text : read", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:'Before {{displayValue}} After'
        }
    }, {value : "John Doe the anonymous guy"},{formatValue:"Before John Doe the anonymous guy After"});

    textTestSuite("text : read", widget, {
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