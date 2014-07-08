/*global define*/
define([
    'underscore',
    'backbone',
    'widgets/window/wNotification'
], function (_, Backbone, ViewMakeRequest, ViewRequestAssigned) {
    'use strict';

    var views = {};

    return Backbone.Router.extend({
        routes: {
            "cancel/:subpart": "cancel",
            "close/:subpart": "close",
            "save/:subpart": "save"
        },

        cancel: function (id) {
            var currentDoc = window.dcp.documents.get(id);
            if (currentDoc && currentDoc.hasAttributesChanged() && !window.confirm("It has been changed !! Are you sure ??")) {
                Backbone.history.navigate("", {replace: true});
            } else {
                window.location = "?app=DOCUMENT&action=VIEW&id=" + id;
            }
        },
        close: function (id) {
            var currentDoc = window.dcp.documents.get(id);
            if (currentDoc && currentDoc.hasAttributesChanged() && !window.confirm("It has been changed !! Are you sure ??")) {
                Backbone.history.navigate("", {replace: true});
            } else {
                window.location = "?app=DOCUMENT&action=VIEW&id=" + id;
            }
        },

        save: function (id) {
            var currentDoc = window.dcp.documents.get(id), values;
            values = {document: { attributes: currentDoc.getValues()}};

            var $notification = $('body').dcpNotification();
            $notification.dcpNotification("clear");

            if (currentDoc.verifyAndNotifyNeededAttributes()) {

                $(".dcpLoading").dcpLoading("reset").dcpLoading("title", "Saving").dcpLoading("modalMode");

                //$(".dcpDocument").hide();
                _.defer(function () {
                    $.ajax({
                        type: "PUT",
                        dataType: "json",
                        contentType: 'application/json',
                        url: "api/v1/documents/" + id + "/",
                        data: JSON.stringify(values)
                    }).done(function (result) {

                        currentDoc.clearErrorMessages();
                        $(".dcpLoading").dcpLoading("hide");
                        if (result.success) {
                            $notification.dcpNotification("showSuccess", {title: "Document Recorded"});
                            // window.location = "?app=DOCUMENT&action=VIEW&id=" + id
                            _.each(result.messages, function (aMessage) {
                                if (aMessage.type === "message") {
                                    aMessage.type = "info";
                                }
                                $notification.dcpNotification("show", aMessage.type, {
                                    title: aMessage.contentText,
                                    htmlMessage: aMessage.contentHtml
                                });
                            });
                        }
                    }).fail(function (data) {

                        currentDoc.clearErrorMessages();

                        $(".dcpLoading").dcpLoading("hide");
                        var result = JSON.parse(data.responseText);
                        _.each(result.messages, function (errorMessage) {
                            if (errorMessage.type === "error") {
                                currentDoc.addErrorMessage(errorMessage);
                            }
                        });
                    });
                    Backbone.history.navigate("", {replace: true});
                });
            } else {
                Backbone.history.navigate("", {replace: true});
            }
        }
    });

});