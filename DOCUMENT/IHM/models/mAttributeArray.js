define([
    "underscore",
    "models/mAttribute"
], function (_, AttributeModel) {
    'use strict';

    return AttributeModel.extend({

        removeIndexedLine : function mAttributeArrayRemoveIndexLine(index) {
            this.trigger("removeWidgetLine", {index : index}, {silent : true});
        },

        addIndexedLine : function mAttributeArrayaddIndexedLine(index) {
            this.trigger("addWidgetLine", {index : index});
        }
    });
});