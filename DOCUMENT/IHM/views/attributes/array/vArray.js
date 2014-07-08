/*global define*/
define([
    'underscore',
    'backbone',
    'mustache',
    'views/attributes/vAttribute',
    'views/attributes/array/vColumn',
    'widgets/attributes/array/wArray'
], function (_, Backbone, Mustache, ViewAttribute, ViewColumn) {
    'use strict';

    return Backbone.View.extend({

        events: {
            "dcparraylineadded": "initWidget",
            "dcparraylineremoved": "removeLine",
            "dcpattributechange .dcpArray__content__cell": "updateValue"
        },

        initialize: function () {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
        },

        render: function () {
            console.time("render array " + this.model.id);
            var data = this.model.toData();
            var scope = this;
            $(".dcpLoading").dcpLoading("addItem", data.content.length + 1);
            data.content = _.filter(data.content, function (currentContent) {
                return currentContent.isDisplayable;
            });
            data.nbLines = this.getNbLines();
            this.$el.dcpArray(data);

            this.model.get("content").each(function (currentAttr) {

                if (!currentAttr.isDisplayable()) {
                    return;
                }

                try {
                    if (currentAttr.get("valueAttribute")) {

                        var viewA = new ViewColumn({
                            el: scope.el,
                            $els: scope.$el.find('[data-attrid="' + currentAttr.id + '"]'),
                            model: currentAttr,
                            parentElement: scope.$el});
                        viewA.render();

                    }

                } catch (e) {
                    console.error(e);
                }
            });
            console.timeEnd("render array " + this.model.id);
            return this;
        },

        getNbLines: function () {
            var nbLigne = this.nbLines || 0;
            this.model.get("content").each(function (currentAttr) {
                if (currentAttr.get("value") && nbLigne < _.size(currentAttr.get("value"))) {
                    nbLigne = _.size(currentAttr.get("value"));
                }
            });
            return nbLigne;
        },

        updateLabel: function () {
            this.$el.find(".dcpFrame__label").text(this.model.get("label"));
        },

        updateValue: function (event, options) {
            console.log("IN ARRAY update value", options);

            var attributeModel = this.model.get("content").get(options.id);
            if (!attributeModel) {
                throw new Error("Unknown attribute " + options.id);
            }
            attributeModel.setValue(options.value, options.index);
        },



        refresh: function () {
            this.nbLines = this.$el.dcpArray("option", "nbLines");
            this.$el.dcpArray("destroy");
            this.render();
        },

        removeLine: function (event, options) {
            this.model.get("content").each(function (currentContent) {
                currentContent.removeLine(options.line);
            });
            this.refresh();
        },

        getWidgetClass: function (type) {
            switch (type) {
                case "text" :
                    return $.fn.dcpText;
                case "int" :
                    return $.fn.dcpInt;
                case "double" :
                    return $.fn.dcpDouble;
                case "account" :
                case "docid" :
                    return $.fn.dcpDocid;
                default:
                    return $.fn.dcpText;
            }
        }
    });

});