window.dcp.document.documentController("addEventListener",
    "ready",
    function(event, documentObject, message) {
        this.documentController("showMessage", "I'm ready");
    }
);

