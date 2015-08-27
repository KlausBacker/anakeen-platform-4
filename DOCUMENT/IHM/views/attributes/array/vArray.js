/*global define, console*/
define([
    'jquery',
    'underscore',
    'backbone',
    'mustache',
    'dcpDocument/views/attributes/vAttribute',
    'dcpDocument/views/attributes/array/vColumn',
    'dcpDocument/views/document/attributeTemplate',
    'dcpDocument/widgets/attributes/array/wArray'
], function ($, _, Backbone, Mustache, ViewAttribute, ViewColumn, attributeTemplate)
{
    'use strict';

    return Backbone.View.extend({
        className: "row dcpArray",
        displayLabel: true,
        customView: false,
        customRowView: false,
        events: {
            "dcparraylineadded": "addLine",
            "dcparraylineremoved": "removeLine",
            "dcparraylinemoved": "moveLine",
            "dcpattributechange .dcpArray__content__cell": "updateValue"
        },

        columnViews: {},

        initialize: function vArray_initialize(options)
        {
            if (options.displayLabel === false || this.model.getOption("labelPosition") === "none") {
                this.displayLabel = false;
            }
            this.listenTo(this.model, 'change:label', this.updateLabel);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'cleanView', this.remove);
            this.listenTo(this.model, 'change:errorMessage', this.setArrayError);
            this.listenTo(this.model, 'errorMessage', this.setError);
            this.listenTo(this.model, 'hide', this.hide);
            this.listenTo(this.model, 'show', this.show);
            this.listenTo(this.model, 'removeWidgetLine', this.removeWidgetLine);
            this.listenTo(this.model, 'addWidgetLine', this.addWidgetLine);
            this.listenTo(this.model, 'haveView', this._identifyView);
            if (options.originalView !== true) {
                if (this.model.getOption("template")) {
                    this.customView = attributeTemplate.customView(this.model);
                    if (this.model.getOption("template").match('dcpArray__table')) {
                        // Two case of custom : custom line or global custom array
                        this.customRowView = true;
                    }
                }
            }
            this.options = options;
        },

        render: function vArray_render()
        {
            // console.time("render array " + this.model.id);
            var data = this.model.toData(), scope = this, event = {prevent : false};

            this.model.trigger("beforeRender", event, { model : this.model, $el : this.$el});
            if (event.prevent) {
                return this;
            }
            data.content = _.filter(data.content, function vArray_filterCurrentElement(currentContent)
            {
                return currentContent.isDisplayable;
            });
            data.nbLines = this.getNbLines();
            data.renderOptions = this.model.getOptions();
            data.templates = {};
            data.displayLabel = this.displayLabel;
            if (this.model.getTemplates().attribute[this.model.get("type")]) {
                data.templates = this.model.getTemplates().attribute[this.model.get("type")];

            }
            if (data.nbLines === 0 && data.mode === "read") {
                data.showEmpty = this.model.getOption('showEmptyContent');
            } else {
                if (!this.customView || this.customRowView) {
                    this.columnViews=[];
                    this.model.get("content").each(function vArray_analyzeContent(currentAttr)
                    {
                        if (!currentAttr.isDisplayable()) {
                            return;
                        }
                        try {
                            if (currentAttr.getOption("attributeLabel")) {
                                data.content = _.map(data.content, function vArray_changeLabelCurrentElement(currentContent)
                                {
                                    if (currentContent.id === currentAttr.id) {
                                        currentContent.label=currentAttr.getOption("attributeLabel");
                                    }
                                    return currentContent;
                                });
                            }
                            if (currentAttr.get("isValueAttribute")) {
                                scope.columnViews[currentAttr.id] = new ViewColumn({
                                    el: scope.el,
                                    els: function vArray_findScope()
                                    {
                                        return scope.$el.find('[data-attrid="' + currentAttr.id + '"]');
                                    },
                                    originalView: true,
                                    model: currentAttr,
                                    parentElement: scope.$el
                                });
                                scope.columnViews[currentAttr.id].render();
                            }
                        } catch (e) {
                            if (window.dcp.logger) {
                                window.dcp.logger(e);
                            } else {
                                console.error(e);
                            }
                        }
                    });
                }
            }

            if (this.customView) {
                data.customTemplate = this.customView;
                data.customLineCallback = function vArray_callCustomLine(index)
                {
                    return attributeTemplate.customArrayRowView(index, scope.model, scope);
                };
            }

            try {
                if (this.customView && !this.customRowView) {
                    this.$el.append(this.customView);
                } else {
                    this.$el.dcpArray(data);
                }
            } catch (e) {
                if (window.dcp.logger) {
                    window.dcp.logger(e);
                } else {
                    console.error(e);
                }
            }

            this.$el.attr("data-attrid", this.model.id);
            // console.timeEnd("render array " + this.model.id);
            this.model.trigger("renderDone", {model: this.model, $el: this.$el});
            return this;
        },

        getNbLines: function ()
        {
            var nbLigne = this.nbLines || 0;
            this.model.get("content").each(function vArray_getCurrentLine(currentAttr)
            {
                if (currentAttr.get("attributeValue") && nbLigne < _.size(currentAttr.get("attributeValue"))) {
                    nbLigne = _.size(currentAttr.get("attributeValue"));
                }
            });
            return nbLigne;
        },

        updateLabel: function ()
        {
            this.$el.find(".dcpArray__label").text(this.model.get("label"));
        },

        /**
         *
         * @param event
         * @param options
         */
        updateValue: function vArray_UpdateValue(event, options)
        {
            var attributeModel = this.model.get("content").get(options.id);
            if (!attributeModel) {
                throw new Error("Unknown attribute " + options.id);
            }
            attributeModel.setValue(options.value, options.index);
        },

        refresh: function vArray_Refresh()
        {
            this.nbLines = this.$el.dcpArray("option", "nbLines");
            this.$el.dcpArray("destroy");
            this.render();
        },

        removeLine: function (event, options)
        {
            this.model.get("content").each(function vArray_removeLine(currentContent)
            {
                currentContent.removeIndexValue(options.line);
            });
            this.model.trigger("array", "removeLine", this.model, options.line);
        },

        removeWidgetLine: function vArray_RemoveWidgetLine(options)
        {
            this.$el.dcpArray("removeLine", options.index, {silent: true});
        },

        addWidgetLine: function vArray_addWidgetLine(options)
        {
            this.$el.dcpArray("addLine", options.index);
        },

        addLine: function vArray_AddLine(event, options)
        {
            var currentArrayView = this, customView = null;
            this.model.get("content").each(function vArray_addLineGetContent(currentContent)
            {
                var currentViewColumn;
                if (options.needAddValue || options.copyValue) {
                    currentContent.createIndexedValue(options.line, options.copyValue);
                }
                currentViewColumn = currentArrayView.columnViews[currentContent.id];
                if (currentViewColumn) {
                    customView = null;
                    if (currentContent.getOption("template")) {
                        customView = attributeTemplate.customView(currentContent,
                            function vArray_customViewInit()
                            {
                                currentViewColumn.widgetInit(
                                    $(this),
                                    currentViewColumn.getData(options.line));
                                currentViewColumn.moveValueIndex({});
                            }, {index: options.line}
                        );
                    }
                    currentViewColumn.addNewWidget(options.line, customView);
                }
            });
            this.model.trigger("array", "addLine", this.model, options.line);
        },

        moveLine: function vArray_moveLine(event, options)
        {
            this.model.get("content").each(function vArray_getMoveLineContent(currentContent)
            {
                currentContent.moveIndexValue(options.fromLine, options.toLine);
            });
            this.model.trigger("array", "moveLine", this.model, options);
        },
        getAttributeModel: function vArray_getAttributeModel(attributeId)
        {
            var docModel = this.model.getDocumentModel();
            return docModel.get('attributes').get(attributeId);
        },

        setError: function vArray_setError(event, message)
        {
            var parentId = this.model.get('parent');

            if (message) {
                this.$el.find(".dcpArray__label").addClass("has-error");
            } else {
                this.$el.find(".dcpArray__label").removeClass("has-error");
            }


            if (parentId) {
                var parentModel = this.getAttributeModel(parentId);
                if (parentModel) {
                    parentModel.trigger("errorMessage", event, message);
                }
            }
        },
        setArrayError: function vArray_setArrayError(event, message)
        {
            this.setError(event, message);
            // global error on array itself
            this.$el.dcpArray("setError", message);
        },

        hide: function vArray_hide()
        {
            this.$el.hide();
        },

        show: function vArray_show()
        {
            this.$el.show();
        },

        _identifyView: function vArray_identifyView(event)
        {
            event.haveView = true;
            //Add the pointer to the current jquery element to a list passed by the event
            event.elements = event.elements.add(this.$el);
        }
    });

});
