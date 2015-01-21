/*global require, describe, beforeEach, setFixtures, expect, it, sandbox, jasmine, afterEach*/

var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

require([
    'underscore',
    'jquery',
    'text!template/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'models/mDocument',
    'views/document/vDocument'
], function (_, $, template, ModelDocument, ViewDocument) {
    "use strict";

    var testDocument,
        generateFamilyStructure,
        generateDocumentContent;

    template = JSON.parse(template);

    if (template.success !== true) {
        throw new Error("Unable to parse template " + template.error || "");
    }

    template = template.content;

    window.dcp = window.dcp || {};

    window.dcp.templates = _.defaults(window.dcp.templates, template) || template;

    // Mock family definition
    generateFamilyStructure = function (options) {

        var structure = [], attrStruct = {
            "id" :           "test_f_frame",
            "visibility" :   "W",
            "label" :        "frame",
            "type" :         "frame",
            "logicalOrder" : 0,
            "multiple" :     false,
            "options" :      [],
            "renderMode" : options.renderMode,
            "content" :      {}
        };

        structure.push(attrStruct);

        return structure;
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

    testDocument = function (title, options) {
        var modelDocument, currentSandbox, getSandbox = function () {
            return currentSandbox;
        };

        describe(title + " Test Document", function () {
            beforeEach(function () {
                var localId = _.uniqueId("Document");
                var $renderZone = $("#render");

                if (window.location.hash === "#displayDom") {
                    if ($renderZone.length === 0) {
                        $renderZone = $("body");
                    }
                    currentSandbox = $("<div></div>");
                    if ($renderZone.length === 0) {
                        $renderZone = $("body");
                    }
                    $renderZone.append(currentSandbox);
                } else {
                    currentSandbox = setFixtures(sandbox());
                }
                window.dcp = window.dcp || {};
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
                        locale : options.locale || {"culture": "fr-FR"},
                        renderMode : options.renderMode || "view",
                        attributes : options.attributes || generateFamilyStructure(options),
                        renderOptions : {}
                    }
                );
            });

            afterEach(function() {
                modelDocument.trigger("destroy");
            });

            describe(title + " : creation", function () {

                it("content", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox).toHaveClass("dcpDocument");
                    expect($sandBox).toHaveClass("dcpDocument--" + modelDocument.get("renderMode"));
                });

                it("header", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__header")).toExist();
                    expect($sandBox.find(".dcpDocument__header__icon")).toExist();
                    expect($sandBox.find(".dcpDocument__header__icon")).toHaveAttr('src', modelDocument.get("properties").get("icon") || "");
                    expect($sandBox.find(".dcpDocument__header__title")).toExist();
                    expect($sandBox.find(".dcpDocument__header__title")).toHaveText(modelDocument.get("properties").get("title"));
                    expect($sandBox.find(".dcpDocument__header__family")).toExist();
                    expect($sandBox.find(".dcpDocument__header__family")).toHaveText(modelDocument.get("properties").get("family").title || "");
                });

                it("menu", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__menu")).toExist();
                });

                it("content", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__body")).toExist();
                    expect($sandBox.find(".dcpDocument__form")).toExist();
                    expect($sandBox.find(".dcpDocument__frames")).toExist();
                    expect($sandBox.find(".dcpDocument__tabs")).toExist();
                });

                it("footer", function () {
                    var $sandBox = getSandbox();
                    (new ViewDocument({model : modelDocument, el : $sandBox}).render());
                    expect($sandBox.find(".dcpDocument__footer")).toExist();
                });

                it("event", function () {
                    var $sandBox = getSandbox(), view, renderDone, elementRendered;
                    view = new ViewDocument({model : modelDocument, el : $sandBox});
                    renderDone = jasmine.createSpy("renderDone");
                    elementRendered = jasmine.createSpy("elementRendered");
                    view.on("renderDone", renderDone);
                    view.on("partRender", elementRendered);
                    view.render();
                    expect(renderDone).toHaveBeenCalled();
                    expect(elementRendered.calls.count()).toEqual(modelDocument.get("attributes").length);
                });
            });
        });
    };

    testDocument("view", {});
    testDocument("edit", {renderMode : "edit"});
    testDocument("view locale us", {locale : {"culture": "en-US"}});
    testDocument("edit locale us", {renderMode : "edit", locale :  {"culture": "en-US"}});

    if (window.dcp.executeTests) {
        window.dcp.executeTests();
    }
});