/*global define*/
define(function (require, exports, module) {
        'use strict';
        var _ = require('underscore');
        var $ = require('jquery');
        var Mustache = require('mustache');


        return {

            customLineTemplate: '',

            /**
             * Get some data to complete custom attribute template
             * @param documentModel document model
             * @returns {{properties: *, attributes: {}}}
             * @public
             */
            getTemplateModelInfo: function attributeTemplateGetTemplateModelInfo(documentModel) {
                var documentData=documentModel.getDocumentData() ;
                var tplInfo = {
                    properties: documentData.properties,
                    attributes: {}
                };
                var scope = this;

                tplInfo.properties.isWriteMode = (tplInfo.properties.renderMode === "edit");
                tplInfo.properties.isReadMode = (tplInfo.properties.renderMode === "view");

                _.each(documentData.attributeValues, function (aValue, aId) {
                    tplInfo.attributes[aId] = {attributeValue: aValue};
                });
                _.each(documentData.attributeLabels, function (aValue, aId) {
                    var currentAttributeModel = documentModel.get('attributes').get(aId);
                    if (tplInfo.attributes[aId]) {
                        tplInfo.attributes[aId].label = aValue;
                    } else {
                        tplInfo.attributes[aId] = {label: aValue};
                    }
                    tplInfo.attributes[aId].id = aId;
                    tplInfo.attributes[aId].htmlContent = _.bind(scope.getCustomTemplate, scope, currentAttributeModel, false);
                    tplInfo.attributes[aId].htmlView = _.bind(scope.getCustomTemplate, scope, currentAttributeModel, true);
                    tplInfo.attributes[aId].isReadMode = (currentAttributeModel.get("mode") === "read");
                    tplInfo.attributes[aId].isWriteMode = (currentAttributeModel.get("mode") === "write");

                    if (currentAttributeModel.get("type") === "array") {

                        tplInfo.attributes[aId].rows = _.bind(scope.getArrayRowInfo, scope, currentAttributeModel);
                        tplInfo.attributes[aId].tableTools = _.bind(scope.getArrayTools, scope, currentAttributeModel);

                    }
                });
                return tplInfo;
            },

            /**
             * Get some data to complete custom attribute template
             * @returns {{properties: *, attributes: {}}}
             * @private
             */
            getTemplateInfo: function attributeTemplateGetTemplateInfo(attributeModel) {
                var tplInfo=this.getTemplateModelInfo(attributeModel.getDocumentModel());
                var attributeId = attributeModel.id;
                var extraKeys = attributeModel.getOption("templateKeys");

                tplInfo.attribute = tplInfo.attributes[attributeId];
                if (extraKeys) {
                    var copyextraKeys = _.clone(extraKeys);
                    if (copyextraKeys.attribute && copyextraKeys.attribute.rows) {
                        copyextraKeys.attribute = _.clone(extraKeys.attribute);
                        delete copyextraKeys.attribute.rows;
                    }
                    tplInfo = this._deepExtend(tplInfo, copyextraKeys);
                }
                return tplInfo;
            },

            _deepExtend: function attributeTemplate_deepExtend(target, source) {
                for (var prop in source) {
                    if (source.hasOwnProperty(prop)) {
                        if (prop in target) {
                            this._deepExtend(target[prop], source[prop]);
                        } else {
                            target[prop] = source[prop];
                        }
                    }
                }
                return target;
            },

            /**
             * Construct custom view based on template options
             * @param attrModel Attribute model
             * @param callBackView Callback to call after
             * @returns {*|HTMLElement}
             * @param config
             */
            customView: function attributeTemplateCustomView(attrModel, callBackView, config) {
                var customTpl = '<div class="dcpCustomTemplate" data-attrid="' + attrModel.id + '">' +
                    attrModel.getOption("template") + '</div>';
                var tplInfo = this.getTemplateInfo(attrModel);
                var $render;

                if (config && !_.isUndefined(config.index) && config.index >= 0) {
                    tplInfo.attribute.attributeValue=tplInfo.attribute.attributeValue[config.index];
                }
                $render = $(Mustache.render(customTpl, tplInfo));
                this.completeCustomContent($render,attrModel.getDocumentModel(),callBackView, config);



                return $render;
            },



            completeCustomContent: function attributeTemplateCompleteCustomContent($el, documentModel, callBackView, config) {

                $el.find(".dcpCustomTemplate--content").each(function () {
                    var attrId = $(this).data("attrid");
                    var displayLabel = ($(this).data("displaylabel") === true);
                    var elAttrModel = documentModel.get('attributes').get(attrId);
                    var attrContent = "NO VIEW FOR " + attrId;
                    var view = '';
                    var BackView = null;
                    var originalView = null;

                    if (elAttrModel) {

                        if (_.isFunction(callBackView)) {
                            // When called from vColumn to render widget in a cell

                            callBackView.apply($(this));
                            attrContent = '';
                        } else {
                            switch (elAttrModel.get("type")) {
                                case "array":
                                    BackView = require.apply(require, ['dcpDocument/views/attributes/array/vArray']);
                                    break;
                                case "tab":
                                    BackView = require.apply(require, ['dcpDocument/views/attributes/tab/vTabContent']);
                                    break;
                                case "frame":
                                    BackView = require.apply(require, ['dcpDocument/views/attributes/frame/vFrame']);
                                    break;
                                default:
                                    BackView = require.apply(require, ['dcpDocument/views/attributes/vAttribute']);
                            }

                            originalView = true;
                            if (elAttrModel.getOption("template")) {
                                if (config && config.useCustomAttribute) {
                                    // when use custom template in another custom template
                                    originalView = false;
                                }
                            }

                            view = new BackView({
                                model: elAttrModel,
                                originalView: originalView,
                                initializeContent:(config && config.initializeContent) || false,
                                displayLabel: displayLabel
                            });
                            attrContent = view.render().$el;
                        }
                    }
                    $(this).append(attrContent);
                });


            },

            /**
             * Information used when add new line
             * @param attributeModel
             * @param index line index
             * @private
             * @returns {{properties: *, attributes: {}}}
             */
            getLineInfo: function attributeTemplategetLineInfo(attributeModel, index) {
                var documentData = attributeModel.getDocumentModel().getDocumentData();
                var tplInfo = this.getTemplateInfo(attributeModel);
                var extraKeys = attributeModel.getOption("templateKeys");


                _.each(documentData.attributeLabels, function (aValue, aId) {
                    var currentAttributeModel = attributeModel.getDocumentModel().get('attributes').get(aId);
                    // Reset some special keys which are not allowed here
                    if (currentAttributeModel.get("type") === "array") {
                        tplInfo.attributes[aId].rows = [];
                        tplInfo.attributes[aId].tableTools = 'NO TABLE TOOL ALLOWED HERE';
                    }
                });

                tplInfo.rowTools = this.getRowTool(attributeModel);
                tplInfo.content = {};
                attributeModel.get("content").each(function (currentAttr) {
                    var aId = currentAttr.id;
                    var aLabel = currentAttr.get('label');
                    if (!currentAttr.isDisplayable()) {
                        return;
                    }

                    tplInfo.content[aId] = {};
                    tplInfo.content[aId].attributeValue = {
                        value: null, // No value for the moment. Value will be set by array view with default values
                        displayValue: ""
                    };
                    tplInfo.content[aId].label = aLabel;
                    tplInfo.content[aId].htmlContent = '<div class="dcpCustomTemplate--row dcpArray__content__cell" data-displaylabel="false" data-attrid="' + aId + '"/>';

                });

                if (extraKeys && extraKeys.attribute && extraKeys.attribute.rows) {
                    _.each(extraKeys.attribute.rows, function (extraValues, extraKey) {
                        tplInfo[extraKey] = extraValues[index];
                    });
                }

                return tplInfo;
            },

            /**
             * Extract rows template line to customLineTemplate
             * @param attrModel
             * @private
             * @returns {{attribute: {rows: Function}}}
             */
            extractRow: function attributeTemplateExtractRow(attrModel) {
                var scope = this;
                var info;

                info = {
                    attribute: {
                        rows: function () {
                            return function (text, render) {
                                scope.customLineTemplate = text.trim();
                            };
                        }
                    }
                };
                info.attributes = {};
                info.attributes[attrModel.id] = info.attributes;
                return info;
            },

            /**
             * Construct custom line (declared in vArray::render and used in wArray::_getLineContent)
             * @param index
             * @param attrModel
             * @param callerView
             * @returns {*|HTMLElement}
             */
            customArrayRowView: function attributeTemplateCustomArrayRowView(index, attrModel, callerView) {

                var $render;

                // Extract line to customLineTemplate variable
                Mustache.render(attrModel.getOption("template"), this.extractRow(attrModel));
                $render = $(Mustache.render(
                    this.customLineTemplate,
                    this.getLineInfo(attrModel, index)));

                return $render;
            },

            /**
             * Get element where custom template will be inserted (htmlContent and htmlView)
             * @param attributeModel
             * @param displayLabel
             * @private
             * @returns {string}
             */
            getCustomTemplate: function attributeTemplategetCustomTemplate(attributeModel, displayLabel) {

                return '<div class="dcpCustomTemplate--content" data-displaylabel="' + (displayLabel ? "true" : "false") + '" data-attrid="' + attributeModel.id + '"/>';

            },


            /**
             * Extract dcpArray__tools from content array template
             * @param attributeModel
             * @private
             * @returns {*}
             */
            getArrayTools: function attributeTemplateGetArrayTools(attributeModel) {
                var tpls = attributeModel.getTemplates().attribute[attributeModel.get("type")];
                if (tpls && tpls.content) {
                    return $(Mustache.render(tpls.content, {tools: true})).find(".dcpArray__tools").get(0).outerHTML;

                }
                return 'no tools';
            },

            /**
             * Extract dcpArray__content__toolCell from line array template
             * @param attributeModel
             * @private
             * @returns {*}
             */
            getRowTool: function attributeTemplateGetRowTool(attributeModel) {
                var tpls = attributeModel.getTemplates().attribute[attributeModel.get("type")];
                var tool = '';
                if (tpls && tpls.line) {
                    tool = '<div class="dcpArray__content__toolCell">' +
                    $(Mustache.render(tpls.line, {tools: true})).find(".dcpArray__content__toolCell").html() + "</div>";

                }
                return tool;
            },


            /**
             * Get data for mustache "rows" variable
             * @param attributeModel
             * @private
             * @returns {Array}
             */
            getArrayRowInfo: function attributeTemplategetArrayRowInfo(attributeModel) {

                var rows = [];
                var line = this.getRowTool(attributeModel);

                attributeModel.get("content").each(function (currentAttr) {
                    var values;
                    var aId = currentAttr.id;
                    var aLabel = currentAttr.get('label');
                    if (!currentAttr.isDisplayable()) {
                        return;
                    }

                    values = currentAttr.get('attributeValue');
                    _.each(values, function (singleValue, index) {
                        if (_.isUndefined(rows[index])) {
                            rows[index] = {content: {}};
                        }

                        rows[index].content[aId] = {
                            label: aLabel,
                            attributeValue: singleValue,
                            htmlContent: '<div class="dcpCustomTemplate--row dcpArray__content__cell"  data-attrid="' + currentAttr.id + '"/>'
                        };

                        rows[index].rowTools = line;

                    });
                });

                return rows;
            }
        };

    }
);