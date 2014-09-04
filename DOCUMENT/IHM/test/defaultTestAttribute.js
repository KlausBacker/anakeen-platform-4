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

    generateFamilyStructure = function (localeAttrId, type) {
        var struct = {
            structure : {
                'test_f_frame' : {
                    id :         'test_f_frame',
                    label :      'frame',
                    multiple :   false,
                    options :    [],
                    type :       'frame',
                    visibility : 'W',
                    content :    {

                    }
                }
            }
        };
        if (localeAttrId) {
            struct.structure.test_f_frame.content[localeAttrId] = {
                id :         localeAttrId,
                label :      localeAttrId,
                multiple :   false,
                options :    [],
                type :       type,
                visibility : 'W'
            };
        }
        return struct;
    };

    generateDocumentContent = function (localeAttrId, value) {
        var data = {};

        value = _.clone(value);

        if (localeAttrId) {
            data[localeAttrId] = {
                displayValue : value.displayValue || value.value || value,
                value :        value.value || value
            };
        }

        return data;
    };

    testAttribute = function (title, type, value, options, value2) {
        var modelDocument, currentSandbox, localAttrId, getSandbox = function () {
            return currentSandbox;
        }, findWidgetName = function ($element) {
            return _.find(_.keys($element.data()), function (currentKey) {
                return currentKey.indexOf("dcpDcp") !== -1;
            });
        };
        beforeEach(function () {
            var localId = _.uniqueId("Document");
            localAttrId = _.uniqueId(type);
            currentSandbox = $("<div></div>");
            var $renderZone = $("#render");
            if ($renderZone.length === 0) {
                $renderZone = $("body");
            }
            $renderZone.append(currentSandbox);
            //currentSandbox = setFixtures(sandbox());
            modelDocument = new ModelDocument(
                {},
                {
                    properties : {id : localId, title : title + "_" + localAttrId, fromname : localId, fromtitle : localId},
                    menus :      [],
                    family :     options.familyContent || generateFamilyStructure(localAttrId, type),
                    locale :     options.locale || "fr_FR",
                    renderMode : options.renderMode || "view",
                    attributes : options.attributes || generateDocumentContent(localAttrId, value)
                }
            );
        });

        describe(title, function () {

            it("dom", function () {
                var $sandBox = getSandbox(), view;
                view = new ViewDocument({model : modelDocument, el : $sandBox});
                view.render();
                expect(".dcpAttribute[data-attrid=" + localAttrId + "]").toExist();
                expect(".dcpAttribute__label[data-attrid=" + localAttrId + "]").toExist();
                expect(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]").toExist();
                expect(".dcpAttribute__contentWrapper--" + type + "[data-attrid=" + localAttrId + "]").toExist();
            });

            it("label", function () {
                var $sandBox = getSandbox(), view, newLabel = _.uniqueId(title);
                view = new ViewDocument({model : modelDocument, el : $sandBox});
                view.render();
                expect(".dcpAttribute__label[data-attrid=" + localAttrId + "]").toHaveText(localAttrId);
                modelDocument.get("attributes").get(localAttrId).set("label", newLabel);
                expect(".dcpAttribute__label[data-attrid=" + localAttrId + "]").toHaveText(newLabel);
            });

            describe("Value", function () {
                /* Check the initialValue on the widget and the attribute model*/
                it("InitialValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("value");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    expect(modelValue.value).toEqual(value.value || value);
                    expect(widget.getValue().value).toEqual(value.value || value);
                });
                /* Check the setValue method of the attribute model*/
                it("ModelSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    modelDocument.get("attributes").get(localAttrId).set("value", value2);
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    expect(value2.value).toEqual(widget.getValue().value);
                    expect(modelDocument.get("attributes").get(localAttrId).get("value").value).toEqual(widget.getValue().value);
                });
                /* Check the setValue method of the widget*/
                it("WidgetSetValue", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    widget.setValue(value2);
                    modelValue = modelDocument.get("attributes").get(localAttrId).get("value");
                    expect(value2.value).toEqual(modelValue.value);
                    expect(widget.getValue().value).toEqual(modelDocument.get("attributes").get(localAttrId).get("value").value);
                });
            });

            describe("Event", function () {
                /* Check the dcpattributechange event of the widget */
                it("WidgetEvent", function () {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    $content.on("dcpattributechange", change);
                    widget.setValue(value2);
                    modelDocument.get("attributes").get(localAttrId).set("value", value);
                    expect(change.calls.count()).toEqual(2);
                });
                /* Check the event of the modelAttribute */
                it("ModelEvent", function () {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    view.render();
                    change = jasmine.createSpy("change");
                    $content = $(".dcpAttribute__contentWrapper[data-attrid=" + localAttrId + "]");
                    widget = $content.data(findWidgetName($content));
                    modelAttribute = modelDocument.get("attributes").get(localAttrId);
                    modelAttribute.on("change:value", change);
                    widget.setValue(value2);
                    modelAttribute.set("value", value);
                    expect(change.calls.count()).toEqual(2);
                });
            });

        });

    };

    return testAttribute;
});