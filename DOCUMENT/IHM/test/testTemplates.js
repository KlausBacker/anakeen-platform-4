/*global require*/
require([
    'underscore',
    'dcpDocumentTest/templatesTestAttribute'
], function (_, templateTestSuite) {
    "use strict";


    var tplConfig = [
        {
            title: "%type% : read hello",
            attribute: {type: "%type%", label: "%label%"},
            options: {
                renderMode: "view"
            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "<h1>Hello</h1>"
                    }
                }
            },
            initialValue: {
                value: "%value%",
                displayValue: "%displayValue%"
            },
            expectedContent: "<h1>Hello</h1>"
        }, {
            title: "%type% : read all static",
            attribute: {type: "%type%", label: "%label%", "id": "my%type%1"},
            options: {
                renderMode: "view"

            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "{{attribute.id}}<h1>{{attribute.label}}/{{attribute.attributeValue.value}}</h1><h2>{{attribute.attributeValue.displayValue}}</h2>"
                    }
                }
            },
            initialValue: {
                value: "%value%",
                displayValue: "%displayValue%"
            },
            expectedContent: "my%type%1<h1>%%label%%/%%value%%</h1><h2>%%displayValue%%</h2>"
        }, {
            title: "%type% : read extern id",
            attribute: {type: "%type%", label: "%label%", "id": "mytext"},
            options: {
                renderMode: "view"

            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "{{attributes.mytext.id}}<h1>{{attributes.mytext.label}}/{{attributes.mytext.attributeValue.value}}</h1><h2>{{attributes.mytext.attributeValue.displayValue}}</h2>"
                    }
                }
            },
            initialValue: {
                value: "%value%",
                displayValue: "%displayValue%"
            },
            expectedContent: "mytext<h1>%%label%%/%%value%%</h1><h2>%%displayValue%%</h2>"
        }, {
            title: "%type% : write hello",
            attribute: {type: "%type%", label: "%label%"},
            options: {
                renderMode: "edit"
            },
            initialValue: {
                value: "%altValue%",
                displayValue: "%altDisplayValue%"
            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "<h1>Hello Jane</h1>"
                    }
                }
            },
            expectedContent: "<h1>Hello Jane</h1>"
        }, {
            title: "%type% : write displayValue",
            attribute: {type: "%type%", label: "%label%"},
            options: {
                renderMode: "edit"
            },
            initialValue: {
                value: "%altValue%",
                displayValue: "%altDisplayValue%"
            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "<h1>Hello {{attribute.attributeValue.displayValue}}</h1>"
                    }
                }
            },
            expectedContent: "<h1>Hello %%altDisplayValue%%</h1>"
        }, {
            title: "%type% : read htmlContent",
            attribute: {type: "%type%", label: "%label%"},
            options: {
                renderMode: "view"
            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "<div class='myCustom' style='border:dotted 2px red;'>{{{attribute.htmlContent}}}</div>"
                    }
                }
            },
            initialValue: {
                value: "%value%",
                displayValue: "%displayValue%"
            },
            expectedSubContents: [
                {
                    filter: "div.myCustom  > .dcpCustomTemplate--content .dcpAttribute__content__value",
                    htmlValue: "%displayValue%"
                }
            ]
        }, {
            title: "%type% : write htmlContent",
            attribute: {type: "%type%", label: "%label%"},
            options: {
                renderMode: "edit"
            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "<div class='myCustom' style='border:dotted 2px red;'>{{{attribute.htmlContent}}}</div>"
                    }
                }
            },
            initialValue: {
                value: "%value%",
                displayValue: "%displayValue%"
            },
            expectedSubContents: [
                {
                    filter: "div.myCustom  > .dcpCustomTemplate--content .dcpAttribute__value",
                    textValue: null
                }
            ]
        }, {
            title: "%type% : read htmlView",
            attribute: {type: "%type%", label: "Mon super libellé"},
            options: {
                renderMode: "view"
            },
            renderOptions: {
                types: {
                    "%type%": {
                        template: "<div class='myCustom' style='border:dotted 2px red;'>{{{attribute.htmlView}}}</div>"
                    }
                }
            },
            initialValue: {
                value: "%value%",
                displayValue: "%displayValue%"
            },
            expectedSubContents: [
                {
                    filter: "div.myCustom > .dcpCustomTemplate--content .dcpAttribute__content__value",
                    htmlValue: "%displayValue%"
                },

                {
                    filter: "div.myCustom >  .dcpCustomTemplate--content .dcpAttribute__label.dcpLabel",
                    textValue: "Mon super libellé"
                }
            ]
        }
    ];

    var tplData = [
        {
            type: "text", label: "Mon libellé",
            value: "John Doe",
            displayValue: "John Doe l'inconnu",
            altValue: "Jane Doé",
            altDisplayValue: "Jane Doé > l'inconnu"
        }, {
            type: "date",
            label: "Ma date",
            value: "2012-08-23",
            displayValue: "23/08/2012",
            altValue: "1987-09-29",
            altDisplayValue: "29/09/1987"
        },{
            type: "time",
            label: "Mon temps",
            value: "12:00",
            displayValue: "12:00",
            altValue: "14:45",
            altDisplayValue: "14:45"
        },{
            type: "timestamp",
            label: "Mon horodate",
            value: "2012-08-23 12:00",
            displayValue: "23/08/2012 12:00",
            altValue: "1987-09-29 14:59",
            altDisplayValue: "29/09/1987 14:59"
        }, {
            type: "docid",
            label: "Ma relation",
            value: "1009",
            displayValue: "Un document",
            altValue: "3456",
            altDisplayValue: "Un autre document"
        }, {
            type: "account",
            label: "Mon compte",
            value: "1009",
            displayValue: "Un compte",
            altValue: "3456",
            altDisplayValue: "Un autre compte"
        },{
            type: "htmltext",
            label: "Ma rédaction",
            value: "<p><strong>Grosse rédaction</strong>.</p>",
            displayValue: "<p><strong>Grosse rédaction</strong>.</p>",
            altValue: "<p><strong>Grosse rédaction</strong>.</p>",
            altDisplayValue: "<p><strong>Grosse rédaction</strong>.</p>"
        },{
            type: "longtext",
            label: "Ma remarque",
            value: "Remarque:\nPoint n°1",
            displayValue: "Remarque:\nPoint n°1",
            altValue: "Remarque:\nPoint n°1 & Point n°2",
            altDisplayValue: "Remarque:\nPoint n°1 & Point n°2"
        },{
            type: "file",
            label: "Mon fichier",
            value: "image/png|34|test.jpg",
            displayValue: "test.jpg",
            altValue: "application.pdf|34|Information.pdf",
            altDisplayValue: "Information.pdf"
        },{
            type: "image",
            label: "Mon image",
            value: "image/png|34|test.jpg",
            displayValue: "test.jpg",
            altValue: "image/jpeg|34|Information.jpeg",
            altDisplayValue: "Information.jpeg"
        },{
            type: "int",
            label: "Mon entier",
            value: "1234",
            displayValue: "1234",
            altValue: "-67890",
            altDisplayValue: "-67890"
        },{
            type: "double",
            label: "Mon décimal",
            value: "1234.567",
            displayValue: "1234.567",
            altValue: "-67890.098",
            altDisplayValue: "-67890.098"
        },{
            type: "money",
            label: "Mes sous",
            value: "1234.56",
            displayValue: "1234.56",
            altValue: "-67890.09",
            altDisplayValue: "-67890.09"
        },{
            type: "color",
            label: "Ma couleur",
            value: "#00ff55",
            displayValue: "#00ff55",
            altValue: "#00ff55",
            altDisplayValue: "#00ff55"
        },{
            type: "enum",
            label: "Mon enum",
            value: "A",
            displayValue: "la",
            altValue: "1",
            altDisplayValue: "Un"
        }
    ];

    function replaceEverywhere(obj, data) {
        var modObject;
         if (_.isString(obj)) {
            modObject = obj;
            _.map(data, function (val, idx) {
                modObject = modObject.replace('%%' + idx + '%%', $('<div/>').text(val).html(), "g");
                modObject = modObject.replace('%' + idx + '%', val, "g");
            });
        } else if (_.isObject(obj)) {
            modObject = {};
            _.map(obj, function (val, idx) {
                modObject[replaceEverywhere(idx, data)] = replaceEverywhere(obj[idx], data);
            });
        } else {
            return obj;
        }
        return modObject;
    }




    _.each(tplConfig, function (testConf) {
        _.each(tplData, function (data) {
            templateTestSuite(replaceEverywhere(testConf, data));
        });


    });


    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});