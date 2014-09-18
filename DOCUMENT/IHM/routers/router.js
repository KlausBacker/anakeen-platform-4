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
            "create/:subpart": "create",
            "save/:subpart": "save"
        },

        cancel: function (id) {
            var currentDoc = window.dcp.documents.get(id);
            if (currentDoc && currentDoc.hasAttributesChanged() && !window.confirm("It has been changed !! Are you sure ??")) {
                Backbone.history.navigate("", {replace: true});
            } else {
                window.location = "?app=DOCUMENT&id=" + id;
            }
        },

        close: function (id) {
            var currentDoc = window.dcp.documents.get(id);
            if (currentDoc && currentDoc.hasAttributesChanged() && !window.confirm("It has been changed !! Are you sure ??")) {
                Backbone.history.navigate("", {replace: true});
            } else {
                window.location = "?app=DOCUMENT&id=" + id;
            }
        },
        create: function (id) {
            var currentDoc = window.dcp.documents.get(id), values;
            values = {document: { attributes: currentDoc.getValues()}};

            var $notification = $('body').dcpNotification();
            var fromId = currentDoc.get("properties").get("fromid");
            $notification.dcpNotification("clear");
            currentDoc.clearErrorMessages();
            console.log("doc", fromId, currentDoc);

            if (currentDoc.verifyAndNotifyNeededAttributes()) {
                $(".dcpLoading").dcpLoading("reset").dcpLoading("setTitle", "Saving").dcpLoading("modalMode");
                _.defer(function () {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        contentType: 'application/json',
                        url: "api/v1/families/" + fromId + "/",
                        data: JSON.stringify(values)
                    }).pipe(
                        function (response) {
                            if (response.success) {
                                return( response );
                            } else {
                                return($.Deferred().reject(response));
                            }
                        },
                        function (response) {
                            var messages = [];
                            try {
                                var result = JSON.parse(response.responseText);
                                messages = result.messages;
                            } catch (e) {
                            }

                            return({
                                success: false,
                                result: null,
                                messages: messages,
                                responseText: "Unexpected error: " + response.status + " " + response.statusText
                            });
                        })
                        .done(function (result) {
                            $(".dcpLoading").dcpLoading("hide");
                            if (result.success) {
                                $notification.dcpNotification("showSuccess", {title: "New Document Created"});
                                _.each(result.messages, function (aMessage) {
                                    if (aMessage.type === "message") {
                                        aMessage.type = "info";
                                    }
                                    $notification.dcpNotification("show", aMessage.type, {
                                        title: aMessage.contentText,
                                        htmlMessage: aMessage.contentHtml
                                    });
                                });
                                currentDoc.setValues(result.data.document.attributes);
                                currentDoc.setProperties(result.data.document.properties);
                                currentDoc.get('attributes').trigger("changeMenuVisibility", null, {
                                        id: "save",
                                        visibility: "visible"
                                    }
                                );
                                currentDoc.get('attributes').trigger("changeMenuVisibility", null, {
                                        id: "create",
                                        visibility: "hidden"
                                    }
                                );
                                currentDoc.get('attributes').trigger("changeMenuVisibility", null, {
                                        id: "create!",
                                        visibility: "hidden"
                                    }
                                );
                            }
                        }).fail(function (data) {
                            currentDoc.clearErrorMessages();
                            $(".dcpLoading").dcpLoading("hide");
                            if (data.messages.length === 0) {
                                data.messages = [
                                    {
                                        type: "error",
                                        contentText: data.responseText
                                    }
                                ];
                            }

                            _.each(data.messages, function (errorMessage) {
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
        },
        save: function (id) {
            var currentDoc = window.dcp.documents.get(id), values;
            var $notification = $('body').dcpNotification();

            if (!currentDoc) {
                // when it is a new document - index is 0
                currentDoc = window.dcp.documents.get(0);
            }
            values = {document: { attributes: currentDoc.getValues()}};

            $notification.dcpNotification("clear");
            currentDoc.clearErrorMessages();
            if (currentDoc.verifyAndNotifyNeededAttributes()) {
                $(".dcpLoading").dcpLoading("reset").dcpLoading("setTitle", "Saving").dcpLoading("modalMode");
                _.defer(function () {
                    $.ajax({
                        type: "PUT",
                        dataType: "json",
                        contentType: 'application/json',
                        url: "api/v1/documents/" + id + "/",
                        data: JSON.stringify(values)
                    }).pipe(
                        function (response) {
                            if (response.success) {
                                return( response );
                            } else {
                                return($.Deferred().reject(response));
                            }
                        },
                        function (response) {
                            return({
                                success: false,
                                result: null,
                                responseText: "Unexpected error: " + response.status + " " + response.statusText
                            });
                        })
                        .done(function (result) {
                            $(".dcpLoading").dcpLoading("hide");
                            if (result.success) {
                                $notification.dcpNotification("showSuccess", {title: "Document Recorded"});
                                _.each(result.messages, function (aMessage) {
                                    if (aMessage.type === "message") {
                                        aMessage.type = "info";
                                    }
                                    $notification.dcpNotification("show", aMessage.type, {
                                        title: aMessage.contentText,
                                        htmlMessage: aMessage.contentHtml
                                    });
                                });
                                currentDoc.setValues(result.data.document.attributes);
                                currentDoc.setProperties(result.data.document.properties);
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