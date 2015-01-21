/*global require, ,describe, define, beforeEach, setFixtures, expect, it, sandbox, spyOnEvent, jasmine, afterEach*/

// Get the template from guest if you are in karma mode (non authentified)
var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

/**
 * Default test for attributes
 *
 * Mock a simplified document (one frame and the tested attributes) and launch standard test on it
 * This test is an abstract (see the specialized one to use it)
 */
define([
    'underscore',
    'jquery',
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'models/mDocument',
    'views/document/vDocument'
], function (_, $, template, ModelDocument, ViewDocument) {
    "use strict";

    var testAttribute,
        generateVisibility,
        generateFamilyStructure,
        generateDocumentContent;

    // Get the template in JSON form and set it in the global variable
    template = JSON.parse(template);

    if (template.success !== true) {
        throw new Error("Unable to parse template " + template.error || "");
    }
    template = template.content;
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || template;

    // Mock family definition
    generateFamilyStructure = function (localeAttrId, attrDef, renderMode, value) {
        var structure = [], secondStruct, attrStruct = {
            "id" :           "test_f_frame",
            "visibility" :   "W",
            "label" :        "frame",
            "type" :         "frame",
            "logicalOrder" : 0,
            "multiple" :     false,
            "options" :      [],
            "renderMode" :   renderMode,
            "content" :      {}
        };

        structure.push(attrStruct);

        if (localeAttrId) {
            value = _.clone(value);
            secondStruct = {
                "id" :           localeAttrId,
                "visibility" : attrDef.visibility || 'W',
                "label" : attrDef.label || ("label of " + localeAttrId),
                "label_old" :    localeAttrId,
                "type" :         attrDef.type,
                "logicalOrder" : 0,
                "multiple" :     false,
                "options" : attrDef.options || [],
                "renderMode" : renderMode,
                "content" :      {},
                "attributeValue" : value,
                "parent" : "test_f_frame"
            };
            secondStruct = _.extend(secondStruct, attrDef);

            attrStruct.content[localeAttrId] = _.extend(secondStruct, attrDef);
            structure.push(secondStruct);
        }
        return structure;
    };

    //Mock current visibility conf
    generateVisibility = function (localAttrId, attrDef) {
        var values = {
            'test_f_frame' : 'W'
        };
        values[localAttrId] = attrDef.visibility || 'W';
        return values;
    };

    // Clone the value to avoid cross modification between test
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
        var renderOptions = config.renderOptions || {};
        var familyStructure;
        var modelDocument, currentSandbox, localAttrId, getSandbox = function () {
            return currentSandbox;
        }, findWidgetName = function ($element) {
            return _.find(_.keys($element.data()), function (currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        };

        beforeEach(function () {
            var localId = _.uniqueId("Document"), $renderZone = $("#render");
            localAttrId = _.uniqueId(attributeDefinition.type);

            if (window.location.hash === "#displayDom") {
                currentSandbox = $("<div></div>");
                if ($renderZone.length === 0) {
                    $renderZone = $("body");
                }
                $renderZone.append(currentSandbox);
            } else {
                currentSandbox = setFixtures(sandbox());
            }

            familyStructure = generateFamilyStructure(localAttrId, attributeDefinition, options.renderMode, initialValue);

            //Generate mock model to test interaction between model, view and widget
            modelDocument = new ModelDocument(
                {
                    properties :    {
                        id :       localId,
                        title : title + "_" + localId,
                        fromname : localId,
                        family :   {
                            title : localId
                        }
                    },
                    menus :         [],
                    locale : options.locale || "fr_FR",
                    renderMode : options.renderMode || "view",
                    attributes : options.attributes || familyStructure,
                    renderOptions : renderOptions
                }
            );
        });

        afterEach(function () {
            //modelDocument.trigger("destroy");
        });

        describe(title, function () {

            it("dom", function () {
                var $sandBox = getSandbox(), view;
                view = new ViewDocument({model : modelDocument, el : $sandBox});
                view.render();
                expect($sandBox.find(".dcpAttribute[data-attrid=" + localAttrId + "]")).toExist();
                expect($sandBox.find(".dcpAttribute__label[data-attrid=" + localAttrId + "]")).toExist();
                expect($sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]")).toExist();
                expect($sandBox.find(".dcpAttribute__content--" + attributeDefinition.type + "[data-attrid=" + localAttrId + "]")).toExist();
            });

            it("label", function () {
                var $sandBox = getSandbox(), view, newLabel = _.uniqueId(title);
                var iniLabel = familyStructure[1].label;

                view = new ViewDocument({model : modelDocument, el : $sandBox});
                view.render();
                expect($sandBox.find(".dcpAttribute__label.dcpLabel[data-attrid=" + localAttrId + "]")).toHaveText(iniLabel);
                modelDocument.get("attributes").get(localAttrId).set("label", newLabel);
                expect($sandBox.find(".dcpAttribute__label[data-attrid=" + localAttrId + "]")).toHaveText(newLabel);
            });

            describe("Value", function () {
                /* Check the initialValue on the widget and the attribute model*/
                it("InitialValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("attributeValue");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
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
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    modelDocument.get("attributes").get(localAttrId).set("attributeValue", otherValue);
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("attributeValue");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
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
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    widget.setValue(otherValue);
                    widgetValue = widget.getValue();
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("attributeValue");
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
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    $content.on("dcpattributechange", change);

                    widget.setValue(initialValue);

                    expect(change.calls.count()).toEqual(0);
                });
                it("WidgetEventFromModelSetInitial", function () {
                    var $sandBox = getSandbox(), view, $content, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    $content.on("dcpattributechange", change);
                    modelDocument.get("attributes").get(localAttrId).set("attributeValue", initialValue);

                    expect(change.calls.count()).toEqual(0);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromWidgetSetInitialValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    widget.setValue(initialValue);

                    expect(change.calls.count()).toEqual(0);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromModelSetInitial", function () {
                    var $sandBox = getSandbox(), view, modelAttribute, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    modelAttribute.set("attributeValue", initialValue);
                    expect(change.calls.count()).toEqual(0);
                });
            });
            describe("Event Change", function () {
                /* Check the dcpattributechange event of the widget */
                it("WidgetEventFromSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, widget, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    $content.on("dcpattributechange", change);
                    widget.setValue(otherValue);

                    expect(change.calls.count()).toEqual(1);
                });
                it("WidgetEventFromModelSet", function () {
                    var $sandBox = getSandbox(), view, $content, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    $content.on("dcpattributechange", change);
                    modelDocument.get("attributes").get(localAttrId).set("attributeValue", otherValue);

                    expect(change.calls.count()).toEqual(1);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromWidgetSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:attributeValue", change);
                    widget.setValue(otherValue);

                    expect(change.calls.count()).toEqual(1);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromModelSet", function () {
                    var $sandBox = getSandbox(), view, modelAttribute, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:attributeValue", change);
                    modelAttribute.set("attributeValue", otherValue);
                    expect(change.calls.count()).toEqual(1);
                });
            });

        });

    };

    return testAttribute;
});