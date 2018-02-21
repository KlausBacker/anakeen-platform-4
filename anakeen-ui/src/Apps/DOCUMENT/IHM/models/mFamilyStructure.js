define([
    "underscore",
    "backbone"
], function require_structure(_, Backbone)
{
    "use strict";

    return Backbone.Model.extend({

        typeModel:"ddui:familyStructure",
        idAttribute: "familyId",

        url: function mFamilyStructure_url()
        {
            var urlStructure = _.template("api/v2/families/<%- familyId %>/views/structure");

            return urlStructure({
                familyId: this.get("familyId")
            });

        }
    });
});