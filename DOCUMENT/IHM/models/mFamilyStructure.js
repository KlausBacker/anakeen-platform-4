define([
    "underscore",
    "backbone"
], function (_, Backbone)
{
    "use strict";


    return Backbone.Model.extend({

        idAttribute: "familyId",

        url: function mFamilyStructure_url()
        {
            var urlStructure = _.template("api/v1/families/<%- familyId %>/views/structure");

            return urlStructure({
                familyId: this.get("familyId")
            });

        }
    });
});