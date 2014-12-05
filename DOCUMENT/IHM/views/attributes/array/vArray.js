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
            "dcpattributechange .dcpArray__content__cell": "updateValue"
        },

        columnViews: {},

        initialize: function (options) {
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);

        },

        render: function () {
            // console.time("render array " + this.model.id);
            var data = this.model.toData();
            var scope = this;
            $(".dcpLoading").dcpLoading("addItem", data.content.length + 1);
            data.content = _.filter(data.content, function (currentContent) {
                return currentContent.isDisplayable;
            });
            data.nbLines = this.getNbLines();
            data.renderOptions = this.model.getOptions();
            data.templates = {};
            if (this.model.getTemplates().attribute[this.model.get("type")]) {
                data.templates = this.model.getTemplates().attribute[this.model.get("type")];
            }
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
                        TraceKit.report(e);
                        console.error(e);
                    }
                });
            }
            try {
                this.$el.dcpArray(data);
            } catch(e) {
                TraceKit.report(e);
                console.error(e);
            }


            // console.timeEnd("render array " + this.model.id);
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

        /**
         *
         * @param event
         * @param options
         */
        updateValue: function vArrayUpdateValue(event, options) {
            var attributeModel = this.model.get("content").get(options.id);
            if (!attributeModel) {
                throw new Error("Unknown attribute " + options.id);
            }
            attributeModel.setValue(options.value, options.index);
        },

        refresh: function vArrayRefresh() {
            this.nbLines = this.$el.dcpArray("option", "nbLines");
            this.$el.dcpArray("destroy");
            this.render();
        },

        removeLine: function (event, options) {
            this.model.get("content").each(function (currentContent) {
                currentContent.removeIndexValue(options.line);
            });
        },

        addLine: function vArrayAddLine(event, options) {
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
            this.model.get("content").each(function (currentContent) {
                currentContent.moveIndexValue(options.fromLine, options.toLine);

            });
        }
    });

});