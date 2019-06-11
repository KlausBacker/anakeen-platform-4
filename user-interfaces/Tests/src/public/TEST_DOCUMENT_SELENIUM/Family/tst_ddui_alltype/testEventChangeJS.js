window.dcp.document.documentController("addEventListener",
    "change",
    {
        "name": "displayChange"
    },
    function displayChange(event, documentObject, attributeObject, values) {
        this.documentController("showMessage", "Method 1 ->  Attribut "+attributeObject.id+" has changed");
    }
);

window.dcp.document.documentController("addEventListener",
    "ready",
    {
        "name": "addAnimalEvent",
        "documentCheck": function(documentObject) {
            return documentObject.family.name === "TST_DDUI_ALLTYPE";
        }
    },
    function(event, documentObject) {
        var test;
        this.documentController("addEventListener",
            "change",
            {
                "name" : "changeAge.animal",
                "documentCheck": function(documentObject) {
                    return documentObject.family.name === "TST_DDUI_ALLTYPE";
                },
                "attributeCheck" : function(attributeObject) {
                    return attributeObject.id === "test_ddui_all__account"
                }
            },
            function() {
                this.documentController("showMessage", {type:"warning",message:"Method 2 -> You have changed the specific attribute account account"});
            }
        );
    }
);
