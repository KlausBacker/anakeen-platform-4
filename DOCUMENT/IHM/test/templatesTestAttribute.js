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
    'text!dcpDocumentTemplate/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'dcpDocument/models/mDocument',
    'dcpDocument/views/document/vDocument'
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
                "id" :          localeAttrId,
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




    testAttribute = function (config) {

        var title = config.title;
        var attributeDefinition = config.attribute;
        var initialValue = config.initialValue;
        var options = config.options || {};
        var expectedContent = config.expectedContent;
        var expectedSubContents = config.expectedSubContents || [];
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
            localAttrId = attributeDefinition.id || _.uniqueId(attributeDefinition.type);

            if (config.useRender || window.location.hash === "#displayDom") {
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
                var iniLabel = familyStructure[1].label;
                view = new ViewDocument({model : modelDocument, el : $sandBox});
                view.render();


                expect($sandBox.find(".dcpFrame__content > .dcpAttribute > .dcpAttribute__label.dcpLabel[data-attrid=" + localAttrId + "]")).toHaveText(iniLabel);

                expect($sandBox.find(".dcpAttribute[data-attrid=" + localAttrId + "]")).toExist();
                expect($sandBox.find(".dcpAttribute__label[data-attrid=" + localAttrId + "]")).toExist();
                expect($sandBox.find(".dcpAttribute__content[data-attrid=" + localAttrId + "]")).toExist();
                expect($sandBox.find(".dcpAttribute__content--" + attributeDefinition.type + "[data-attrid=" + localAttrId + "]")).toExist();

                expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "]")).toExist();

                if (expectedContent) {
                    expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "]")).toHaveHtml(expectedContent);
                }

                _.each(expectedSubContents, function (expectFilter) {
                    if (expectFilter.textValue === null) {
                        expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "] "+expectFilter.filter)).toExist();
                    } else {
                        if (expectFilter.textValue) {
                            expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "] "+expectFilter.filter)).toHaveText(expectFilter.textValue);

                        }
                        if (expectFilter.htmlValue) {
                            expect($sandBox.find(".dcpAttribute__content .dcpCustomTemplate[data-attrid=" + localAttrId + "] " + expectFilter.filter)).toHaveHtml(expectFilter.htmlValue);
                        }
                    }

                });
            });





        });

    };

    return testAttribute;
});