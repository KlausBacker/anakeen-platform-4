/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'widgets/attributes/array/array'
], function (_, Backbone, Mustache) {
    'use strict';

    return Backbone.View.extend({

        events : {
             "dcparraylineadded" : "initWidget"
        },

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render : function () {
            console.time("render array " + this.model.id);
            var data = this.model.toData();
            data.nbLines = this.getNbLines();
            this.$el.dcpArray(data);
            console.timeEnd("render attribute " + this.model.id);
            return this;
        },

        getNbLines : function() {
            var nbLigne = 0;
            this.model.get("content").each(function (currentAttr) {
                if (nbLigne < currentAttr.get("value").length) {
                    nbLigne = currentAttr.get("value").length;
                }
            });
            return nbLigne;
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        initWidget : function(event, options) {
            var model = this.model;
            options.element.find(".dcpArray__content__cell").each(function(index, element) {
                var $element = $(element), objectData = model.get("content").get($element.data("attrid"));
                $(element).dcpText(objectData.toData(options.line));
            })
        }
    });

});