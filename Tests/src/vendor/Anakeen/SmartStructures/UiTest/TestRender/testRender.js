import './testRender.css';

window.dcp.document.documentController("addEventListener", "ready", {
    "name": "display.render", "documentCheck": function (document)
    {
        return document.family.name === "TST_RENDER"
    }
}, function (event, documentObject)
{
    console.log(documentObject);

    var $target = $(".test-document");
    var docid = $(this).documentController("getValue", "tst_docname").value;
    var viewId = $(this).documentController("getValue", "tst_docviewid").value;

    if ($target.length === 1) {
        $target.document({
            "initid": docid, "viewId": viewId || "!defaultConsultation", withoutResize: true
        });

        $(window).trigger("resize");
        $target.height("calc(100vh - " + ($target.offset().top + 3) + "px)");
    }
});

window.dcp.document.documentController(
    "addEventListener",
    "actionClick",
    {
        "name": "tst.openWindow",
        "documentCheck": function (documentObject) {
            return (documentObject.family.name === "TST_RENDER");
        }
    },
    function(event, documentObject, data) {
        if (data.eventId === "tst") {
            if (data.options.length > 0 && data.options[0] === "openWindow" ) {
                var $target = $(".test-document");
                window.open($target.document("getProperties").url);
            }
        }
    }
);