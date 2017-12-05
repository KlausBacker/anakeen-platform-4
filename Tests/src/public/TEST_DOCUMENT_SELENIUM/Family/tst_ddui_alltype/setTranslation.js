( function(window) {
        'use strict';

        function contrainte () {
            console.log("   declaration contrainte ")
            
            window.dcp.document.documentController(
                "addConstraint",
                {
                    "name": "checkinteger",
                    "documentCheck": function (documentObject) {
                        return documentObject.family.name === "TST_DDUI_ALLTYPE"
                    },
                    "attributeCheck": function (attribute,doc,toto) {
                        return attribute.id === "test_ddui_all__integer";
                    }
                },
                function (documentObject, attribute, value) {

                    if (value.current.value > 11) {
                        return "doit etre inférieur a 11";
                    }
                }
            );
        }
            window.dcp.document.documentController("addEventListener",
                "ready",
                function (event, documentObject, message) {
                
                    contrainte()
                }
            );
        }
)(window)
