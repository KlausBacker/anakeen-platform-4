import './testrender.css';
const _ = require('underscore');

$(document).ready(() => {

    function viewDocument(docid, viewId) {
        const $target = $(".systemRight");

        if (!$target.data("isInitialized")) {
            $target.document({
                "initid": docid,
                "viewId": viewId || "!defaultConsultation",
                withoutResize:true
            });
            $target.document("addEventListener", "ready", {}, (event, document) => {
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


    $.getJSON("api/v1/families/TST_RENDER/documents/?slice=ALL").done((result) => {
        const template= _.template('<button data-docid="<%- properties.id %>" class="btn btn-outline-success tst-create"> <%- properties.title %></button>');

        if (result && result.data && result.data.documents) {
            result.data.documents.forEach((item) => {
               $(".systemLeft").append(template(item));
            });
        }
    });

    $(".systemLeft").on("click", ".tst-create", function() {
        viewDocument($(this).data('docid'), "!defaultConsultation");
    });
});
