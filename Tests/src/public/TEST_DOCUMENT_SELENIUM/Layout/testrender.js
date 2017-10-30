require([ "dcpDocument/document"], function testMain() {
    $(document).ready(function testOnReady() {
        var firstSearch = $($("a.searchIcon").get(0));
        var iOS = /(iPad|iPhone|iPod)/g.test(navigator.userAgent);

        function viewDocument(docid, viewId) {
            var $target = $(".systemRight");

            if (!$target.data("isInitialized")) {
                $target.document({
                    "initid": docid,
                    "viewId": viewId || "!defaultConsultation",
                    withoutResize:true
                });
                $target.document("addEventListener", "ready", {}, function logRead(event, document) {
                    console.log("new ready", document);

                });

                $target.data("isInitialized", true);
            } else {
                $target.document("fetchDocument", {
                    "initid": docid,
                    "viewId": viewId || "!defaultConsultation"
                });
            }
        }


        $.getJSON("api/v1/families/TST_RENDER/documents/?slice=ALL").done(function getFamiliesTst(result) {
            console.log("result", result);
            var template= _.template('<button data-docid="<%- properties.id %>" class="btn btn-outline-success tst-create"> <%- properties.title %></button>');
            var btns;

            if (result && result.data && result.data.documents) {
                _.each(result.data.documents, function (item) {
                   $(".systemLeft").append(template(item));
                });
            }
        });

        $(".systemLeft").on("click", ".tst-create", function clickOnCreate() {
            viewDocument($(this).data('docid'), "!defaultConsultation");
        });
    });
});
