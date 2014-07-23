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

        nothing: function nothing(event, options) {
        },
        /**
         * called by vArray::addLine()
         * @param index
         */
        addNewWidget: function addNewWidget(index) {
            var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]');
            var aModel = this.model;
            var data=aModel.toData(index);
            var widgetClass = this.getTypedWidgetClass(data.type);

            if (cells[index]) {

                data.renderOptions = aModel.getOptions();
                widgetClass.apply(
                    $(cells[index]),
                    [data]);
            }

        }
    });

});