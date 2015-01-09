define([
    "underscore",
    "models/mAttributeStructural"
], function (_, AttributeStructuralModel) {
    'use strict';

    return AttributeStructuralModel.extend({

        removeIndexedLine : function mAttributeArrayRemoveIndexLine(index) {
            this.trigger("removeWidgetLine", {index : index}, {silent : true});
        },

        addIndexedLine : function mAttributeArrayaddIndexedLine(index) {
            this.trigger("addWidgetLine", {index : index});
        }
    });
});