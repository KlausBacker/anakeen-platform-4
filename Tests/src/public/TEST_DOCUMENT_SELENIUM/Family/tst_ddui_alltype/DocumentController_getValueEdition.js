function test() {
    var testValues = window.dcp.document.documentController("getValues"),
        _attrMsg =  "";
    for (prop in testValues) {
        var testAttributeValue = JSON.stringify(window.dcp.document.documentController("getValue", prop.toString()))
        _attrMsg += "<p> "+"attribue"+prop+": Value = "+testAttributeValue+" <p/>"
    }
    window.dcp.document.documentController("showMessage", {type:"warning",htmlMessage:_attrMsg});
};
window.dcp.document.documentController("addEventListener",
    "ready",
    function(event, documentObject, message) {
        this.documentController("showMessage", "I'm ready");
        test()

    }
);