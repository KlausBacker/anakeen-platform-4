/*global require*/
require([
    'dcpDocumentTest/templatesTestAttribute'
], function (defaultTestSuite) {
    "use strict";

    defaultTestSuite({
        title: "text : read",
        attribute: {type: "text", label:"Mon libellé"},
        options: {
            renderMode: "view"

        },
        renderOptions:{
            types: {
                "text" : {
                    template:"<h1>Hello</h1>"
                }
            }
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        expectedContent : "<h1>Hello</h1>"
    });

    defaultTestSuite({
        title: "text : read",
        attribute: {type: "text", label:"Mon libellé", "id":"mytext1"},
        options: {
            renderMode: "view"

        },
        renderOptions:{
            types: {
                "text" : {
                    template:"{{attribute.id}}<h1>{{attribute.label}}/{{attribute.attributeValue.value}}</h1><h2>{{attribute.attributeValue.displayValue}}</h2>"
                }
            }
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        expectedContent : "mytext1<h1>Mon libellé/John Doe</h1><h2>John Doe l'inconnu</h2>"
    });

    defaultTestSuite({
        title: "text : read",
        attribute: {type: "text", label:"Mon libellé", "id":"mytext"},
        options: {
            renderMode: "view"

        },
        renderOptions:{
            types: {
                "text" : {
                    template:"{{attributes.mytext.id}}<h1>{{attributes.mytext.label}}/{{attributes.mytext.attributeValue.value}}</h1><h2>{{attributes.mytext.attributeValue.displayValue}}</h2>"
                }
            }
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        expectedContent : "mytext<h1>Mon libellé/John Doe</h1><h2>John Doe l'inconnu</h2>"
    });
    defaultTestSuite({
        title: "text : write",
        attribute: {type: "text", label:"Mon libellé"},
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "Jane Doe",
            displayValue: "Jane Doe l'inconnue"
        },
        renderOptions:{
            types: {
                "text" : {
                    template:"<h1>Hello Jane</h1>"
                }
            }
        },
        expectedContent : "<h1>Hello Jane</h1>"
    });


    defaultTestSuite({
        title: "text : write",
        attribute: {type: "text", label:"Mon libellé"},
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "Jane Doe",
            displayValue: "Jane Doe l'inconnue>"
        },
        renderOptions:{
            types: {
                "text" : {
                    template:"<h1>Hello {{attribute.attributeValue.displayValue}}</h1>"
                }
            }
        },
        expectedContent : "<h1>Hello Jane Doe l'inconnue&gt;</h1>"
    });


    defaultTestSuite({
        title: "text : read",
        attribute: {type: "text", label:"Mon libellé"},
        options: {
            renderMode: "view"
        },
        renderOptions:{
            types: {
                "text" : {
                    template:"<h1>{{{attribute.htmlContent}}}</h1>"
                }
            }
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        expectedSubContents : [
            {filter:"h1 > .dcpCustomTemplate--content .dcpAttribute__content__value",
                htmlValue:"John Doe l'inconnu"}
        ]
    });

    defaultTestSuite({
        title: "text : write",
        attribute: {type: "text", label:"Mon libellé"},
        options: {
            renderMode: "edit"
        },
        renderOptions:{
            types: {
                "text" : {
                    template:"<h1>{{{attribute.htmlContent}}}</h1>"
                }
            }
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        expectedSubContents : [
            {filter:"h1 > .dcpCustomTemplate--content input.dcpAttribute__value",
                textValue:null}
        ]
    });


    defaultTestSuite({
        title: "text : read",
        attribute: {type: "text", label:"Mon super libellé"},
        options: {
            renderMode: "view"
        },
        renderOptions:{
            types: {
                "text" : {
                    template:"<h1>{{{attribute.htmlView}}}</h1>"
                }
            }
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        expectedSubContents : [
            {filter:"h1 > .dcpCustomTemplate--content .dcpAttribute__content__value",
                htmlValue:"John Doe l'inconnu"},

            {filter:"h1 >  .dcpCustomTemplate--content .dcpAttribute__label.dcpLabel",
                textValue:"Mon super libellé"}
        ]
    });

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});