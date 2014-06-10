/*global define*/
define([
    'underscore',
    'backbone'
], function (_, Backbone, ViewMakeRequest, ViewRequestAssigned) {
    'use strict';

    var views = {};

    return Backbone.Router.extend({
        routes : {
            "cancel/:subpart" :                     "cancel",
            "save/:subpart" : "save"
        },

        cancel : function(id) {
            var currentDoc = window.dcp.documents.get(id);
            if (currentDoc && currentDoc.hasAttributesChanged() && !confirm("It has been changed !! Are you sure ??")) {
                Backbone.history.navigate("", {replace : true});
            } else {
                window.location = "?app=DOCUMENT&action=VIEW&id=" + id
            }
        },

        save : function(id) {
            var currentDoc = window.dcp.documents.get(id), values;
            values = {document : { attributes : currentDoc.getValues()}};
            $(".dcpLoading").show();
            $(".dcpDocument").hide();
            $.ajax({
                type :        "PUT",
                dataType :    "json",
                contentType : 'application/json',
                url :         "api/documents/" + id + "/",
                data :        JSON.stringify(values)
            }).done(function(result) {
                $(".dcpLoading").hide();
                if (result.success) {
                    window.location = "?app=DOCUMENT&action=VIEW&id="+id
                }
            }).fail(function(data) {
                $(".dcpLoading").hide();
                $(".dcpDocument").show();
                console.log(data);
            });
            Backbone.history.navigate("", {replace : true});
        }
    });

});