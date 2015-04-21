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
    'dcpDocument/test/UnitTestUtilities',
    'text!dcpContextRoot/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'dcpDocument/models/mDocument',
    'dcpDocument/views/document/vDocument'
], function (_, $, unitTestUtils, template, ModelDocument, ViewDocument)
{
    "use strict";

    var testAttribute;

    // Get the template in JSON form and set it in the global variable
    template = JSON.parse(template);

    if (template.success !== true) {
        throw new Error("Unable to parse template " + template.error || "");
    }
    template = template.content;
    window.dcp = window.dcp || {};
    window.dcp.templates = window.dcp.templates || {};
    _.defaults(window.dcp.templates, template);

    testAttribute = function (config)
    {

        var title = config.title,
            attributeDefinition = config.attribute,
            initialValue = config.initialValue,
            options = config.options || {},
            otherValue = config.otherValue,
            familyStructure,
            launchTest,
            modelDocument, currentSandbox, localAttrId, getSandbox = function ()
            {
                return currentSandbox;
            }, findWidgetName = function ($element)
            {
                return _.find(_.keys($element.data()), function (currentKey)
                {
                    return currentKey.indexOf("dcpDcp") !== -1;
                });
            };

        beforeEach(function ()
        {
            familyStructure = options.attributes || unitTestUtils.generateFamilyStructure(config.attribute, options.renderMode, initialValue);
            currentSandbox = unitTestUtils.generateSandBox(config, $("#render"));
            //Generate mock model to test interaction between model, view and widget
            modelDocument = unitTestUtils.generateModelDocument(options,
                config.title,
                familyStructure,
                config.renderOptions || {}
            );
            localAttrId = familyStructure.localeAttrId;
        });

        afterEach(function ()
        {
            modelDocument.trigger("destroy");
        });

        launchTest = function launchTest(view, executeTest)
        {
            view.listenTo(view, "renderDone", function viewRenderDone()
            {
                executeTest();
            });
            view.listenTo(modelDocument, "attributeRender", function execTestAfterRender(id)
            {
                if (id === localAttrId) {
                    executeTest();
                }
            });
            view.render();
        };

        describe(title, function ()
        {

            it("dom", function (done)
            {
                var $sandBox = getSandbox(), view, executeTest;
                view = new ViewDocument({model: modelDocument, el: $sandBox});
                executeTest = _.after(2, function executeTest()
                {
                    expect($sandBox.find(".dcpAttribute[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__label[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__content--" + attributeDefinition.type + "[data-attrid=" + localAttrId + "]")).toExist();
                    done();
                });
                launchTest(view, executeTest);
            });

            it("label", function (done)
            {
                var $sandBox = getSandbox(), view, newLabel = _.uniqueId(title), executeTest;
                var iniLabel = familyStructure[1].label;

                view = new ViewDocument({model: modelDocument, el: $sandBox});
                executeTest = _.after(2, function executeTest()
                {
                    expect($sandBox.find(".dcpAttribute__label.dcpLabel[data-attrid=" + localAttrId + "]")).toHaveText(iniLabel);
                    modelDocument.get("attributes").get(localAttrId).set("label", newLabel);
                    expect($sandBox.find(".dcpAttribute__label[data-attrid=" + localAttrId + "]")).toHaveText(newLabel);
                    done();
                });
                launchTest(view, executeTest);
            });

            describe("Value", function ()
            {
                /* Check the initialValue on the widget and the attribute model*/
                it("InitialValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        modelValue = modelDocument.get("attributes").get(localAttrId).get("attributeValue");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        widgetValue = widget.getValue();
                        if (_.isArray(initialValue)) {
                            // verify each values
                            _.each(initialValue, function (v, k)
                            {
                                expect(modelValue[k].value).toEqual(v.value);
                                expect(widgetValue[k].value).toEqual(v.value);
                            });

                        } else {
                            expect(modelValue.value).toEqual(initialValue.value);
                            expect(widgetValue.value).toEqual(initialValue.value);
                        }
                        done();
                    });
                    launchTest(view, executeTest);

                });
                /* Check the setValue method of the attribute model*/
                it("ModelSetValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        modelDocument.get("attributes").get(localAttrId).set("attributeValue", otherValue);
                        modelValue = modelDocument.get("attributes").get(localAttrId).get("attributeValue");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        widgetValue = widget.getValue();

                        if (_.isArray(otherValue)) {
                            // verify each values
                            _.each(otherValue, function (v, k)
                            {
                                expect(modelValue[k].value).toEqual(v.value);
                                expect(widgetValue[k].value).toEqual(v.value);
                            });
                        } else {
                            expect(otherValue.value).toEqual(widgetValue.value);
                            expect(modelValue.value).toEqual(widgetValue.value);
                        }
                        done();
                    });
                    launchTest(view, executeTest);
                });
                /* Check the setValue method of the widget*/
                it("WidgetSetValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, modelValue, widget, widgetValue, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        widget.setValue(otherValue);
                        widgetValue = widget.getValue();
                        modelValue = modelDocument.get("attributes").get(localAttrId).get("attributeValue");
                        if (_.isArray(initialValue)) {
                            // verify each values
                            _.each(otherValue, function (v, k)
                            {
                                expect(modelValue[k].value).toEqual(v.value);
                                expect(widgetValue[k].value).toEqual(v.value);
                            });
                        } else {
                            expect(otherValue.value).toEqual(modelValue.value);
                            expect(widgetValue.value).toEqual(modelValue.value);
                        }
                        done();
                    });
                    launchTest(view, executeTest);
                });
            });

            describe("Event No Change", function ()
            {
                /* Check the dcpattributechange event of the widget */
                it("WidgetEventFromSetInitialValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, widget, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        $content.on("dcpattributechange", change);

                        widget.setValue(initialValue);

                        expect(change.calls.count()).toEqual(0);
                        done();
                    });
                    launchTest(view, executeTest);
                });
                it("WidgetEventFromModelSetInitial", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        $content.on("dcpattributechange", change);
                        modelDocument.get("attributes").get(localAttrId).set("attributeValue", initialValue);

                        expect(change.calls.count()).toEqual(0);
                        done();
                    });
                    launchTest(view, executeTest);

                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromWidgetSetInitialValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        modelAttribute = modelDocument.get("attributes").get(localAttrId);
                        modelAttribute.on("change:value", change);
                        widget.setValue(initialValue);

                        expect(change.calls.count()).toEqual(0);
                        done();
                    });
                    launchTest(view, executeTest);

                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromModelSetInitial", function (done)
                {
                    var $sandBox = getSandbox(), view, modelAttribute, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        modelAttribute = modelDocument.get("attributes").get(localAttrId);
                        modelAttribute.on("change:value", change);
                        modelAttribute.set("attributeValue", initialValue);
                        expect(change.calls.count()).toEqual(0);
                        done();
                    });
                    launchTest(view, executeTest);
                });
            });
            describe("Event Change", function ()
            {
                /* Check the dcpattributechange event of the widget */
                it("WidgetEventFromSetValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, widget, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        $content.on("dcpattributechange", change);
                        widget.setValue(otherValue);

                        expect(change.calls.count()).toEqual(1);
                        done();
                    });
                    launchTest(view, executeTest);
                });
                it("WidgetEventFromModelSet", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        $content.on("dcpattributechange", change);
                        modelDocument.get("attributes").get(localAttrId).set("attributeValue", otherValue);

                        expect(change.calls.count()).toEqual(1);
                        done();
                    });
                    launchTest(view, executeTest);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromWidgetSetValue", function (done)
                {
                    var $sandBox = getSandbox(), view, $content, modelAttribute, widget, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        $content = $sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]");
                        widget = $content.data(findWidgetName($content));
                        modelAttribute = modelDocument.get("attributes").get(localAttrId);
                        modelAttribute.on("change:attributeValue", change);
                        widget.setValue(otherValue);

                        expect(change.calls.count()).toEqual(1);
                        done();
                    });
                    launchTest(view, executeTest);
                });
                /* Check the event of the modelAttribute */
                it("ModelEventFromModelSet", function (done)
                {
                    var $sandBox = getSandbox(), view, modelAttribute, change, executeTest;
                    view = new ViewDocument({model: modelDocument, el: $sandBox});
                    executeTest = _.after(2, function executeTest()
                    {
                        change = jasmine.createSpy("change");
                        modelAttribute = modelDocument.get("attributes").get(localAttrId);
                        modelAttribute.on("change:attributeValue", change);
                        modelAttribute.set("attributeValue", otherValue);
                        expect(change.calls.count()).toEqual(1);
                        done();
                    });
                    launchTest(view, executeTest);
                });
            });

        });

    };

    return testAttribute;
});