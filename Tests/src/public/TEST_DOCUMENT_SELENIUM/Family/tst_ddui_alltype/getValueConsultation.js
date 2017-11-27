function test() {
    var testValues = window.dcp.document.documentController("getValues")
    console.log("ok"+testValues);
    for (prop in testValues) {
        var testAttributeValue = JSON.stringify(window.dcp.document.documentController("getValue", prop.toString()))
        console.log("attribue"+prop+": Value = "+testAttributeValue);
    }
};
window.dcp.document.documentController("addEventListener",
    "ready",
    function(event, documentObject, message) {
        this.documentController("showMessage", "I'm ready");
        test()

    }
);