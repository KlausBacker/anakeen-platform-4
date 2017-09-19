/*global require*/
require([
    'dcpDocumentTest/suiteDefaultTestAttribute'
], function require_defaultTestattribute(defaultTestSuite)
{
    "use strict";

    //region text
    defaultTestSuite({
        title: "text : read",
        attribute: {type: "text"},
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        otherValue: {
            value: "La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine",
            displayValue: "La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine"
        }
    });
    defaultTestSuite({
        title: "text : write",
        attribute: {type: "text"},
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe l'inconnu"
        },
        otherValue: {
            value: "Les parties supérieures sont gris-bleu",
            displayValue: "Les parties supérieures sont gris-bleu"
        }
    });
    //endregion text
    //region date
    defaultTestSuite({
        title: "date : read",
        attribute: {type: "date"},
        initialValue: {value: "2012-12-14", displayValue: "14/12/2012"},
        otherValue: {value: "1985-05-12", displayValue: "12/05/1985"}
    });
    defaultTestSuite({
        title: "date : write",
        attribute: {type: "date"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "2012-12-14", displayValue: "14/12/2012"},
        otherValue: {value: "1985-05-12", displayValue: "12/05/1985"}
    });
    //endregion date
    //region timestamp
    defaultTestSuite({
        title: "timestamp : read",
        attribute: {type: "timestamp"},
        initialValue: {value: "2012-12-14T12:56", displayValue: "14/12/2012 12:56"},
        otherValue: {value: "1985-05-12T10:00", displayValue: "12/05/1985 10:00"}
    });
    defaultTestSuite({
        title: "timestamp : write",
        attribute: {type: "timestamp"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "2012-12-14T12:56", displayValue: "14/12/2012 12:56"},
        otherValue: {value: "1985-05-12T10:00", displayValue: "12/05/1985 10:00"}
    });
    //endregion timestamp

    //region time
    defaultTestSuite({
        title: "time : read",
        attribute: {type: "time"},
        initialValue: {value: "12:56", displayValue: "12:56"},
        otherValue: {value: "23:59", displayValue: "23:59"}
    });
    defaultTestSuite({
        title: "time : write",
        attribute: {type: "time"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "12:56", displayValue: "12:56"},
        otherValue: {value: "23:59", displayValue: "23:59"}
    });
    //endregion time
    //region docid
    defaultTestSuite({
        title: "docid : read",
        attribute: {type: "docid"},
        initialValue: {value: "1253", displayValue: "John Doe"},
        otherValue: {value: "7865", displayValue: "Éléonord Hùço"}
    });
    defaultTestSuite({
        title: "docid : write",
        attribute: {type: "docid"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "1253", displayValue: "John Doe"},
        otherValue: {value: "7865", displayValue: "Éléonord Hùço"}
    });
    //endregion docid
    //region int
    defaultTestSuite({
        title: "int : read",
        attribute: {type: "int"},
        initialValue: {value: "1253", displayValue: "1 253"},
        otherValue: {value: "7865", displayValue: "7 865"}
    });
    defaultTestSuite({
        title: "int : write",
        attribute: {type: "int"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "1253", displayValue: "1 253"},
        otherValue: {value: "7098865", displayValue: "7 098 865"}
    });
    //endregion int
    //region double
    defaultTestSuite({
        title: "double : read",
        attribute: {type: "double"},
        initialValue: {value: "1253", displayValue: "1 253"},
        otherValue: {value: "7865.678", displayValue: "7 865,678"}
    });
    defaultTestSuite({
        title: "double : write",
        attribute: {type: "double"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "1253", displayValue: "1 253"},
        otherValue: {value: "7865.678", displayValue: "7 865,678"}
    });
    //endregion double
    //region money
    defaultTestSuite({
        title: "money : read",
        attribute: {type: "money"},
        initialValue: {value: "1253", displayValue: "1 253 €"},
        otherValue: {value: "7865.68", displayValue: "7 865,68 €"}
    });
    defaultTestSuite({
        title: "money : write",
        attribute: {type: "money"},
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "1253", displayValue: "1 253 €"},
        otherValue: {value: "7865.68", displayValue: "7 865,68 €"}
    });
    //endregion money
    //region enum
    defaultTestSuite({
        title: "enum : read",
        attribute: {type: "enum"},
        initialValue: {value: "G", displayValue: "Sol"},
        otherValue: {value: "D", displayValue: "Ré"}
    });
    defaultTestSuite({
        title: "enum vertical: write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ]
        },
        options: {
            renderMode: "edit"
        },
        renderOptions: {
            editDisplay: "vertical"
        },
        initialValue: {value: "G", displayValue: "Sol"},
        otherValue: {value: "D", displayValue: "Ré"}
    });
    defaultTestSuite({
        title: "enum horizontal: write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ]
        },
        options: {
            renderMode: "edit"
        },
        renderOptions: {
            editDisplay: "horizontal"
        },
        initialValue: {value: "G", displayValue: "Sol"},
        otherValue: {value: "D", displayValue: "Ré"}
    });
    defaultTestSuite({
        title: "enum autoCompletion: write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ]
        },
        options: {
            renderMode: "edit"
        },
        renderOptions: {
            editDisplay: "autoCompletion"
        },
        initialValue: {value: "G", displayValue: "Sol"},
        otherValue: {value: "D", displayValue: "Ré"}
    });
    defaultTestSuite({
        title: "enum : write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ]
        },
        options: {
            renderMode: "edit"
        },
        initialValue: {value: "G", displayValue: "Sol"},
        otherValue: {value: "D", displayValue: "Ré"}
    });
    defaultTestSuite({
        title: "enum multiple : read",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ],
            options: {
                multiple: "yes"
            }
        },
        options: {
            renderMode: "view"
        },
        initialValue: [
            {value: "G", displayValue: "Sol"}
        ],
        otherValue: [
            {value: "D", displayValue: "Ré"},
            {value: "A", displayValue: "La"}
        ]
    });
    defaultTestSuite({
        title: "enum multiple : write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ],
            options: {
                multiple: "yes"
            }
        },
        options: {
            renderMode: "edit"
        },
        initialValue: [
            {value: "G", displayValue: "Sol"}
        ],
        otherValue: [
            {value: "D", displayValue: "Ré"},
            {value: "A", displayValue: "La"}
        ]
    });
    defaultTestSuite({
        title: "enum multiple vertical: write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ],
            options: {
                multiple: "yes"
            }
        },
        options: {
            renderMode: "edit"
        },
        renderOptions: {
            editDisplay: "vertical"
        },
        initialValue: [
            {value: "G", displayValue: "Sol"}
        ],
        otherValue: [
            {value: "D", displayValue: "Ré"},
            {value: "A", displayValue: "La"}
        ]
    });
    defaultTestSuite({
        title: "enum multiple horizontal: write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ],
            options: {
                multiple: "yes"
            }
        },

        options: {
            renderMode: "edit"
        },
        renderOptions: {
            editDisplay: "horizontal"
        },
        initialValue: [
            {value: "G", displayValue: "Sol"}
        ],
        otherValue: [
            {value: "D", displayValue: "Ré"},
            {value: "A", displayValue: "La"}
        ]
    });
    defaultTestSuite({
        title: "enum multiple autoCompletion: write",
        attribute: {
            type: "enum",
            enumItems: [
                {key: "A", label: "La"},
                {key: "B", label: "Si"},
                {key: "C", label: "Do"},
                {key: "D", label: "Ré"},
                {key: "E", label: "Mi"},
                {key: "F", label: "Fa"},
                {key: "G", label: "Sol"}
            ],
            options: {
                multiple: "yes"
            }
        },
        options: {
            renderMode: "edit"
        },
        renderOptions: {
            editDisplay: "autoCompletion"
        },
        initialValue: [
            {value: "G", displayValue: "Sol"}
        ],
        otherValue: [
            {value: "D", displayValue: "Ré"},
            {value: "A", displayValue: "La"}
        ]
    });
    //endregion enum
    //region file
    defaultTestSuite({
        title: "file : read",
        attribute: {type: "file"},
        initialValue: {
            value: "image/png|J123|ping.png",
            displayValue: "ping.png"
        },
        otherValue: {
            value: "image/png|678|pong.png",
            displayValue: "pong.png"
        }
    });
    defaultTestSuite({
        title: "file : write",
        attribute: {type: "file"},
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "image/png|J123|ping.png",
            displayValue: "ping.png"
        },
        otherValue: {
            value: "image/png|678|pong.png",
            displayValue: "pong.png"
        }
    });
    //endregion file
    //region image
    defaultTestSuite({
        title: "image : read",
        attribute: {type: "image"},
        initialValue: {
            value: "image/png|J123|ping.png",
            displayValue: "ping.png"
        },
        otherValue: {
            value: "image/png|678|pong.png",
            displayValue: "pong.png"
        }
    });
    defaultTestSuite({
        title: "image : write",
        attribute: {type: "image"},
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "image/png|J123|ping.png",
            displayValue: "ping.png"
        },
        otherValue: {
            value: "image/png|678|pong.png",
            displayValue: "pong.png"
        }
    });
    //endregion image
    //region htmltext
    defaultTestSuite({
        title: "htmltext : read",
        attribute: {type: "htmltext"},
        noFixture: true,
        initialValue: {
            value: "<p>John Doe</p>",
            displayValue: "<p>John Doe l'inconnu</p>"
        },
        otherValue: {
            value: "<p>La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine</p>",
            displayValue: "<p>La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine</p>"
        }
    });
    defaultTestSuite({
        title: "htmltext : write",
        attribute: {type: "htmltext"},
        noFixture: true,
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "<p>John Doe</p>",
            displayValue: "<p>John Doe l'inconnu</p>"
        },
        otherValue: {
            value: "<p>La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine</p>",
            displayValue: "<p>La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine</p>"
        }
    });
    //endregion htmltext
    //region longtext
    defaultTestSuite({
        title: "longtext : read",
        attribute: {type: "longtext"},
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe\nl'inconnu"
        },
        otherValue: {
            value: "La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine\n s'approchant de la frontière avec la Birmanie",
            displayValue: "La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine\n s'approchant de la frontière avec la Birmanie"
        }
    });
    defaultTestSuite({
        title: "longtext : write",
        attribute: {type: "longtext"},
        options: {
            renderMode: "edit"
        },
        initialValue: {
            value: "John Doe",
            displayValue: "John Doe\nl'inconnu"
        },
        otherValue: {
            value: "La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine\n s'approchant de la frontière avec la Birmanie",
            displayValue: "La Sittelle du Yunnan est endémique du Sud-Ouest de la Chine\n s'approchant de la frontière avec la Birmanie"
        }

    });
    //endregion longtext

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }

});