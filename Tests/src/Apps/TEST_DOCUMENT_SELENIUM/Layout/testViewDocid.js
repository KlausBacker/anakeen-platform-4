

window.dcp.document.documentController("addEventListener",
    "ready",
    {
        "name": "tstddui.subwindow",
        "documentCheck": function checkDduiDocidReady(document) {
            'use strict';
            return document.family.name === "TST_DDUI_DOCID";
        }
    },
    function testDduiDocidReady(/*event, document, data*/) {
        'use strict';

        if (window.frameElement) {
            // hide header and menu if document is in dialog window
            if ($(window.frameElement).closest(".dialog-window").length === 1) {
                $(".dcpDocument__header").hide();
                $(".dcpDocument__menu").hide();

            }
        }

    }
);