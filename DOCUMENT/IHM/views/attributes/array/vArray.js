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
            "dcparraylineadded": "addLine",
            "dcparraylineremoved": "removeLine",
            "dcparraylinemoved": "moveLine",
            "dcpattributechange .dcpArray__content__cell": "updateValue",
            "dcpattributechangeattrsvalue .dcpAttribute__contentWrapper": "changeAttributesValue"
        },

        columnViews: {},

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
            if (data.nbLines === 0 && data.mode === "read") {
                data.showEmpty = this.model.getOption('showEmptyContent');
            } else {
                this.model.get("content").each(function (currentAttr) {
                    if (!currentAttr.isDisplayable()) {
                        return;
                    }
                    try {
                        if (currentAttr.get("valueAttribute")) {

                            scope.columnViews[currentAttr.id] = new ViewColumn({
                                el: scope.el,
                                els: function () {
                                    return scope.$el.find('[data-attrid="' + currentAttr.id + '"]');
                                },
                                model: currentAttr,
                                parentElement: scope.$el});
                            scope.columnViews[currentAttr.id].render();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            }
            this.$el.dcpArray(data);

            console.timeEnd("render array " + this.model.id);
            return this;
        },
        /**
         * Modify several attribute
         * @param event event object
         * @param data values {id: menuId, visibility: "disabled", "visible", "hidden"}
         * @param index the index which comes from modifcation action
         */
        changeAttributesValue: function (event, dataItem, valueIndex) {
            var scope = this;
            _.each(dataItem.values, function (val, aid) {
                if (typeof val === "object") {
                    console.log("changeAttributesValue", aid, scope.model, aid);
                    console.log("changeAttributesValue IDX", valueIndex);
                    var attrModel = scope.model.get("documentModel").get('attributes').get(aid);
                    if (attrModel) {

                        console.log("YEAH", attrModel.id, val, valueIndex);
                        if (attrModel.hasMultipleOption()) {
                            attrModel.addValue({value: val.value, displayValue: val.displayValue}, valueIndex);
                        } else {
                            console.log("SETTO", {value: val.value, displayValue: val.displayValue}, valueIndex);
                            attrModel.setValue( {value: val.value, displayValue: val.displayValue}, valueIndex);
                        }

                    }
                }
            });
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

        /**
         *
         * @param event
         * @param options
         */
        updateValue: function (event, options) {

            console.log("array :: view has receive change", options);
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
                currentContent.removeIndexValue(options.line);
            });
            //  this.refresh();
        },
        addLine: function (event, options) {
            var scope = this;
            this.model.get("content").each(function (currentContent) {
                if (options.needAddValue || options.copyValue) {
                    currentContent.addIndexValue(options.line, options.copyValue);
                }

                var vColumn = scope.columnViews[currentContent.id];
                if (vColumn) {
                    vColumn.addNewWidget(options.line);
                }
            });
        },
        moveLine: function moveLine(event, options) {
            var scope = this;
            this.model.get("content").each(function (currentContent) {
                currentContent.moveIndexValue(options.fromLine, options.toLine);

            });
        }
    });

});