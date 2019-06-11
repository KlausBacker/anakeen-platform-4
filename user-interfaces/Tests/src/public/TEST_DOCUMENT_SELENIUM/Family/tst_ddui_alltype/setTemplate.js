


/**
 * test getValue method during document edition
 */

( function(window){
        'use strict';

        var attributeValue, attributeId, attributeType, msgColor = '';

        function test() {


        }
        window.dcp.document.documentController("addEventListener",
            "ready",
            function(event, documentObject, message) {
                this.documentController("showMessage", "I'm ready");
                test()
            }
        );
    }
)(window)

