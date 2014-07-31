/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/vAttribute'
], function (_, Backbone, Mustache, ViewAttribute) {
    'use strict';

    return ViewAttribute.extend({

        /**
         * Use special event to trigger only attributes of mdel
         * @returns {{dcparraylineadded: string, dcparraylineremoved: string}}
         */
        events: function () {
            var absEvents = {
                "dcparraylineadded": "addNewWidget",
                "dcparraylineremoved": "nothing"
            };

            this._addEvent(absEvents, "changeattrsvalue", "changeAttributesValue");
            this._addEvent(absEvents, "delete", "deleteValue");
            return absEvents;
        },


        _addEvent: function (events, name, method) {
            events["dcpattribute" + name + ' .dcpArray__content__cell[data-attrid="' + this.model.id + '"]'] = method;
        },


        render: function () {
            // console.time("render column " + this.model.id);


            //  console.timeEnd("render column " + this.model.id);
            return this;
        },


        nothing: function nothing(event, options) {
        },
        /**
         * called by vArray::addLine()
         * @param index
         */
        addNewWidget: function addNewWidget(index) {
            if (this.options) {
                var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]');
                var aModel = this.model;
                var data = this.getData(index);
                var widgetClass = this.getTypedWidgetClass(data.type);

                if (cells[index]) {

                    widgetClass.apply(
                        $(cells[index]),
                        [data]);
                }
            }
        }


    });

});