/*global define*/
define(function (require, exports, module) {
        'use strict';
        var _ = require('underscore');
        var $ = require('jquery');
        var Mustache = require('mustache');


        return {

            /**
             * Get some data to complete custom attribute template
             * @returns {{properties: *, attributes: {}}}
             */
            getTemplateInfo: function attributeTemplateGetTemplateInfo(attributeModel) {
                var attributeId = attributeModel.id;
                var documentData = attributeModel.getDocumentModel().getDocumentData();
                var tplInfo = {
                    properties: documentData.properties,
                    attributes: {}
                };
                var createContentFunction = this.createAttributeView;
                var createArrayRow = this.createArrayRow;
                _.each(documentData.attributeValues, function (aValue, aId) {
                    tplInfo.attributes[aId] = {attributeValue: aValue};
                });
                _.each(documentData.attributeLabels, function (aValue, aId) {
                    var currentAttributeModel = attributeModel.getDocumentModel().get('attributes').get(aId);
                    if (tplInfo.attributes[aId]) {
                        tplInfo.attributes[aId].label = aValue;
                    } else {
                        tplInfo.attributes[aId] = {label: aValue};
                    }
                    tplInfo.attributes[aId].id = aId;
                    tplInfo.attributes[aId].htmlContent = _.bind(createContentFunction, this, currentAttributeModel, false);
                    tplInfo.attributes[aId].htmlView = _.bind(createContentFunction, this, currentAttributeModel, true);
                    if (currentAttributeModel.get("type") === "array") {
                        /*tplInfo.attributes[aId].rows = function () {
                         return function (text, render) {
                         console.log("Hello");
                         };
                         };*/
                        tplInfo.attributes[aId].rows = _.bind(createArrayRow, this, currentAttributeModel);
                    }
                });
                tplInfo.attribute = tplInfo.attributes[attributeId];
                return tplInfo;
            },

            customView: function (attrModel, callBackView, callerView) {

                var customTpl = '<div class="dcpCustomTemplate" data-attrid="' + attrModel.id + '">' +
                    attrModel.getOption("template") + '</div>';

                var $render = $(Mustache.render(
                    customTpl,
                    this.getTemplateInfo(attrModel)));

                $render.find(".dcpCustomTemplate--content").each(function () {
                    var attrId = $(this).data("attrid");
                    var displayLabel = ($(this).data("displaylabel") === true);
                    var elAttrModel = attrModel.getDocumentModel().get('attributes').get(attrId);
                    var attrContent = "NO VIEW FOR " + attrId;
                    var view = '';
                    var BackView = null;
                    var parentAttributeId = null;
                    var parentAttribute = null;
                    if (elAttrModel) {
                        if (elAttrModel.get("type") === "array") {
                            BackView = require('views/attributes/array/vArray');
                            view = new BackView({model: elAttrModel, displayLabel: displayLabel});
                            attrContent = view.render().$el;
                        } else {
                            if (_.isFunction(callBackView)) {
                                // When called from vColumn to render widget in a cell
                                callBackView.apply($(this));
                                attrContent = '';
                            } else {
                                if (elAttrModel.get("type") === "tab") {
                                    BackView = require('views/attributes/tab/vTabContent');
                                    view = new BackView({model: elAttrModel, displayLabel: displayLabel});
                                    attrContent = view.render().$el;
                                } else if (elAttrModel.get("type") === "frame") {
                                    BackView = require('views/attributes/frame/vFrame');
                                    view = new BackView({model: elAttrModel, displayLabel: displayLabel});
                                    attrContent = view.render().$el;
                                } else {
                                    BackView = require('views/attributes/vAttribute');
                                    view = new BackView({model: elAttrModel, displayLabel: displayLabel});
                                    attrContent = view.render().$el;
                                }
                            }
                        }
                    }
                    $(this).append(attrContent);
                });


                $render.find(".dcpCustomTemplate--row").each(function () {
                    var attrId = $(this).data("attrid");
                    var index = $(this).data("rowindex");
                    var elAttrModel = attrModel.getDocumentModel().get('attributes').get(attrId);
                    var columnView = callerView.columnViews[attrId];
                    var attrContent = "NO VIEW FOR " + attrId;

                    if (elAttrModel && columnView) {
                        // Need to add system class for array widget
                        $(this).closest("tr").addClass("dcpArray__content__line");
                        columnView.widgetInit(
                            $(this),
                            columnView.getData(index));

                        attrContent = '';
                    }
                    $(this).append(attrContent);
                });
                return $render;
            },
            createAttributeView: function attributeTemplateCreateAttributeView(attributeModel, displayLabel) {

                return '<div class="dcpCustomTemplate--content" data-displaylabel="' + (displayLabel ? "true" : "false") + '" data-attrid="' + attributeModel.id + '"/>';

            },
            createArrayRow: function attributeTemplateCreateArrayRow(attributeModel) {

                var rows = [];
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
                            htmlContent: '<div class="dcpCustomTemplate--row dcpArray__content__cell" data-rowindex="' + index + '" data-attrid="' + currentAttr.id + '"/>'
                        };
                    });
                });
                return rows;
            }
        };

    }
);