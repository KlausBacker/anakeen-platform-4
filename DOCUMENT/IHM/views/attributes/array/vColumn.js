/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/vAttribute'
], function (_, Backbone, Mustache, ViewAttribute) {
    'use strict';

    return ViewAttribute.extend({


        events: {
            "dcparraylineadded": "addNewWidget",
            "dcparraylineremoved": "nothing"
        },


        render: function () {
            console.time("render column " + this.model.id);


            console.timeEnd("render column " + this.model.id);
            return this;
        },

        nothing: function updateColumn(event, options) {
            console.log("IN COLUMN update value", options);
        },
        /**
         * called by vArray::addLine()
         * @param index
         */
        addNewWidget: function updateColumn(index) {
            var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]');
            var aModel = this.model;
            var data = this.model.toData();
            var widgetClass = this.getTypedWidgetClass(data.type);

            if (cells[index]) {
                widgetClass.apply(
                    $(cells[index]),
                    [aModel.toData(index)]);
            }

        }
    });

});