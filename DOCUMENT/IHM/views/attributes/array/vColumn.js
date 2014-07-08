/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/vAttribute'
], function (_, Backbone, Mustache, ViewAttribute) {
    'use strict';

    return ViewAttribute.extend({




        render: function () {
            console.log("render column " + this.model.id);
            console.time("render column " + this.model.id);
            var aModel=this.model;
            var data = this.model.toData();
            var values = this.model.get('value');
            var widgetClass = this.getTypedWidgetClass(data.type);
            $(".dcpLoading").dcpLoading("addItem", data.content.length + 1);

         //   console.log("DATA", data);
            var cells = this.options.parentElement.find('.dcpArray__content__cell[data-attrid="' + this.model.id + '"]');

          //  console.log("CELLS", this.getTypedWidgetClass(), cells);
          //  console.log("widgetClass",widgetClass);

            _.each(cells, function (cellElement, cIndex) {
              //  console.log("INIT CELL",cellElement, cIndex );
              //  console.log("w", cIndex, aModel.toData(cIndex));


                widgetClass.apply(
                    $(cellElement),
                    [aModel.toData(cIndex)]);

            });


          //  console.log("render column " + this.model.id, values, data);

            console.timeEnd("render column " + this.model.id);
            return this;
        }
    });

});