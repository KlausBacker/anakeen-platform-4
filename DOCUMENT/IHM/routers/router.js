/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone, ViewMakeRequest, ViewRequestAssigned) {
    'use strict';

    var views = {};

    return Backbone.Router.extend({
        routes: {
            "cancel/:subpart": "cancel",
            "save/:subpart": "save"
        },

        cancel: function (id) {
            var currentDoc = window.dcp.documents.get(id);
            if (currentDoc && currentDoc.hasAttributesChanged() && !confirm("It has been changed !! Are you sure ??")) {
                Backbone.history.navigate("", {replace: true});
            } else {
                window.location = "?app=DOCUMENT&action=VIEW&id=" + id
            }
        },

        save: function (id) {
            var currentDoc = window.dcp.documents.get(id), values;
            values = {document: { attributes: currentDoc.getValues()}};
            $(".dcpLoading").dcpLoading("reset").dcpLoading("title", "Saving").dcpLoading("modalMode");

            //$(".dcpDocument").hide();
            _.defer(function () {
                $.ajax({
                    type: "PUT",
                    dataType: "json",
                    contentType: 'application/json',
                    url: "api/documents/" + id + "/",
                    data: JSON.stringify(values)
                }).done(function (result) {
                    $(".dcpLoading").dcpLoading("hide");
                    if (result.success) {
                        window.location = "?app=DOCUMENT&action=VIEW&id=" + id
                    }
                }).fail(function (data) {

                    currentDoc.clearErrorMessages();

                    $(".dcpLoading").dcpLoading("hide");
                    var result=JSON.parse(data.responseText);
                    _.each(result.messages, function (errorMessage) {
                        if (errorMessage.type==="error") {
                            currentDoc.addErrorMessage(errorMessage);
                        }
                    });
                    console.log(result);
                });
                Backbone.history.navigate("", {replace: true});
            });
        }
    });

});