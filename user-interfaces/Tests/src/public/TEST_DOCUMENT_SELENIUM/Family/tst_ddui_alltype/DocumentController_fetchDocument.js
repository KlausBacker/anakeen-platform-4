/**
 * test Document Fetching throw user event
 */

(function (window) {
    'use strict';
    function test() {
        $("button.mybtn_DocumentController_fetchDocument").on("click", function () {
            window.dcp.document.documentController("fetchDocument",
                {initid: 1156},
                {force: false}
            ).then(function (data) {
                console.log("Document " + data.nextDocument.title + " has been loaded");
            }).catch(function (data) {
                console.warn(data.errorMessage.contentText);
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
