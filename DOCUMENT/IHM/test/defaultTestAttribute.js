/*global require, describe, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/

var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

define([
    'underscore',
    'jquery',
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'models/document',
    'views/document/vDocument'
], function (_, $, template, ModelDocument, ViewDocument) {
    "use strict";

    var testAttribute,
        generateFamilyStructure,
        generateDocumentContent;

    template = JSON.parse(template);

    if (template.success !== true) {
        throw new Error("Unable to parse template " + template.error || "");
    }

    template = template.content;

    window.dcp = window.dcp || {};

    window.dcp.templates = window.dcp.templates || template;

    generateFamilyStructure = function (localeAttrId, attrDef) {
        var attrStruct, struct = {
            structure: {
                'test_f_frame': {
                    id: 'test_f_frame',
                    label: 'frame',
                    multiple: false,
                    options: [],
                    type: 'frame',
                    visibility: 'W',
                    content: {

                    }
                }
            }
        };
        if (localeAttrId) {
            attrStruct = {
                id: localeAttrId,
                label: attrDef.label || ("label of " + localeAttrId),
                label_old: localeAttrId,
                multiple: false,
                options: attrDef.options || [],
                type: attrDef.type,
                visibility: attrDef.visibility || 'W'
            };

            struct.structure.test_f_frame.content[localeAttrId] = _.extend(attrStruct, attrDef);
        }
        return struct;
    };

    generateDocumentContent = function (localeAttrId, value) {
        var data = {};

        value = _.clone(value);

        if (localeAttrId) {
            data[localeAttrId] = value;
        }

        return data;
    };

    testAttribute = function (config) {

        var title = config.title;
        var attributeDefinition = config.attribute;
        var initialValue = config.initialValue;
        var options = config.options || {};
        var otherValue = config.otherValue;

        var modelDocument, currentSandbox, localAttrId, getSandbox = function () {
            return currentSandbox;
        }, findWidgetName = function ($element) {
            return _.find(_.keys($element.data()), function (currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        };

        var familyStructure = null;
        beforeEach(function () {
            var localId = _.uniqueId("Document");
            localAttrId = _.uniqueId(attributeDefinition.type);
            currentSandbox = $("<div></div>");
            var $renderZone = $("#render");
            if ($renderZone.length === 0) {
                $renderZone = $("body");
            }
            $renderZone.append(currentSandbox);
            //currentSandbox = setFixtures(sandbox());
            familyStructure = options.familyContent || generateFamilyStructure(localAttrId, attributeDefinition);

            modelDocument = new ModelDocument(
                {},
                {
                    properties: {id: localId, title: title + "_" + localAttrId, fromname: localId, fromtitle: localId},
                    menus: [],
                    family: familyStructure,
                    locale: options.locale || "fr_FR",
                    renderMode: options.renderMode || "view",
                    attributes: options.attributes || generateDocumentContent(localAttrId, initialValue)
                }
            );
        });

        describe(title, function () {

            it("dom", function () {
                var $sandBox = getSandbox(), view;
                view = new ViewDocument({model: modelDocument, el: $sandBox});
                view.render();
                expect(".dcpAttribute[data-attrid=" + localAttrId + "]").toExist();
                expect(".dcpAttribute__label[data-attrid=" + localAttrId + "]").toExist();
                expect(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]").toExist();
                expect(".dcpAttribute__contentWrapper--" + attributeDefinition.type + "[data-attrid=" + localAttrId + "]").toExist();
            });

            it("label", function () {
                var $sandBox = getSandbox(), view, newLabel = _.uniqueId(title);
                var iniLabel = familyStructure.structure.test_f_frame.content[localAttrId].label;

                view = new ViewDocument({model: modelDocument, el: $sandBox});
                view.render();
                expect(".dcpAttribute__label[data-attrid=" + localAttrId + "]").toHaveText(iniLabel);
                modelDocument.get("attributes").get(localAttrId).set("label", newLabel);
                expect(".dcpAttribute__label[data-attrid=" + localAttrId + "]").toHaveText(newLabel);
            });

            describe("Value", function () {
                /* Check the initialValue on the widget and the attribute model*/
                it("InitialValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("value");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    widgetValue = widget.getValue();
                    if (_.isArray(initialValue)) {
                        // verify each values
                        _.each(initialValue, function (v, k) {
                            expect(modelValue[k].value).toEqual(v.value);
                            expect(widgetValue[k].value).toEqual(v.value);
                        });

                    } else {
                        expect(modelValue.value).toEqual(initialValue.value);
                        expect(widgetValue.value).toEqual(initialValue.value);
                    }
                });
                /* Check the setValue method of the attribute model*/
                it("ModelSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    modelDocument.get("attributes").get(localAttrId).set("value", otherValue);
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("value");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    widgetValue = widget.getValue();

                    if (_.isArray(otherValue)) {
                        // verify each values
                        _.each(otherValue, function (v, k) {
                            expect(modelValue[k].value).toEqual(v.value);
                            expect(widgetValue[k].value).toEqual(v.value);
                        });
                    } else {
                        expect(otherValue.value).toEqual(widgetValue.value);
                        expect(modelValue.value).toEqual(widgetValue.value);
                    }
                });
                /* Check the setValue method of the widget*/
                it("WidgetSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    widget.setValue(otherValue);
                    widgetValue = widget.getValue();
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("value");
                    if (_.isArray(initialValue)) {
                        // verify each values
                        _.each(otherValue, function (v, k) {
                            expect(modelValue[k].value).toEqual(v.value);
                            expect(widgetValue[k].value).toEqual(v.value);
                        });
                    } else {
                        expect(otherValue.value).toEqual(modelValue.value);
                        expect(widgetValue.value).toEqual(modelValue.value);
                    }
                });
            });

            describe("Event No Change", function () {
                /* Check the dcpattributechange event of the widget */
                it("WidgetEventFromSetInitialValue", function () {
                    var $sandBox = getSandbox(), view, $content, widget, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    $content.on("dcpattributechange", change);

                    widget.setValue(initialValue);

                    expect(change.calls.count()).toEqual(0);
                });
                it("WidgetEventFromModelSetInitial", function () {
                    var $sandBox = getSandbox(), view, $content,  change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    $content.on("dcpattributechange", change);
                    modelDocument.get("attributes").get(localAttrId).set("value", initialValue);

                    expect(change.calls.count()).toEqual(0);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromWidgetSetInitialValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    widget.setValue(initialValue);

                    expect(change.calls.count()).toEqual(0);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromModelSetInitial", function () {
                    var $sandBox = getSandbox(), view, modelAttribute, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    modelAttribute.set("value", initialValue);
                    expect(change.calls.count()).toEqual(0);
                });
            });
            describe("Event Change", function () {
                /* Check the dcpattributechange event of the widget */
                it("WidgetEventFromSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, widget, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    $content.on("dcpattributechange", change);
                    widget.setValue(otherValue);

                    expect(change.calls.count()).toEqual(1);
                });
                it("WidgetEventFromModelSet", function () {
                    var $sandBox = getSandbox(), view, $content, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    $content.on("dcpattributechange", change);
                    modelDocument.get("attributes").get(localAttrId).set("value", otherValue);

                    expect(change.calls.count()).toEqual(1);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromWidgetSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    widget.setValue(otherValue);

                    expect(change.calls.count()).toEqual(1);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromModelSet", function () {
                    var $sandBox = getSandbox(), view, modelAttribute, change;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    modelAttribute.set("value", otherValue);
                    expect(change.calls.count()).toEqual(1);
                });
            });

        });

    };

    return testAttribute;
});