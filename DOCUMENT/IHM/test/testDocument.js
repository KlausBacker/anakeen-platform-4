/*global require, describe, beforeEach, setFixtures, expect, it, sandbox, jasmine, afterEach*/

var asset = "index.php";
if (window.__karma__) {
    asset = "guest.php";
}

require([
    'underscore',
    'jquery',
    'dcpDocument/test/UnitTestUtilities',
    'text!dcpContextRoot/' + asset + '?app=DOCUMENT&action=TEMPLATE',
    'dcpDocument/models/mDocument',
    'dcpDocument/views/document/vDocument'
], function (_, $, unitTestUtils, template, ModelDocument, ViewDocument) {
    "use strict";

    var testDocument;

    template = JSON.parse(template);

    if (template.success !== true) {
        throw new Error("Unable to parse template " + template.error || "");
    }

    template = template.content;

    window.dcp = window.dcp || {};

    window.dcp.templates = _.defaults(window.dcp.templates, template) || template;

    testDocument = function (title, options) {
        var modelDocument, currentSandbox, getSandbox = function () {
            return currentSandbox;
        };

        describe(title + " Test Document", function () {
            beforeEach(function () {
                currentSandbox = unitTestUtils.generateSandBox(options, $("#render"));
                //Generate mock model to test interaction between model, view and widget
                modelDocument = unitTestUtils.generateModelDocument(options,
                    title,
                    options.attributes || unitTestUtils.generateFamilyStructure({}, options.renderMode),
                    options.renderOptions || {}
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