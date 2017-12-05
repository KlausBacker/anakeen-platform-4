/**
 * test Document Reinitialisation throw user event
 */

(function (window) {
    'use strict';

    function test() {
        $("button.mybtn_DocumentController_reinitDocument").on("click", function () {
            window.dcp.document.documentController("reinitDocument").then(function (data) {
                data.element.documentController("showMessage", {
                    type: "success",
                    message: "Document " + data.nextDocument.title + " has been reinisialized"
                });
            }).catch(function (data) {
                data.element.documentController("showMessage", {
                    type: "error",
                    message: data.errorMessage.contentText
                });
            });
        });
    }

    window.dcp.document.documentController("addEventListener",
        "ready",
        function (event, documentObject, message) {
            this.documentController("showMessage", "I'm ready");
            test()
        }
    );
})(window);

