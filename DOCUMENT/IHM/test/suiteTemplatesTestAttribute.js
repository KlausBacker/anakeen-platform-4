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
    window.dcp.templates = window.dcp.templates || template;

    testAttribute = function testAttribute(config)
    {

        var initialValue = config.initialValue,
            options = config.options || {},
            expectedContent = config.expectedContent,
            expectedSubContents = config.expectedSubContents || [],
            familyStructure;
        var modelDocument, currentSandbox, localAttrId, getSandbox = function getSandbox()
        {
            return currentSandbox;
        };

        beforeEach(function beforeEach()
        {
            familyStructure = unitTestUtils.generateFamilyStructure(config.attribute, options.renderMode, initialValue);
            currentSandbox = unitTestUtils.generateSandBox(config, $("#render"));
            //Generate mock model to test interaction between model, view and widget
            modelDocument = unitTestUtils.generateModelDocument(options,
                config.title,
                familyStructure,
                config.renderOptions || {}
            );
            localAttrId = familyStructure.localeAttrId;
        });

        afterEach(function afterEach()
        {
            if (window.location.hash !== "#displayDom") {
                modelDocument.trigger("destroy");
            }
        });

        describe(config.title, function checkAttribute()
        {

            it("dom", function checkDom(done)
            {
                var $sandBox = getSandbox(), view, iniLabel = familyStructure[1].label, executeTest;
                view = new ViewDocument({model: modelDocument, el: $sandBox});
                executeTest = _.after(2, function executeTest()
                {
                    expect($sandBox.find(".dcpFrame__content > .dcpAttribute > .dcpAttribute__label.dcpLabel[data-attrid=" + localAttrId + "]")).toHaveText(iniLabel);
                    expect($sandBox.find(".dcpAttribute[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__label[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__content--" + config.attribute.type + "[data-attrid=" + localAttrId + "]")).toExist();
                    expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "]")).toExist();

                    if (expectedContent) {
                        expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "]")).toHaveHtml(expectedContent);
                    }

                    _.each(expectedSubContents, function checkExpectedFilter(expectFilter)
                    {
                        if (expectFilter.textValue === null) {
                            expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "] " + expectFilter.filter)).toExist();
                        } else {
                            if (expectFilter.textValue) {
                                expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "] " + expectFilter.filter)).toHaveText(expectFilter.textValue);
                            }
                            if (expectFilter.htmlValue) {
                                expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "] " + expectFilter.filter)).toHaveHtml(expectFilter.htmlValue);
                            }
                        }

                    });
                    done();
                });
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
            });

        });

    };

    return testAttribute;
});