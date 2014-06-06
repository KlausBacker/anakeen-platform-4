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
             "dcparraylineadded" : "initWidget",
             "dcparraylineremoved" : "initWidget",
             "dcpattributechange .dcpArray__content__cell" : "updateValue"
        },

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render : function () {
            console.time("render array " + this.model.id);
            var data = this.model.toData();
            data.content = _.filter(data.content, function(currentContent) {
                return currentContent.isDisplayable;
            });
            data.nbLines = this.getNbLines();
            this.$el.dcpArray(data);
            console.timeEnd("render attribute " + this.model.id);
            return this;
        },

        getNbLines : function() {
            var nbLigne = 0;
            this.model.get("content").each(function (currentAttr) {
                if (currentAttr.get("value") && nbLigne < _.size(currentAttr.get("value"))) {
                    nbLigne = _.size(currentAttr.get("value"));
                }
            });
            return nbLigne;
        },

        updateLabel : function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        updateValue : function(event, options) {
            var attributeModel = this.model.get("content").get(options.id);
            if (!attributeModel) {
                throw new Error("Unknown attribute " + options.id);
            }
            attributeModel.setValue(options.value, options.index);
        },

        initWidget : function(event, options) {
            var model = this.model;
            options.element.find(".dcpArray__content__cell").each(function(index, element) {
                var $element = $(element), currentAttribute = model.get("content").get($element.data("attrid"));
                if (currentAttribute.isDisplayable()) {
                    $(element).dcpText(currentAttribute.toData(options.line));
                } else {
                   throw new Error("Try to display a non displayable attribute "+currentAttribute.id);
                }
            })
        }
    });

});