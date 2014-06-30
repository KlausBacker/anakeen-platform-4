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
             "dcparraylineremoved" : "removeLine",
             "dcpattributechange .dcpArray__content__cell" : "updateValue"
        },

        initialize : function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render : function () {
            console.time("render array " + this.model.id);
            var data = this.model.toData();

            $(".dcpLoading").dcpLoading("addItem",data.content.length +1 );
            data.content = _.filter(data.content, function(currentContent) {
                return currentContent.isDisplayable;
            });
            data.nbLines = this.getNbLines();
            this.$el.dcpArray(data);
            console.timeEnd("render array " + this.model.id);
            return this;
        },

        getNbLines : function() {
            var nbLigne = this.nbLines || 0;
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
            console.log("IN ARRAY update value");
            var attributeModel = this.model.get("content").get(options.id);
            if (!attributeModel) {
                throw new Error("Unknown attribute " + options.id);
            }
            attributeModel.setValue(options.value, options.index);
        },

        initWidget : function(event, options) {
            var model = this.model;
            var scope=this;
            options.element.find(".dcpArray__content__cell").each(function(index, element) {
                var $element = $(element), currentAttribute = model.get("content").get($element.data("attrid"));
                if (currentAttribute.isDisplayable()) {
                    scope.dcpArraySwitch(currentAttribute.attributes.type,
                        $(element),
                        currentAttribute.toData(options.line));
                } else {
                   throw new Error("Try to display a non displayable attribute "+currentAttribute.id);
                }
            });
        },

        refresh : function() {
            this.nbLines = this.$el.dcpArray("option", "nbLines");
            this.$el.dcpArray("destroy");
            this.render();
        },

        removeLine : function(event, options) {
            this.model.get("content").each(function(currentContent) {
               currentContent.removeLine(options.line);
            });
            this.refresh();
        },

        dcpArraySwitch: function (attrType, $element, method) {
            switch (attrType) {
                case "text" :
                    return $element.dcpText(method);
                case "account" :
                case "docid" :
                    return $element.dcpDocid(method);
                default:
                    return $element.dcpText(method);
            }
        }
    });

});