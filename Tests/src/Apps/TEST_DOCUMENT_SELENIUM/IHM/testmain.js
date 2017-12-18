import './testmain.css';

const _ = require('underscore');

$(document).ready(function testOnReady() {

    function viewDocument(docid, viewId) {
        const $target = $(".systemRight");

        if (!$target.data("isInitialized")) {
            $target.document({
                "initid": docid,
                "viewId": viewId || "!defaultConsultation"
            });

            $target.data("isInitialized", true);
        } else {
            $target.document("fetchDocument", {
                "initid": docid,
                "viewId": viewId || "!defaultConsultation"
            });
        }
    }


    $.getJSON("api/v1/test/ddui/families/").done((result) => {
        const template = _.template('<button data-familyname="<%- properties.name %>" class="btn btn-default tst-create"><img src="<%- properties.icon %>?width=32"/>Create <%- properties.title %></button>');

        if (result && result.data && result.data.documents) {
            result.data.documents.forEach((item) => {
                $(".systemLeft").append(template(item));
            });
        }
    });

    $(".systemLeft").on("click", ".tst-create", function() {
        viewDocument($(this).data('familyname'), "!defaultCreation");
    });
});
