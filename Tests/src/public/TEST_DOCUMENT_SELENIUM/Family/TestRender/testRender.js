require([ "dcpDocument/document"], function testMain()
{
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
            $target.height("calc(100vh - " + ($target.offset().top + 25) + "px)");
        }

    })
});