/*global require*/
require([
    'widgets/attributes/longtext/loaderLongtext',
    'widgets/attributes/defaultTestAttribute',
    'widgets/attributes/text/textTestAttribute',
    'widgets/attributes/longtext/longtextTestAttribute'
], function (widget, defaultTestSuite, textTestSuite, longtextTestSuite) {
    "use strict";

    defaultTestSuite("longtext : read", widget, {}, {value : "C'est aussi un auteur qui a abordé pratiquement tous les sujets : biologie, physique, métaphysique, logique, poétique, politique, rhétorique et de façon très ponctuelle l'économie"});
    defaultTestSuite("longtext : write", widget, {mode : "write"}, {value : "C'est aussi un auteur qui a abordé pratiquement tous les sujets : biologie, physique, métaphysique, logique, poétique, politique, rhétorique et de façon très ponctuelle l'économie"});

    textTestSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            placeHolder:"Hello\nThis is a <big> text."
        }

    }, {value : "Aristote, \nsurnommé le Stagirite"},{value:"Aristote, \nsurnommé le Stagirite"});


    textTestSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            placeHolder:null,
            maxLength : 10
        }
    }, {value : "Limit : \nUp to 10 characters"},{value:"Limit : \nUp to 10 characters"});

    textTestSuite("text : read", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:'Before {{displayValue}} After'
        }
    }, {value : "John Doe : \nThe anonymous guy"},{formatValue:"Before John Doe : \nThe anonymous guy After"});

    textTestSuite("text : read", widget, {
        mode : "read",
        deleteButton : true,
        renderOptions : {
            format:'<strong>{{displayValue}}</strong> : <em>{{value}}</em>'
        }
    }, {value : "Thé\nCafé"},{formatValue:"<strong>Thé\nCafé</strong> : <em>Thé\nCafé</em>"});



    longtextTestSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            displayedLineNumber : 0,
            placeHolder:"Hello\nThis is a <big> text."
        }

    }, {value : "<one>\n<two>\n<three>\n<four>\n<five>\n<six>"},{minHeight:100});

    longtextTestSuite("text : write", widget, {
        mode : "write",
        deleteButton : true,
        renderOptions : {
            displayedLineNumber : 2,
            placeHolder:"Hello\nThis is a <big> text."
        }

    }, {value : "<one>\n<two>\n<three>\n<four>\n<five>\n<six>"},{maxHeight:40});



    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});