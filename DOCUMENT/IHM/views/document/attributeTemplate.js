/*global define*/
define(function (require, exports, module)
    {
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
            getTemplateModelInfo: function attributeTemplateGetTemplateModelInfo(documentModel)
            {
                var documentData = documentModel.getDocumentData();
                var templateInfo = {
                    properties: documentData.properties,
                    attributes: {}
                };
                var currentTemplate = this;

                templateInfo.properties.isWriteMode = (templateInfo.properties.renderMode === "edit");
                templateInfo.properties.isReadMode = (templateInfo.properties.renderMode === "view");

                _.each(documentData.attributeValues, function associateValue(attributeValue, attributeId)
                {
                    templateInfo.attributes[attributeId] = {attributeValue: attributeValue};
                });
                _.each(documentData.attributeLabels, function (attributeLabel, attributeId)
                {
                    var currentAttributeModel = documentModel.get('attributes').get(attributeId);
                    if (templateInfo.attributes[attributeId]) {
                        templateInfo.attributes[attributeId].label = attributeLabel;
                    } else {
                        templateInfo.attributes[attributeId] = {label: attributeLabel};
                    }
                    templateInfo.attributes[attributeId].id = attributeId;
                    templateInfo.attributes[attributeId].htmlContent = _.bind(currentTemplate.getCustomTemplate, currentTemplate, currentAttributeModel, false);
                    templateInfo.attributes[attributeId].htmlView = _.bind(currentTemplate.getCustomTemplate, currentTemplate, currentAttributeModel, true);
                    templateInfo.attributes[attributeId].isReadMode = (currentAttributeModel.get("mode") === "read");
                    templateInfo.attributes[attributeId].isWriteMode = (currentAttributeModel.get("mode") === "write");

                    if (currentAttributeModel.get("type") === "array") {
                        templateInfo.attributes[attributeId].toolsEnabled = templateInfo.attributes[attributeId].isWriteMode &&
                            (currentAttributeModel.get("visibility") !== "U") &&
                        (currentAttributeModel.getOption("rowAddDisable") !== true ||
                         currentAttributeModel.getOption("rowDelDisable") !== true||
                         currentAttributeModel.getOption("rowMoveDisable") !== true);
                        templateInfo.attributes[attributeId].rows = _.bind(currentTemplate.getArrayRowInfo, currentTemplate, currentAttributeModel);
                        templateInfo.attributes[attributeId].tableTools = _.bind(currentTemplate.getArrayTools, currentTemplate, currentAttributeModel);
                    }
                });
                return templateInfo;
            },

            /**
             * Get some data to complete custom attribute template
             * @returns {{properties: *, attributes: {}}}
             * @private
             */
            getTemplateInfo: function attributeTemplateGetTemplateInfo(attributeModel)
            {
                var templateInfo = this.getTemplateModelInfo(attributeModel.getDocumentModel());
                var attributeId = attributeModel.id;
                var extraKeys = attributeModel.getOption("templateKeys");

                templateInfo.attribute = templateInfo.attributes[attributeId];
                if (extraKeys) {
                    var copyextraKeys = _.clone(extraKeys);
                    if (copyextraKeys.attribute && copyextraKeys.attribute.rows) {
                        copyextraKeys.attribute = _.clone(extraKeys.attribute);
                        delete copyextraKeys.attribute.rows;
                    }
                    templateInfo = this._deepExtend(templateInfo, copyextraKeys);
                }
                return templateInfo;
            },

            /**
             * Recursively extend data
             *
             * @param target
             * @param source
             * @returns {*}
             */
            _deepExtend: function attributeTemplate_deepExtend(target, source)
            {
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
            customView: function attributeTemplateCustomView(attrModel, callBackView, config)
            {
                var customTemplate = '<div class="dcpCustomTemplate" data-attrid="' + attrModel.id + '">' +
                    attrModel.getOption("template") + '</div>';
                var templateInfo = this.getTemplateInfo(attrModel);
                var $render;

                if (config && !_.isUndefined(config.index) && config.index >= 0) {
                    templateInfo.attribute.attributeValue = templateInfo.attribute.attributeValue[config.index];
                }
                $render = $(Mustache.render(customTemplate, templateInfo));
                this.completeCustomContent($render, attrModel.getDocumentModel(), callBackView, config);
                return $render;
            },


            /**
             *
             * @param $el
             * @param documentModel
             * @param callBackView
             * @param config
             */
            completeCustomContent: function attributeTemplateCompleteCustomContent($el, documentModel, callBackView, config)
            {
                $el.find(".dcpCustomTemplate--content").each(function ()
                {
                    var attrId = $(this).data("attrid"),
                        displayLabel = ($(this).data("displaylabel") === true),
                        currentAttributeModel = documentModel.get('attributes').get(attrId),
                        attrContent = "NO VIEW FOR " + attrId,
                        view = '',
                        BackView = null,
                        originalView = null;

                    if (currentAttributeModel) {

                        if (_.isFunction(callBackView)) {
                            // When called from vColumn to render widget in a cell
                            callBackView.apply($(this));
                            attrContent = '';
                        } else {
                            switch (currentAttributeModel.get("type")) {
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
                            if (currentAttributeModel.getOption("template")) {
                                if (config && config.useCustomAttribute) {
                                    // when use custom template in another custom template
                                    originalView = false;
                                }
                            }

                            view = new BackView({
                                model: currentAttributeModel,
                                originalView: originalView,
                                initializeContent: (config && config.initializeContent) || false,
                                displayLabel: displayLabel,
                                secondView: true
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
            getLineInfo: function attributeTemplategetLineInfo(attributeModel, index)
            {
                var documentData = attributeModel.getDocumentModel().getDocumentData();
                var templateInfo = this.getTemplateInfo(attributeModel);
                var extraKeys = attributeModel.getOption("templateKeys");


                _.each(documentData.attributeLabels, function attributeTemplate_eachLabel(attributeLabel, attributeId)
                {
                    var currentAttributeModel = attributeModel.getDocumentModel().get('attributes').get(attributeId);
                    // Reset some special keys which are not allowed here
                    if (currentAttributeModel.get("type") === "array") {
                        templateInfo.attributes[attributeId].rows = [];
                        templateInfo.attributes[attributeId].tableTools = 'NO TABLE TOOL ALLOWED HERE';
                    }
                });

                templateInfo.rowTools = this.getRowTool(attributeModel);
                templateInfo.content = {};
                attributeModel.get("content").each(function attributeTemplate_eachContent(currentAttribute)
                {
                    var attributeId = currentAttribute.id;
                    var attributeLabel = currentAttribute.get('label');
                    if (!currentAttribute.isDisplayable()) {
                        return;
                    }

                    templateInfo.content[attributeId] = {};
                    templateInfo.content[attributeId].attributeValue = {
                        value: null, // No value for the moment. Value will be set by array view with default values
                        displayValue: ""
                    };
                    templateInfo.content[attributeId].label = attributeLabel;
                    templateInfo.content[attributeId].htmlContent = '<div class="dcpCustomTemplate--row dcpArray__content__cell" data-displaylabel="false" data-attrid="' + attributeId + '"/>';

                });

                if (extraKeys && extraKeys.attribute && extraKeys.attribute.rows) {
                    _.each(extraKeys.attribute.rows, function (extraValues, extraKey)
                    {
                        templateInfo[extraKey] = extraValues[index];
                    });
                }

                return templateInfo;
            },

            /**
             * Extract rows template line to customLineTemplate
             * @param attrModel
             * @private
             * @returns {{attribute: {rows: Function}}}
             */
            extractRow: function attributeTemplateExtractRow(attrModel)
            {
                var scope = this;
                var info;

                info = {
                    attribute: {
                        rows: function ()
                        {
                            return function attributeTemplate_getRowContent(text, render)
                            {
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
            customArrayRowView: function attributeTemplateCustomArrayRowView(index, attrModel, callerView)
            {

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
            getCustomTemplate: function attributeTemplategetCustomTemplate(attributeModel, displayLabel)
            {
                return '<div class="dcpCustomTemplate--content" data-displaylabel="' + (displayLabel ? "true" : "false") + '" data-attrid="' + attributeModel.id + '"/>';
            },

            /**
             * Extract dcpArray__tools from content array template
             * @param attributeModel
             * @private
             * @returns {*}
             */
            getArrayTools: function attributeTemplateGetArrayTools(attributeModel)
            {
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
            getRowTool: function attributeTemplateGetRowTool(attributeModel)
            {
                var templates = attributeModel.getTemplates().attribute[attributeModel.get("type")];
                var tool = '';
                if (templates && templates.line) {
                    tool = $(Mustache.render(templates.line, {tools: true})).find(".dcpArray__toolCell").html() ;
                }
                return tool;
            },


            /**
             * Get data for mustache "rows" variable
             * @param attributeModel
             * @private
             * @returns {Array}
             */
            getArrayRowInfo: function attributeTemplategetArrayRowInfo(attributeModel)
            {

                var rows = [];
                var line = this.getRowTool(attributeModel);

                attributeModel.get("content").each(function attributeTemplate_eachContent(currentAttribute)
                {
                    var values;
                    var attributeId = currentAttribute.id;
                    var attributeLabel = currentAttribute.get('label');
                    if (!currentAttribute.isDisplayable()) {
                        return;
                    }

                    values = currentAttribute.get('attributeValue');
                    _.each(values, function (singleValue, index)
                    {
                        if (_.isUndefined(rows[index])) {
                            rows[index] = {content: {}};
                        }

                        rows[index].content[attributeId] = {
                            label: attributeLabel,
                            attributeValue: singleValue,
                            htmlContent: '<div class="dcpCustomTemplate--row dcpArray__content__cell"  data-attrid="' + currentAttribute.id + '"/>'
                        };

                        rows[index].rowTools = line;

                    });
                });

                return rows;
            }
        };

    }
);